<?php
/**
 * EventHub - admin nadzorna ploča (CMS)
 */

declare(strict_types=1);

require __DIR__ . '/auth.php';

$pdo = db();

/* Statistika po izvorima */
$bySource = $pdo->query(
    'SELECT source, COUNT(*) AS cnt FROM events GROUP BY source'
)->fetchAll(PDO::FETCH_KEY_PAIR);

$totalEvents   = array_sum($bySource);
$totalMessages = (int)$pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();

/* Sva događanja (i prošla) za upravljanje */
$events = $pdo->query(
    'SELECT id, title, category, start_datetime, source
     FROM events ORDER BY start_datetime DESC LIMIT 200'
)->fetchAll();

/* Zadnje poruke s kontakt forme */
$messages = $pdo->query(
    'SELECT * FROM messages ORDER BY created_at DESC LIMIT 20'
)->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pageTitle = 'Nadzorna ploča';
require __DIR__ . '/../includes/header.php';
?>

<section class="container">
  <div class="admin-head">
    <h1 class="page-title">Nadzorna ploča</h1>
    <p class="admin-user">
      Prijavljeni: <strong><?= e($_SESSION['user_name']) ?></strong>
      · <a href="logout.php">Odjava</a>
    </p>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-success" role="status"><?= e($flash) ?></div>
  <?php endif; ?>

  <div class="stat-grid">
    <div class="stat-card">
      <span class="stat-num"><?= $totalEvents ?></span>
      <span class="stat-label">ukupno događanja</span>
    </div>
    <div class="stat-card">
      <span class="stat-num"><?= (int)($bySource['ticketmaster'] ?? 0) ?></span>
      <span class="stat-label">Ticketmaster API</span>
    </div>
    <div class="stat-card">
      <span class="stat-num"><?= (int)($bySource['ical'] ?? 0) ?></span>
      <span class="stat-label">iCal feed</span>
    </div>
    <div class="stat-card">
      <span class="stat-num"><?= (int)($bySource['scraper'] ?? 0) ?></span>
      <span class="stat-label">web scraping</span>
    </div>
    <div class="stat-card">
      <span class="stat-num"><?= $totalMessages ?></span>
      <span class="stat-label">poruka korisnika</span>
    </div>
  </div>

  <div class="detail-actions admin-actions">
    <a class="btn btn-primary" href="import.php">Uvoz podataka iz izvora</a>
    <a class="btn btn-ghost" href="event_form.php">+ Novo događanje (ručno)</a>
    <a class="btn btn-ghost" href="users.php">Korisnici</a>
    <a class="btn btn-ghost" href="gallery.php">Galerija</a>
    <a class="btn btn-ghost" href="../api/events.php?format=xml" target="_blank" rel="noopener">XML export</a>
    <a class="btn btn-ghost" href="../api/events.php?format=json" target="_blank" rel="noopener">JSON export</a>
  </div>

  <h2 class="detail-subtitle">Događanja (<?= count($events) ?>)</h2>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th><th>Naziv</th><th>Kategorija</th>
          <th>Datum</th><th>Izvor</th><th>Akcije</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($events as $ev): ?>
          <tr>
            <td><?= (int)$ev['id'] ?></td>
            <td><a href="../event.php?id=<?= (int)$ev['id'] ?>"><?= e($ev['title']) ?></a></td>
            <td><?= e($ev['category']) ?></td>
            <td class="mono"><?= e(date('d.m.Y. H:i', strtotime($ev['start_datetime']))) ?></td>
            <td><span class="badge badge-<?= e($ev['source']) ?>"><?= e(source_label($ev['source'])) ?></span></td>
            <td class="actions-cell">
              <a class="btn btn-small" href="event_form.php?id=<?= (int)$ev['id'] ?>">Uredi</a>
              <a class="btn btn-small btn-danger" href="delete.php?id=<?= (int)$ev['id'] ?>"
                 onclick="return confirm('Sigurno obrisati ovo događanje?');">Obriši</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <h2 class="detail-subtitle">Poruke s kontakt forme</h2>
  <?php if (!$messages): ?>
    <p>Još nema poruka.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr><th>Datum</th><th>Ime</th><th>E-mail</th><th>Naslov</th><th>Poruka</th><th>Newsletter</th></tr>
        </thead>
        <tbody>
          <?php foreach ($messages as $m): ?>
            <tr>
              <td class="mono"><?= e(date('d.m.Y. H:i', strtotime($m['created_at']))) ?></td>
              <td><?= e($m['first_name'] . ' ' . $m['last_name']) ?> <small>(<?= e($m['country']) ?>)</small></td>
              <td><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></td>
              <td><?= e($m['subject']) ?></td>
              <td><?= e(mb_strimwidth($m['message'], 0, 100, '…')) ?></td>
              <td><?= $m['newsletter'] ? 'da' : 'ne' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
