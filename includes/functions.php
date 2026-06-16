<?php
/**
 * EventHub - pomoćne funkcije
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/** Sigurno ispisivanje u HTML */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Datum u hrvatskom formatu, npr. "sub, 22. lip 2026. · 16:00" */
function format_event_date(string $datetime): string
{
    $days   = ['ned', 'pon', 'uto', 'sri', 'čet', 'pet', 'sub'];
    $months = [1 => 'sij', 'velj', 'ožu', 'tra', 'svi', 'lip',
               'srp', 'kol', 'ruj', 'lis', 'stu', 'pro'];

    $ts = strtotime($datetime);
    if ($ts === false) {
        return $datetime;
    }

    $out = $days[(int)date('w', $ts)] . ', '
         . date('j', $ts) . '. '
         . $months[(int)date('n', $ts)] . ' '
         . date('Y', $ts) . '.';

    if (date('H:i', $ts) !== '00:00') {
        $out .= ' · ' . date('H:i', $ts);
    }

    return $out;
}

/** Ljudski naziv izvora podataka */
function source_label(string $source): string
{
    return match ($source) {
        'ticketmaster' => 'Ticketmaster API',
        'ical'         => 'iCal feed',
        'scraper'      => 'Web scraping',
        default        => 'Ručni unos',
    };
}

/**
 * Upsert događanja: ako (source, external_id) postoji -> UPDATE, inače INSERT.
 * Vraća 'inserted' ili 'updated'.
 */
function upsert_event(array $ev): string
{
    $pdo = db();

    $sql = "INSERT INTO events
              (title, description, category, venue_name, venue_address, city,
               start_datetime, end_datetime, price_info, image_url, event_url,
               source, external_id)
            VALUES
              (:title, :description, :category, :venue_name, :venue_address, :city,
               :start_datetime, :end_datetime, :price_info, :image_url, :event_url,
               :source, :external_id)
            ON DUPLICATE KEY UPDATE
               title = VALUES(title),
               description = VALUES(description),
               category = VALUES(category),
               venue_name = VALUES(venue_name),
               venue_address = VALUES(venue_address),
               city = VALUES(city),
               start_datetime = VALUES(start_datetime),
               end_datetime = VALUES(end_datetime),
               price_info = VALUES(price_info),
               image_url = VALUES(image_url),
               event_url = VALUES(event_url)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title'          => mb_substr($ev['title'] ?? 'Bez naziva', 0, 255),
        ':description'    => $ev['description'] ?? null,
        ':category'       => $ev['category'] ?? 'Ostalo',
        ':venue_name'     => $ev['venue_name'] ?? null,
        ':venue_address'  => $ev['venue_address'] ?? null,
        ':city'           => $ev['city'] ?? 'Zagreb',
        ':start_datetime' => $ev['start_datetime'],
        ':end_datetime'   => $ev['end_datetime'] ?? null,
        ':price_info'     => $ev['price_info'] ?? null,
        ':image_url'      => $ev['image_url'] ?? null,
        ':event_url'      => $ev['event_url'] ?? null,
        ':source'         => $ev['source'],
        ':external_id'    => $ev['external_id'],
    ]);

    // rowCount: 1 = insert, 2 = update (MySQL ponašanje za ON DUPLICATE KEY)
    return $stmt->rowCount() === 1 ? 'inserted' : 'updated';
}

/**
 * Dohvat događanja s filtrima.
 * $filters: q, category, source, from, to, limit, offset, include_past
 */
function get_events(array $filters = []): array
{
    $pdo    = db();
    $where  = [];
    $params = [];

    if (empty($filters['include_past'])) {
        $where[] = 'start_datetime >= NOW() - INTERVAL 1 DAY';
    }
    if (!empty($filters['q'])) {
        $where[]      = '(title LIKE :q OR description LIKE :q OR venue_name LIKE :q)';
        $params[':q'] = '%' . $filters['q'] . '%';
    }
    if (!empty($filters['category'])) {
        $where[]             = 'category = :category';
        $params[':category'] = $filters['category'];
    }
    if (!empty($filters['source'])) {
        $where[]           = 'source = :source';
        $params[':source'] = $filters['source'];
    }
    if (!empty($filters['from'])) {
        $where[]         = 'start_datetime >= :from';
        $params[':from'] = $filters['from'] . ' 00:00:00';
    }
    if (!empty($filters['to'])) {
        $where[]       = 'start_datetime <= :to';
        $params[':to'] = $filters['to'] . ' 23:59:59';
    }

    $sql = 'SELECT * FROM events';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY start_datetime ASC';

    $limit  = max(1, min(100, (int)($filters['limit'] ?? EVENTS_PER_PAGE)));
    $offset = max(0, (int)($filters['offset'] ?? 0));
    $sql   .= " LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/** Ukupan broj događanja uz iste filtre (za paginaciju) */
function count_events(array $filters = []): int
{
    $filters['limit']  = 100;
    $filters['offset'] = 0;

    $pdo    = db();
    $where  = [];
    $params = [];

    if (empty($filters['include_past'])) {
        $where[] = 'start_datetime >= NOW() - INTERVAL 1 DAY';
    }
    if (!empty($filters['q'])) {
        $where[]      = '(title LIKE :q OR description LIKE :q OR venue_name LIKE :q)';
        $params[':q'] = '%' . $filters['q'] . '%';
    }
    if (!empty($filters['category'])) {
        $where[]             = 'category = :category';
        $params[':category'] = $filters['category'];
    }
    if (!empty($filters['source'])) {
        $where[]           = 'source = :source';
        $params[':source'] = $filters['source'];
    }

    $sql = 'SELECT COUNT(*) FROM events';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int)$stmt->fetchColumn();
}

/** Sve kategorije koje postoje u bazi (za filter padajući izbornik) */
function get_categories(): array
{
    return db()->query(
        'SELECT DISTINCT category FROM events ORDER BY category'
    )->fetchAll(PDO::FETCH_COLUMN);
}

/* ============================================================
 * Korisnici, sesije i role
 * ============================================================ */

/** Pokreće sesiju ako već nije pokrenuta */
function ensure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/** Trenutno prijavljeni korisnik (iz sesije) ili null */
function current_user(): ?array
{
    ensure_session();
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'       => (int)$_SESSION['user_id'],
        'username' => (string)($_SESSION['user_username'] ?? ''),
        'name'     => (string)($_SESSION['user_name'] ?? ''),
        'role'     => (string)($_SESSION['user_role'] ?? 'user'),
    ];
}

/** Je li prijavljeni korisnik administrator? */
function is_admin(): bool
{
    $u = current_user();
    return $u !== null && $u['role'] === 'admin';
}

/** Postavlja sesiju nakon uspješne prijave */
function login_user(array $row): void
{
    ensure_session();
    session_regenerate_id(true);
    $_SESSION['user_id']       = (int)$row['id'];
    $_SESSION['user_username'] = $row['username'];
    $_SESSION['user_name']     = trim($row['first_name'] . ' ' . $row['last_name']);
    $_SESSION['user_role']     = $row['role'];
}

/** Popis država za padajuće izbornike (kontakt, registracija) */
function country_options(): array
{
    return ['Hrvatska', 'Slovenija', 'Bosna i Hercegovina', 'Srbija',
            'Austrija', 'Njemačka', 'Italija', 'Mađarska', 'Ostalo'];
}
