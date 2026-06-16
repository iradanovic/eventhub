<?php
/**
 * EventHub - prijava korisnika (user i admin)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
ensure_session();

if (current_user()) {
    header('Location: ' . (is_admin() ? 'admin/dashboard.php' : 'profil.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT * FROM users WHERE username = :u OR email = :e');
    $stmt->execute([':u' => $username, ':e' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        login_user($user);
        header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'profil.php'));
        exit;
    }

    $error = 'Pogrešno korisničko ime ili lozinka.';
}

$pageTitle  = 'Prijava';
$activePage = 'prijava';
require __DIR__ . '/includes/header.php';
?>

<section class="container page-narrow">
  <h1 class="page-title">Prijava</h1>
  <p class="page-intro">
    Prijavite se korisničkim imenom ili e-mail adresom. Administratori nakon
    prijave pristupaju nadzornoj ploči, a korisnici svom profilu.
  </p>

  <?php if ($error): ?>
    <div class="alert alert-error" role="alert"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post" action="prijava.php" class="form-card form-login">
    <div class="form-field">
      <label for="username">Korisničko ime ili e-mail</label>
      <input type="text" id="username" name="username" required autocomplete="username">
    </div>
    <div class="form-field">
      <label for="password">Lozinka</label>
      <input type="password" id="password" name="password" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn btn-primary">Prijava</button>
    <p class="form-hint">
      Nemate račun? <a href="registracija.php">Registrirajte se</a>.<br>
      Demo pristup: <code>admin</code> / <code>admin123</code> (administrator)
      ili <code>ivan</code> / <code>ivan123</code> (korisnik).
    </p>
  </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
