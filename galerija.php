<?php
/**
 * EventHub - galerija (minimalno dva reda slika, svaka s opisom)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$gallery = [
    ['assets/img/gal-1.svg', 'Publika na koncertu',
     'Atmosfera s prošlogodišnjeg ljetnog koncerta na Jarunu.'],
    ['assets/img/gal-2.svg', 'Ljetno kino',
     'Projekcija pod zvijezdama na Ljetnoj pozornici Tuškanac.'],
    ['assets/img/gal-3.svg', 'Sajamski paviljon',
     'Interliber - najveći sajam knjiga na Zagrebačkom velesajmu.'],
    ['assets/img/gal-4.svg', 'Galerijski postav',
     'Postav izložbe bečke secesije u Muzeju za umjetnost i obrt.'],
    ['assets/img/gal-5.svg', 'Ulični festival',
     'Cest is d\'Best - ulični zabavljači u centru grada.'],
    ['assets/img/gal-6.svg', 'Advent na Zrinjevcu',
     'Zimska čarolija ispod platana - nagrađivani zagrebački Advent.'],
];

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
    <?php foreach ($gallery as [$src, $alt, $caption]): ?>
      <figure class="gallery-item">
        <img src="<?= e($src) ?>" alt="<?= e($alt) ?>" loading="lazy">
        <figcaption><?= e($caption) ?></figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
