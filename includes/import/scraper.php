<?php
/**
 * EventHub - uvoz događanja web scrapingom lokalne stranice
 * Tehnika: DOMDocument + DOMXPath (XPath izrazi za izvlačenje podataka iz HTML-a)
 *
 * Napomena: struktura vanjskih stranica se mijenja, pa su XPath izrazi
 * izdvojeni u konstantu $selectors radi lakšeg održavanja. U DEMO_MODE
 * koristi se lokalna kopija stranice iz data/samples/sample_local.html.
 */

declare(strict_types=1);

require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/ticketmaster.php'; // zbog http_get()

function import_scraper(): array
{
    $stats = ['source' => 'Web scraping (HTML + XPath)', 'inserted' => 0, 'updated' => 0, 'errors' => []];

    if (DEMO_MODE) {
        $html = @file_get_contents(__DIR__ . '/../../data/samples/sample_local.html');
        if ($html === false) {
            $stats['errors'][] = 'Ne mogu pročitati lokalni uzorak sample_local.html.';
            return $stats;
        }
    } else {
        $html = http_get(SCRAPER_URL);
        if ($html === null) {
            $stats['errors'][] = 'Neuspješno dohvaćanje stranice: ' . SCRAPER_URL;
            return $stats;
        }
    }

    // XPath selektori za strukturu stranice s događanjima
    $selectors = [
        'item'     => "//article[contains(@class,'event')]",
        'title'    => ".//h3/a",
        'url'      => ".//h3/a/@href",
        'datetime' => ".//time/@datetime",
        'venue'    => ".//*[contains(@class,'venue')]",
        'category' => ".//*[contains(@class,'category')]",
        'desc'     => ".//*[contains(@class,'summary')]",
    ];

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);            // toleriraj neuredan HTML
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $items = $xpath->query($selectors['item']);

    if ($items === false || $items->length === 0) {
        $stats['errors'][] = 'XPath izraz nije pronašao niti jedno događanje - struktura stranice se vjerojatno promijenila.';
        return $stats;
    }

    foreach ($items as $item) {
        $title = xp_text($xpath, $selectors['title'], $item);
        $dt    = xp_text($xpath, $selectors['datetime'], $item);

        if ($title === null || $dt === null) {
            continue;
        }

        $start = date('Y-m-d H:i:s', strtotime($dt));
        $url   = xp_text($xpath, $selectors['url'], $item);

        // relativni link pretvori u apsolutni
        if ($url !== null && !str_starts_with($url, 'http')) {
            $base = DEMO_MODE ? 'https://www.infozagreb.hr' : rtrim(parse_url(SCRAPER_URL, PHP_URL_SCHEME) . '://' . parse_url(SCRAPER_URL, PHP_URL_HOST), '/');
            $url  = $base . '/' . ltrim($url, '/');
        }

        $result = upsert_event([
            'title'          => $title,
            'description'    => xp_text($xpath, $selectors['desc'], $item),
            'category'       => xp_text($xpath, $selectors['category'], $item) ?? 'Lokalno',
            'venue_name'     => xp_text($xpath, $selectors['venue'], $item),
            'start_datetime' => $start,
            'event_url'      => $url,
            'source'         => 'scraper',
            'external_id'    => md5($title . $start),
        ]);
        $stats[$result]++;
    }

    return $stats;
}

/** Vraća trimani tekst prvog čvora koji odgovara XPath izrazu, ili null */
function xp_text(DOMXPath $xpath, string $expr, DOMNode $context): ?string
{
    $nodes = $xpath->query($expr, $context);
    if ($nodes === false || $nodes->length === 0) {
        return null;
    }
    $text = trim($nodes->item(0)->textContent);

    return $text !== '' ? $text : null;
}
