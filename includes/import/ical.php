<?php
/**
 * EventHub - uvoz događanja iz javnog iCal (.ics) feeda
 * Izvor: iCalendar format (RFC 5545)
 */

declare(strict_types=1);

require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/ticketmaster.php'; // zbog http_get()

function import_ical(): array
{
    $stats = ['source' => 'iCal feed (.ics)', 'inserted' => 0, 'updated' => 0, 'errors' => []];

    if (DEMO_MODE) {
        $raw = @file_get_contents(__DIR__ . '/../../data/samples/sample_calendar.ics');
        if ($raw === false) {
            $stats['errors'][] = 'Ne mogu pročitati lokalni uzorak sample_calendar.ics.';
            return $stats;
        }
    } else {
        $raw = http_get(ICAL_FEED_URL);
        if ($raw === null) {
            $stats['errors'][] = 'Neuspješno dohvaćanje iCal feeda: ' . ICAL_FEED_URL;
            return $stats;
        }
    }

    $vevents = parse_ics($raw);
    if (!$vevents) {
        $stats['errors'][] = 'U feedu nije pronađen niti jedan VEVENT blok.';
        return $stats;
    }

    foreach ($vevents as $ev) {
        if (empty($ev['DTSTART']) || empty($ev['SUMMARY'])) {
            continue;
        }

        $start = ics_datetime($ev['DTSTART']);
        $end   = isset($ev['DTEND']) ? ics_datetime($ev['DTEND']) : null;
        if ($start === null) {
            continue;
        }

        $result = upsert_event([
            'title'          => $ev['SUMMARY'],
            'description'    => $ev['DESCRIPTION'] ?? null,
            'category'       => $ev['CATEGORIES'] ?? 'Kalendar',
            'venue_name'     => $ev['LOCATION'] ?? null,
            'start_datetime' => $start,
            'end_datetime'   => $end,
            'event_url'      => $ev['URL'] ?? null,
            'source'         => 'ical',
            'external_id'    => $ev['UID'] ?? md5($ev['SUMMARY'] . $start),
        ]);
        $stats[$result]++;
    }

    return $stats;
}

/**
 * Minimalni ICS parser: vraća polje VEVENT blokova kao asocijativna polja.
 * Podržava "folding" linija (nastavak retka počinje razmakom ili tabom).
 */
function parse_ics(string $raw): array
{
    // 1. Unfold: spoji prelomljene linije
    $raw   = str_replace(["\r\n", "\r"], "\n", $raw);
    $raw   = preg_replace('/\n[ \t]/', '', $raw);
    $lines = explode("\n", $raw);

    $events  = [];
    $current = null;

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === 'BEGIN:VEVENT') {
            $current = [];
            continue;
        }
        if ($line === 'END:VEVENT') {
            if ($current !== null) {
                $events[] = $current;
            }
            $current = null;
            continue;
        }
        if ($current === null || !str_contains($line, ':')) {
            continue;
        }

        [$key, $value] = explode(':', $line, 2);

        // odbaci parametre, npr. DTSTART;TZID=Europe/Zagreb -> DTSTART
        $key = strtoupper(explode(';', $key)[0]);

        // odescapiraj ICS specijalne znakove
        $value = str_replace(['\\n', '\\,', '\\;'], ["\n", ',', ';'], $value);

        $current[$key] = $value;
    }

    return $events;
}

/**
 * Pretvara ICS datum u MySQL DATETIME.
 * Podržani formati: 20260622T160000Z, 20260622T160000, 20260622
 */
function ics_datetime(string $value): ?string
{
    $value = trim($value);

    if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})Z?$/', $value, $m)) {
        $iso = sprintf('%s-%s-%s %s:%s:%s', $m[1], $m[2], $m[3], $m[4], $m[5], $m[6]);
        if (str_ends_with($value, 'Z')) {
            // UTC -> lokalno vrijeme
            $dt = new DateTime($iso, new DateTimeZone('UTC'));
            $dt->setTimezone(new DateTimeZone('Europe/Zagreb'));
            return $dt->format('Y-m-d H:i:s');
        }
        return $iso;
    }

    if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $value, $m)) {
        return sprintf('%s-%s-%s 00:00:00', $m[1], $m[2], $m[3]);
    }

    return null;
}
