<?php
/**
 * EventHub - početna stranica: popis događanja s filtrima
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$page    = max(1, (int)($_GET['page'] ?? 1));
$filters = [
    'q'        => trim($_GET['q'] ?? ''),
    'category' => trim($_GET['category'] ?? ''),
    'source'   => trim($_GET['source'] ?? ''),
    'limit'    => EVENTS_PER_PAGE,
    'offset'   => ($page - 1) * EVENTS_PER_PAGE,
];

$events     = get_events($filters);
$total      = count_events($filters);
$totalPages = max(1, (int)ceil($total / EVENTS_PER_PAGE));
$categories = get_categories();

$pageTitle  = 'Događanja u Zagrebu';
$activePage = 'index';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container">
    <p class="hero-eyebrow">Ticketmaster API · iCal feed · web scraping</p>
    <h1>Sva zagrebačka<br>događanja.<br><em>Jedno mjesto.</em></h1>
    <p class="hero-sub">
      EventHub automatski skuplja koncerte, izložbe, festivale i sportska
      događanja iz tri različita izvora podataka i nudi ih kroz vlastiti
      XML/JSON API.
    </p>
  </div>
</section>

<section class="container intro-section">
  <div class="intro-grid">
    <div class="intro-text">
      <h2 class="detail-subtitle">Kako EventHub radi?</h2>
      <p>
        Zagrebačka kulturna i sportska scena živi na desecima različitih
        stranica: prodavatelji ulaznica, gradski kalendari, portali i društvene
        mreže. EventHub te izvore automatski objedinjuje, pa umjesto pet
        otvorenih kartica trebate samo jednu.
      </p>
      <p>
        Podaci stižu iz tri smjera: Ticketmaster Discovery API isporučuje
        koncerte i predstave u JSON formatu, javni iCal kalendari čitaju se
        vlastitim parserom ICS zapisa, a lokalne stranice bez API-ja obrađuju
        se web scrapingom. Svako događanje sprema se u bazu samo jednom, bez
        obzira na to koliko se puta uvoz pokrene.
      </p>
      <p>
        Prikupljeno zatim dijelimo dalje: vlastiti API endpoint vraća sva
        događanja u JSON ili XML formatu, opisan XSD shemom, pa i vaša
        aplikacija može graditi na našim podacima. Registrirajte se, istražite
        kalendar i javite nam koje događanje nedostaje.
      </p>
    </div>
    <figure class="intro-figure">
      <img src="assets/img/hero-zagreb.svg" alt="Stilizirani prikaz zagrebačke večernje scene s istaknutim događanjima">
      <figcaption>Koncerti, izložbe i festivali - tri izvora podataka, jedan kalendar.</figcaption>
    </figure>
  </div>
</section>

<section class="container">
  <form class="filters" method="get" action="index.php" aria-label="Filtriranje događanja">
    <div class="filter-field grow">
      <label for="q">Pretraga</label>
      <input type="search" id="q" name="q" value="<?= e($filters['q']) ?>"
             placeholder="npr. koncert, festival, Jarun...">
    </div>
    <div class="filter-field">
      <label for="category">Kategorija</label>
      <select id="category" name="category">
        <option value="">Sve kategorije</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= e($cat) ?>" <?= $filters['category'] === $cat ? 'selected' : '' ?>>
            <?= e($cat) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="filter-field">
      <label for="source">Izvor podataka</label>
      <select id="source" name="source">
        <option value="">Svi izvori</option>
        <option value="ticketmaster" <?= $filters['source'] === 'ticketmaster' ? 'selected' : '' ?>>Ticketmaster API</option>
        <option value="ical" <?= $filters['source'] === 'ical' ? 'selected' : '' ?>>iCal feed</option>
        <option value="scraper" <?= $filters['source'] === 'scraper' ? 'selected' : '' ?>>Web scraping</option>
        <option value="manual" <?= $filters['source'] === 'manual' ? 'selected' : '' ?>>Ručni unos</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Filtriraj</button>
  </form>

  <p class="results-count">
    <?php if ($filters['q'] || $filters['category'] || $filters['source']): ?>
      Pronađeno: <strong><?= $total ?></strong> događanja
      · <a href="index.php">poništi filtre</a>
    <?php else: ?>
      Nadolazećih događanja: <strong><?= $total ?></strong>
    <?php endif; ?>
  </p>

  <?php if (!$events): ?>
    <div class="empty-state">
      <h2>Nema rezultata</h2>
      <p>Pokušajte s drugim pojmom ili poništite filtre. Ako je baza prazna,
         prijavite se u <a href="admin/index.php">administraciju</a> i pokrenite uvoz podataka.</p>
    </div>
  <?php else: ?>
    <div class="event-grid">
      <?php foreach ($events as $ev): ?>
        <article class="ticket">
          <?php if ($ev['image_url']): ?>
            <div class="ticket-image">
              <img src="<?= e($ev['image_url']) ?>" alt="" loading="lazy">
            </div>
          <?php endif; ?>
          <div class="ticket-inner">
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
            <h2 class="ticket-title">
              <a href="event.php?id=<?= (int)$ev['id'] ?>"><?= e($ev['title']) ?></a>
            </h2>
            <p class="ticket-when"><?= e(format_event_date($ev['start_datetime'])) ?></p>
            <?php if ($ev['venue_name']): ?>
              <p class="ticket-venue"><?= e($ev['venue_name']) ?><?= $ev['city'] ? ', ' . e($ev['city']) : '' ?></p>
            <?php endif; ?>
            <?php if ($ev['price_info']): ?>
              <p class="ticket-price"><?= e($ev['price_info']) ?></p>
            <?php endif; ?>
          </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
      <nav class="pagination" aria-label="Stranice rezultata">
        <?php
        $qs = $_GET;
        for ($p = 1; $p <= $totalPages; $p++):
            $qs['page'] = $p;
        ?>
          <a href="index.php?<?= e(http_build_query($qs)) ?>"
             <?= $p === $page ? 'class="active" aria-current="page"' : '' ?>><?= $p ?></a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
