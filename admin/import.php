<?php
/**
 * EventHub - pokretanje uvoza podataka iz vanjskih izvora
 */

declare(strict_types=1);

require __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/import/ticketmaster.php';
require_once __DIR__ . '/../includes/import/ical.php';
require_once __DIR__ . '/../includes/import/scraper.php';

$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $which = $_POST['which'] ?? 'all';

    if ($which === 'ticketmaster' || $which === 'all') {
        $results[] = import_ticketmaster();
    }
    if ($which === 'ical' || $which === 'all') {
        $results[] = import_ical();
    }
    if ($which === 'scraper' || $which === 'all') {
        $results[] = import_scraper();
    }
}

$pageTitle = 'Uvoz podataka';
require __DIR__ . '/../includes/header.php';
?>

<section class="container page-narrow">
  <p class="breadcrumb"><a href="dashboard.php">&larr; Nadzorna ploča</a></p>
  <h1 class="page-title">Uvoz podataka</h1>
  <p class="page-intro">
    Aplikacija agregira događanja iz tri različita tipa izvora.
    <?php if (DEMO_MODE): ?>
      Trenutno je uključen <strong>DEMO_MODE</strong> - uvoz čita lokalne
      uzorke iz <code>data/samples/</code>. Za stvarne podatke postavite
      <code>DEMO_MODE</code> na <code>false</code> u <code>config.php</code>.
    <?php else: ?>
      Uvoz dohvaća <strong>stvarne podatke</strong> s interneta.
    <?php endif; ?>
  </p>

  <?php foreach ($results as $r): ?>
    <div class="alert <?= $r['errors'] ? 'alert-error' : 'alert-success' ?>" role="status">
      <strong><?= e($r['source']) ?></strong>:
      novih <?= (int)$r['inserted'] ?>, ažuriranih <?= (int)$r['updated'] ?>.
      <?php if ($r['errors']): ?>
        <ul>
          <?php foreach ($r['errors'] as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <div class="import-grid">
    <form method="post" class="import-card">
      <h2>Ticketmaster Discovery API</h2>
      <p>REST API koji vraća <strong>JSON</strong>. Dohvaća koncerte i predstave
         za grad <?= e(TICKETMASTER_CITY) ?> (<?= e(TICKETMASTER_COUNTRY) ?>).</p>
      <input type="hidden" name="which" value="ticketmaster">
      <button type="submit" class="btn btn-primary">Pokreni uvoz</button>
    </form>

    <form method="post" class="import-card">
      <h2>iCal feed (.ics)</h2>
      <p>Javni kalendar u <strong>iCalendar</strong> formatu (RFC 5545),
         parsiran vlastitim PHP parserom.</p>
      <input type="hidden" name="which" value="ical">
      <button type="submit" class="btn btn-primary">Pokreni uvoz</button>
    </form>

    <form method="post" class="import-card">
      <h2>Web scraping</h2>
      <p>Izvlačenje događanja iz <strong>HTML-a</strong> lokalne stranice
         pomoću DOMDocument + <strong>XPath</strong> izraza.</p>
      <input type="hidden" name="which" value="scraper">
      <button type="submit" class="btn btn-primary">Pokreni uvoz</button>
    </form>
  </div>

  <form method="post" class="import-all">
    <input type="hidden" name="which" value="all">
    <button type="submit" class="btn btn-ghost">Pokreni sva tri uvoza odjednom</button>
  </form>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
