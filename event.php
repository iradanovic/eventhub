<?php
/**
 * EventHub - detalji pojedinog događanja
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$id   = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM events WHERE id = :id');
$stmt->execute([':id' => $id]);
$ev = $stmt->fetch();

if (!$ev) {
    http_response_code(404);
    $pageTitle = 'Događanje nije pronađeno';
    require __DIR__ . '/includes/header.php';
    echo '<section class="container"><div class="empty-state">
            <h1>404 - događanje nije pronađeno</h1>
            <p>Možda je obrisano ili je link pogrešan. <a href="index.php">Natrag na popis događanja</a>.</p>
          </div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle  = $ev['title'];
$activePage = 'index';
require __DIR__ . '/includes/header.php';
?>

<section class="container event-detail">
  <p class="breadcrumb"><a href="index.php">&larr; Sva događanja</a></p>

  <article class="ticket ticket-large">
    <div class="ticket-stub">
      <span class="ticket-day"><?= date('d', strtotime($ev['start_datetime'])) ?></span>
      <span class="ticket-month"><?= ['','SIJ','VELJ','OŽU','TRA','SVI','LIP','SRP','KOL','RUJ','LIS','STU','PRO'][(int)date('n', strtotime($ev['start_datetime']))] ?></span>
      <span class="ticket-year"><?= date('Y', strtotime($ev['start_datetime'])) ?></span>
    </div>

    <div class="ticket-body">
      <p class="ticket-meta">
        <span class="badge badge-<?= e($ev['source']) ?>"><?= e(source_label($ev['source'])) ?></span>
        <span class="ticket-category"><?= e($ev['category']) ?></span>
      </p>

      <?php if ($ev['image_url']): ?>
        <div class="detail-image">
          <img src="<?= e($ev['image_url']) ?>" alt="Slika događanja: <?= e($ev['title']) ?>">
        </div>
      <?php endif; ?>
      <h1 class="ticket-title"><?= e($ev['title']) ?></h1>

      <dl class="event-facts">
        <dt>Početak</dt>
        <dd><?= e(format_event_date($ev['start_datetime'])) ?></dd>

        <?php if ($ev['end_datetime']): ?>
          <dt>Završetak</dt>
          <dd><?= e(format_event_date($ev['end_datetime'])) ?></dd>
        <?php endif; ?>

        <?php if ($ev['venue_name']): ?>
          <dt>Lokacija</dt>
          <dd>
            <?= e($ev['venue_name']) ?>
            <?= $ev['venue_address'] ? ', ' . e($ev['venue_address']) : '' ?>
            <?= $ev['city'] ? ', ' . e($ev['city']) : '' ?>
          </dd>
        <?php endif; ?>

        <?php if ($ev['price_info']): ?>
          <dt>Cijena</dt>
          <dd><?= e($ev['price_info']) ?></dd>
        <?php endif; ?>

        <dt>Izvor podataka</dt>
        <dd><?= e(source_label($ev['source'])) ?></dd>
      </dl>

      <?php if ($ev['description']): ?>
        <h2 class="detail-subtitle">Opis</h2>
        <p class="event-description"><?= nl2br(e($ev['description'])) ?></p>
      <?php endif; ?>

      <div class="detail-actions">
        <?php if ($ev['event_url']): ?>
          <a class="btn btn-primary" href="<?= e($ev['event_url']) ?>"
             target="_blank" rel="noopener noreferrer">Službena stranica / ulaznice</a>
        <?php endif; ?>
        <a class="btn btn-ghost" href="api/events.php?format=json&amp;id=<?= (int)$ev['id'] ?>"
           target="_blank" rel="noopener">JSON</a>
        <a class="btn btn-ghost" href="api/events.php?format=xml&amp;id=<?= (int)$ev['id'] ?>"
           target="_blank" rel="noopener">XML</a>
      </div>
    </div>
  </article>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
