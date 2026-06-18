<?php
/**
 * EventHub - galerija (minimalno dva reda slika, svaka s opisom)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$gallery = db()->query(
    'SELECT image_url, alt_text, caption FROM gallery ORDER BY id'
)->fetchAll();

$pageTitle  = 'Galerija';
$activePage = 'galerija';
require __DIR__ . '/includes/header.php';
?>

<section class="container">
  <h1 class="page-title">Galerija</h1>
  <p class="page-intro">
    Trenuci s događanja koja je EventHub zabilježio - od festivala i kina na
    otvorenom do sajmova i izložbi.
  </p>

  <div class="gallery-grid">
    <?php foreach ($gallery as $g): ?>
      <figure class="gallery-item">
        <img src="<?= e($g['image_url']) ?>" alt="<?= e($g['alt_text']) ?>" loading="lazy">
        <figcaption><?= e($g['caption']) ?></figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
