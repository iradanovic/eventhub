<?php
/**
 * EventHub - uvoz događanja iz Ticketmaster Discovery API-ja
 * Izvor: REST API koji vraća JSON
 * Dokumentacija: https://developer.ticketmaster.com/products-and-docs/apis/discovery-api/v2/
 */

declare(strict_types=1);

require_once __DIR__ . '/../functions.php';

/**
 * Pokreće uvoz i vraća statistiku: ['inserted' => n, 'updated' => n, 'errors' => [...]]
 */
function import_ticketmaster(): array
{
    $stats = ['source' => 'Ticketmaster API (JSON)', 'inserted' => 0, 'updated' => 0, 'errors' => []];

    if (DEMO_MODE) {
        $raw = @file_get_contents(__DIR__ . '/../../data/samples/ticketmaster_sample.json');
        if ($raw === false) {
            $stats['errors'][] = 'Ne mogu pročitati lokalni uzorak ticketmaster_sample.json.';
            return $stats;
        }
    } else {
        $url = sprintf(
            'https://app.ticketmaster.com/discovery/v2/events.json?apikey=%s&city=%s&countryCode=%s&size=50&sort=date,asc',
            urlencode(TICKETMASTER_API_KEY),
            urlencode(TICKETMASTER_CITY),
            urlencode(TICKETMASTER_COUNTRY)
        );
        $raw = http_get($url);
        if ($raw === null) {
            $stats['errors'][] = 'Neuspješan HTTP zahtjev prema Ticketmaster API-ju. Provjerite API ključ i internet vezu.';
            return $stats;
        }
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $stats['errors'][] = 'Odgovor nije valjani JSON: ' . json_last_error_msg();
        return $stats;
    }

    $events = $data['_embedded']['events'] ?? [];
    if (!$events) {
        $stats['errors'][] = 'API nije vratio niti jedno događanje.';
        return $stats;
    }

    foreach ($events as $ev) {
        try {
            $venue = $ev['_embedded']['venues'][0] ?? [];

            $start = $ev['dates']['start']['dateTime']
                  ?? ($ev['dates']['start']['localDate'] ?? null);
            if ($start === null) {
                continue; // bez datuma događanje nije upotrebljivo
            }
            $startDt = date('Y-m-d H:i:s', strtotime($start));

            $price = null;
            if (!empty($ev['priceRanges'][0])) {
                $pr    = $ev['priceRanges'][0];
                $price = sprintf('%s - %s %s', $pr['min'] ?? '?', $pr['max'] ?? '?', $pr['currency'] ?? '');
            }

            $result = upsert_event([
                'title'          => $ev['name'] ?? 'Bez naziva',
                'description'    => $ev['info'] ?? ($ev['pleaseNote'] ?? null),
                'category'       => $ev['classifications'][0]['segment']['name'] ?? 'Ostalo',
                'venue_name'     => $venue['name'] ?? null,
                'venue_address'  => $venue['address']['line1'] ?? null,
                'city'           => $venue['city']['name'] ?? 'Zagreb',
                'start_datetime' => $startDt,
                'price_info'     => $price,
                'image_url'      => $ev['images'][0]['url'] ?? null,
                'event_url'      => $ev['url'] ?? null,
                'source'         => 'ticketmaster',
                'external_id'    => $ev['id'] ?? md5($ev['name'] . $startDt),
            ]);
            $stats[$result]++;
        } catch (Throwable $e) {
            $stats['errors'][] = 'Greška kod događanja "' . ($ev['name'] ?? '?') . '": ' . $e->getMessage();
        }
    }

    return $stats;
}

/** Jednostavan HTTP GET preko cURL-a s razumnim limitima */
function http_get(string $url): ?string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_USERAGENT      => 'EventHub/1.0 (studentski projekt)',
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    return ($body !== false && $code >= 200 && $code < 300) ? $body : null;
}
