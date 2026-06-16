<?php
/**
 * EventHub - administracija korisnika (popis)
 */

declare(strict_types=1);

require __DIR__ . '/auth.php';

$users = db()->query(
    'SELECT id, first_name, last_name, email, username, role, country, created_at
     FROM users ORDER BY role, last_name, first_name'
)->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pageTitle = 'Korisnici';
require __DIR__ . '/../includes/header.php';
?>

<section class="container">
  <p class="breadcrumb"><a href="dashboard.php">&larr; Nadzorna ploča</a></p>
  <div class="admin-head">
    <h1 class="page-title">Korisnici (<?= count($users) ?>)</h1>
    <a class="btn btn-primary" href="user_form.php">+ Novi korisnik</a>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-success" role="status"><?= e($flash) ?></div>
  <?php endif; ?>

  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th><th>Ime i prezime</th><th>Korisničko ime</th>
          <th>E-mail</th><th>Država</th><th>Rola</th><th>Akcije</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= e($u['first_name'] . ' ' . $u['last_name']) ?></td>
            <td class="mono"><?= e($u['username']) ?></td>
            <td><a href="mailto:<?= e($u['email']) ?>"><?= e($u['email']) ?></a></td>
            <td><?= e($u['country']) ?></td>
            <td>
              <span class="badge <?= $u['role'] === 'admin' ? 'badge-tm' : 'badge-ical' ?>">
                <?= $u['role'] === 'admin' ? 'admin' : 'korisnik' ?>
              </span>
            </td>
            <td class="actions-cell">
              <a class="btn btn-small" href="user_form.php?id=<?= (int)$u['id'] ?>">Uredi</a>
              <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                <a class="btn btn-small btn-danger"
                   href="user_delete.php?id=<?= (int)$u['id'] ?>"
                   onclick="return confirm('Sigurno obrisati ovog korisnika?');">Obriši</a>
              <?php else: ?>
                <span class="form-hint">(vi)</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
