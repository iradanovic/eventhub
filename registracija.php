<?php
/**
 * EventHub - registracija korisnika (rola: user)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
ensure_session();

if (current_user()) {
    header('Location: profil.php');
    exit;
}

$errors = [];
$old = ['first_name' => '', 'last_name' => '', 'email' => '',
        'username' => '', 'country' => 'Hrvatska'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($old) as $k) {
        $old[$k] = trim($_POST[$k] ?? '');
    }
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($old['first_name'] === '' || $old['last_name'] === '') {
        $errors[] = 'Ime i prezime su obavezni.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Upišite ispravnu e-mail adresu.';
    }
    if (!preg_match('/^[A-Za-z0-9_.-]{3,60}$/', $old['username'])) {
        $errors[] = 'Korisničko ime: 3-60 znakova (slova, brojke, . _ -).';
    }
    if (mb_strlen($password) < 6) {
        $errors[] = 'Lozinka mora imati barem 6 znakova.';
    }
    if ($password !== $password2) {
        $errors[] = 'Lozinke se ne podudaraju.';
    }
    if (!in_array($old['country'], country_options(), true)) {
        $old['country'] = 'Ostalo';
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM users WHERE username = :u OR email = :e'
        );
        $stmt->execute([':u' => $old['username'], ':e' => $old['email']]);
        if ((int)$stmt->fetchColumn() > 0) {
            $errors[] = 'Korisničko ime ili e-mail već postoje.';
        }
    }

    if (!$errors) {
        db()->prepare(
            'INSERT INTO users (first_name, last_name, email, username, password_hash, role, country)
             VALUES (:fn, :ln, :em, :un, :ph, \'user\', :co)'
        )->execute([
            ':fn' => $old['first_name'],
            ':ln' => $old['last_name'],
            ':em' => $old['email'],
            ':un' => $old['username'],
            ':ph' => password_hash($password, PASSWORD_DEFAULT),
            ':co' => $old['country'],
        ]);

        $row = db()->query('SELECT * FROM users WHERE id = ' . (int)db()->lastInsertId())->fetch();
        login_user($row);
        header('Location: profil.php?novi=1');
        exit;
    }
}

$pageTitle  = 'Registracija';
$activePage = 'registracija';
require __DIR__ . '/includes/header.php';
?>

<section class="container page-narrow">
  <h1 class="page-title">Registracija</h1>
  <p class="page-intro">
    Otvorite besplatan korisnički račun. Registrirani korisnici imaju vlastiti
    profil, a administratori dodatno upravljaju sadržajem i korisnicima.
  </p>

  <?php if ($errors): ?>
    <div class="alert alert-error" role="alert">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="registracija.php" class="form-card" novalidate>
    <div class="form-row">
      <div class="form-field">
        <label for="first_name">Ime *</label>
        <input type="text" id="first_name" name="first_name" required maxlength="80"
               value="<?= e($old['first_name']) ?>">
      </div>
      <div class="form-field">
        <label for="last_name">Prezime *</label>
        <input type="text" id="last_name" name="last_name" required maxlength="80"
               value="<?= e($old['last_name']) ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-field">
        <label for="email">E-mail adresa *</label>
        <input type="email" id="email" name="email" required maxlength="190"
               value="<?= e($old['email']) ?>">
      </div>
      <div class="form-field">
        <label for="country">Država</label>
        <select id="country" name="country">
          <?php foreach (country_options() as $c): ?>
            <option value="<?= e($c) ?>" <?= $old['country'] === $c ? 'selected' : '' ?>>
              <?= e($c) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-field">
      <label for="username">Korisničko ime *</label>
      <input type="text" id="username" name="username" required maxlength="60"
             autocomplete="username" value="<?= e($old['username']) ?>">
    </div>

    <div class="form-row">
      <div class="form-field">
        <label for="password">Lozinka * <small>(min. 6 znakova)</small></label>
        <input type="password" id="password" name="password" required
               minlength="6" autocomplete="new-password">
      </div>
      <div class="form-field">
        <label for="password2">Ponovite lozinku *</label>
        <input type="password" id="password2" name="password2" required
               minlength="6" autocomplete="new-password">
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Registriraj se</button>
    <p class="form-hint">Već imate račun? <a href="prijava.php">Prijavite se</a>.</p>
  </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
