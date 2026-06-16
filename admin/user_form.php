<?php
/**
 * EventHub - dodavanje / uređivanje korisnika (uklj. rolu)
 */

declare(strict_types=1);

require __DIR__ . '/auth.php';

$pdo    = db();
$id     = (int)($_GET['id'] ?? 0);
$errors = [];
$isSelf = $id > 0 && $id === (int)$_SESSION['user_id'];

$u = ['first_name' => '', 'last_name' => '', 'email' => '',
      'username' => '', 'role' => 'user', 'country' => 'Hrvatska'];

if ($id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        $_SESSION['flash'] = 'Korisnik nije pronađen.';
        header('Location: users.php');
        exit;
    }
    $u = array_merge($u, $row);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['first_name', 'last_name', 'email', 'username', 'role', 'country'] as $k) {
        $u[$k] = trim($_POST[$k] ?? '');
    }
    $password = $_POST['password'] ?? '';

    if ($u['first_name'] === '' || $u['last_name'] === '') {
        $errors[] = 'Ime i prezime su obavezni.';
    }
    if (!filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Neispravna e-mail adresa.';
    }
    if (!preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $u['username'])) {
        $errors[] = 'Korisničko ime: 3-60 znakova (slova, brojke, . _ -).';
    }
    if (!in_array($u['role'], ['admin', 'user'], true)) {
        $u['role'] = 'user';
    }
    if ($isSelf && $u['role'] !== 'admin') {
        $errors[] = 'Ne možete sami sebi ukloniti administratorsku rolu.';
    }
    if ($id === 0 && mb_strlen($password) < 6) {
        $errors[] = 'Za novog korisnika lozinka je obavezna (min. 6 znakova).';
    }
    if ($password !== '' && mb_strlen($password) < 6) {
        $errors[] = 'Nova lozinka mora imati barem 6 znakova.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM users
             WHERE (username = :u OR email = :e) AND id <> :id'
        );
        $stmt->execute([':u' => $u['username'], ':e' => $u['email'], ':id' => $id]);
        if ((int)$stmt->fetchColumn() > 0) {
            $errors[] = 'Korisničko ime ili e-mail već postoje.';
        }
    }

    if (!$errors) {
        if ($id > 0) {
            $sql = 'UPDATE users SET
                      first_name = :fn, last_name = :ln, email = :em,
                      username = :un, role = :ro, country = :co'
                 . ($password !== '' ? ', password_hash = :ph' : '')
                 . ' WHERE id = :id';
            $params = [
                ':fn' => $u['first_name'], ':ln' => $u['last_name'],
                ':em' => $u['email'],      ':un' => $u['username'],
                ':ro' => $u['role'],       ':co' => $u['country'],
                ':id' => $id,
            ];
            if ($password !== '') {
                $params[':ph'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $pdo->prepare($sql)->execute($params);
            $_SESSION['flash'] = 'Korisnik je ažuriran.';
        } else {
            $pdo->prepare(
                'INSERT INTO users
                   (first_name, last_name, email, username, password_hash, role, country)
                 VALUES (:fn, :ln, :em, :un, :ph, :ro, :co)'
            )->execute([
                ':fn' => $u['first_name'], ':ln' => $u['last_name'],
                ':em' => $u['email'],      ':un' => $u['username'],
                ':ph' => password_hash($password, PASSWORD_DEFAULT),
                ':ro' => $u['role'],       ':co' => $u['country'],
            ]);
            $_SESSION['flash'] = 'Novi korisnik je dodan.';
        }
        header('Location: users.php');
        exit;
    }
}

$pageTitle = $id > 0 ? 'Uredi korisnika' : 'Novi korisnik';
require __DIR__ . '/../includes/header.php';
?>

<section class="container page-narrow">
  <p class="breadcrumb"><a href="users.php">&larr; Korisnici</a></p>
  <h1 class="page-title"><?= $id > 0 ? 'Uredi korisnika' : 'Novi korisnik' ?></h1>

  <?php if ($errors): ?>
    <div class="alert alert-error" role="alert">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" class="form-card" novalidate>
    <div class="form-row">
      <div class="form-field">
        <label for="first_name">Ime *</label>
        <input type="text" id="first_name" name="first_name" required maxlength="80"
               value="<?= e($u['first_name']) ?>">
      </div>
      <div class="form-field">
        <label for="last_name">Prezime *</label>
        <input type="text" id="last_name" name="last_name" required maxlength="80"
               value="<?= e($u['last_name']) ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-field">
        <label for="email">E-mail *</label>
        <input type="email" id="email" name="email" required maxlength="190"
               value="<?= e($u['email']) ?>">
      </div>
      <div class="form-field">
        <label for="username">Korisničko ime *</label>
        <input type="text" id="username" name="username" required maxlength="60"
               value="<?= e($u['username']) ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-field">
        <label for="role">Rola *</label>
        <select id="role" name="role" <?= $isSelf ? 'disabled' : '' ?>>
          <option value="user"  <?= $u['role'] === 'user'  ? 'selected' : '' ?>>korisnik</option>
          <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>administrator</option>
        </select>
        <?php if ($isSelf): ?>
          <input type="hidden" name="role" value="admin">
          <small class="form-hint">Vlastitu rolu nije moguće mijenjati.</small>
        <?php endif; ?>
      </div>
      <div class="form-field">
        <label for="country">Država</label>
        <select id="country" name="country">
          <?php foreach (country_options() as $c): ?>
            <option value="<?= e($c) ?>" <?= $u['country'] === $c ? 'selected' : '' ?>>
              <?= e($c) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-field">
      <label for="password">
        <?= $id > 0 ? 'Nova lozinka (ostavite prazno ako se ne mijenja)' : 'Lozinka *' ?>
      </label>
      <input type="password" id="password" name="password"
             <?= $id === 0 ? 'required' : '' ?> minlength="6" autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary">
      <?= $id > 0 ? 'Spremi promjene' : 'Dodaj korisnika' ?>
    </button>
  </form>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
