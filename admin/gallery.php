<?php
/**
 * EventHub - administracija galerije (popis)
 */

declare(strict_types=1);

require __DIR__ . '/auth.php';

$images = db()->query(
    'SELECT id, image_url, alt_text, caption FROM gallery ORDER BY id'
)->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pageTitle = 'Galerija';
require __DIR__ . '/../includes/header.php';
?>

<section class="container">
  <p class="breadcrumb"><a href="dashboard.php">&larr; Nadzorna ploča</a></p>
  <div class="admin-head">
    <h1 class="page-title">Galerija (<?= count($images) ?>)</h1>
    <a class="btn btn-primary" href="gallery_form.php">+ Nova slika</a>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-success" role="status"><?= e($flash) ?></div>
  <?php endif; ?>

  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>ID</th><th>Slika</th><th>Naslov</th><th>Opis</th><th>Akcije</th></tr>
      </thead>
      <tbody>
        <?php foreach ($images as $img): ?>
          <tr>
            <td><?= (int)$img['id'] ?></td>
            <td><img class="admin-thumb" src="../<?= e($img['image_url']) ?>" alt=""></td>
            <td><?= e($img['alt_text']) ?></td>
            <td><?= e(mb_strimwidth($img['caption'], 0, 80, '…')) ?></td>
            <td class="actions-cell">
              <a class="btn btn-small" href="gallery_form.php?id=<?= (int)$img['id'] ?>">Uredi</a>
              <a class="btn btn-small btn-danger" href="gallery_delete.php?id=<?= (int)$img['id'] ?>"
                 onclick="return confirm('Sigurno obrisati ovu sliku?');">Obriši</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
