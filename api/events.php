<?php
/**
 * EventHub API - vlastiti web servis
 *
 * GET /api/events.php
 *   ?format=json|xml   (zadano: json)
 *   ?id=5              (jedno događanje)
 *   ?category=Glazba
 *   ?source=ticketmaster|ical|scraper|manual
 *   ?q=festival
 *   ?from=2026-06-01&to=2026-12-31
 *   ?limit=20
 *
 * XML odgovor je opisan shemom events.xsd (u istom direktoriju).
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$format = strtolower($_GET['format'] ?? 'json');

/* ---------- Dohvat podataka ---------- */
if (isset($_GET['id'])) {
    $stmt = db()->prepare('SELECT * FROM events WHERE id = :id');
    $stmt->execute([':id' => (int)$_GET['id']]);
    $events = $stmt->fetchAll();
} else {
    $events = get_events([
        'q'            => trim($_GET['q'] ?? ''),
        'category'     => trim($_GET['category'] ?? ''),
        'source'       => trim($_GET['source'] ?? ''),
        'from'         => trim($_GET['from'] ?? ''),
        'to'           => trim($_GET['to'] ?? ''),
        'limit'        => (int)($_GET['limit'] ?? 50),
        'include_past' => true,
    ]);
}

/* ---------- XML izlaz ---------- */
if ($format === 'xml') {
    header('Content-Type: application/xml; charset=UTF-8');

    $dom               = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;

    $root = $dom->createElement('events');
    $root->setAttribute('generated', date('c'));
    $root->setAttribute('count', (string)count($events));
    $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $root->setAttribute('xsi:noNamespaceSchemaLocation', 'events.xsd');
    $dom->appendChild($root);

    foreach ($events as $ev) {
        $node = $dom->createElement('event');
        $node->setAttribute('id', (string)$ev['id']);
        $node->setAttribute('source', $ev['source']);

        $fields = [
            'title'          => $ev['title'],
            'description'    => $ev['description'],
            'category'       => $ev['category'],
            'venue_name'     => $ev['venue_name'],
            'venue_address'  => $ev['venue_address'],
            'city'           => $ev['city'],
            'start_datetime' => str_replace(' ', 'T', $ev['start_datetime']),
            'end_datetime'   => $ev['end_datetime'] ? str_replace(' ', 'T', $ev['end_datetime']) : null,
            'price_info'     => $ev['price_info'],
            'event_url'      => $ev['event_url'],
        ];

        foreach ($fields as $name => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $child = $dom->createElement($name);
            $child->appendChild($dom->createTextNode((string)$value));
            $node->appendChild($child);
        }

        $root->appendChild($node);
    }

    echo $dom->saveXML();
    exit;
}

/* ---------- JSON izlaz (zadano) ---------- */
header('Content-Type: application/json; charset=UTF-8');

echo json_encode(
    [
        'meta' => [
            'generated' => date('c'),
            'count'     => count($events),
            'source'    => 'EventHub API v1',
        ],
        'events' => array_map(static function (array $ev): array {
            return [
                'id'             => (int)$ev['id'],
                'title'          => $ev['title'],
                'description'    => $ev['description'],
                'category'       => $ev['category'],
                'venue'          => [
                    'name'    => $ev['venue_name'],
                    'address' => $ev['venue_address'],
                    'city'    => $ev['city'],
                ],
                'start_datetime' => $ev['start_datetime'],
                'end_datetime'   => $ev['end_datetime'],
                'price_info'     => $ev['price_info'],
                'event_url'      => $ev['event_url'],
                'source'         => $ev['source'],
            ];
        }, $events),
    ],
    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
);
