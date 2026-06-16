<?php
/**
 * EventHub - dokumentacija vlastitog API-ja
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle  = 'API dokumentacija';
$activePage = 'api';
require __DIR__ . '/includes/header.php';

$base = APP_URL . '/api/events.php';
?>

<section class="container page-narrow">
  <h1 class="page-title">EventHub API</h1>
  <p class="page-intro">
    Sva agregirana događanja dostupna su i strojno, kroz vlastiti web servis.
    Isti podaci mogu se dohvatiti u <strong>JSON</strong> ili <strong>XML</strong>
    formatu, a XML odgovor je formalno opisan
    <a href="api/events.xsd" target="_blank" rel="noopener">XSD shemom</a>.
  </p>

  <h2 class="detail-subtitle">Osnovni endpoint</h2>
  <pre class="code-block"><code>GET <?= e($base) ?></code></pre>

  <h2 class="detail-subtitle">Parametri</h2>
  <table class="api-table">
    <thead>
      <tr><th>Parametar</th><th>Opis</th><th>Primjer</th></tr>
    </thead>
    <tbody>
      <tr><td><code>format</code></td><td>Format odgovora: <code>json</code> (zadano) ili <code>xml</code></td><td><code>format=xml</code></td></tr>
      <tr><td><code>id</code></td><td>Jedno događanje po ID-u</td><td><code>id=3</code></td></tr>
      <tr><td><code>q</code></td><td>Pretraga po nazivu, opisu i lokaciji</td><td><code>q=festival</code></td></tr>
      <tr><td><code>category</code></td><td>Filtriranje po kategoriji</td><td><code>category=Glazba</code></td></tr>
      <tr><td><code>source</code></td><td>Izvor: <code>ticketmaster</code>, <code>ical</code>, <code>scraper</code>, <code>manual</code></td><td><code>source=ical</code></td></tr>
      <tr><td><code>from</code> / <code>to</code></td><td>Raspon datuma (YYYY-MM-DD)</td><td><code>from=2026-06-01</code></td></tr>
      <tr><td><code>limit</code></td><td>Maksimalan broj rezultata (1-100)</td><td><code>limit=20</code></td></tr>
    </tbody>
  </table>

  <h2 class="detail-subtitle">Isprobajte odmah</h2>
  <div class="detail-actions">
    <a class="btn btn-primary" href="api/events.php?format=json" target="_blank" rel="noopener">Sva događanja - JSON</a>
    <a class="btn btn-ghost" href="api/events.php?format=xml" target="_blank" rel="noopener">Sva događanja - XML</a>
    <a class="btn btn-ghost" href="api/events.php?format=json&amp;source=ticketmaster" target="_blank" rel="noopener">Samo Ticketmaster - JSON</a>
  </div>

  <h2 class="detail-subtitle">Primjer JSON odgovora</h2>
  <pre class="code-block"><code>{
  "meta": {
    "generated": "2026-06-09T12:00:00+02:00",
    "count": 1,
    "source": "EventHub API v1"
  },
  "events": [
    {
      "id": 1,
      "title": "INmusic festival #18",
      "category": "Glazba",
      "venue": { "name": "Jarun", "city": "Zagreb" },
      "start_datetime": "2026-06-22 16:00:00",
      "source": "manual"
    }
  ]
}</code></pre>

  <h2 class="detail-subtitle">Primjer XML odgovora</h2>
  <pre class="code-block"><code>&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;events generated="2026-06-09T12:00:00+02:00" count="1"
        xsi:noNamespaceSchemaLocation="events.xsd"&gt;
  &lt;event id="1" source="manual"&gt;
    &lt;title&gt;INmusic festival #18&lt;/title&gt;
    &lt;category&gt;Glazba&lt;/category&gt;
    &lt;start_datetime&gt;2026-06-22T16:00:00&lt;/start_datetime&gt;
  &lt;/event&gt;
&lt;/events&gt;</code></pre>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
