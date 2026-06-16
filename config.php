<?php
/**
 * EventHub - konfiguracija aplikacije
 *
 * Tajne (DB lozinka, API ključ) NE pišu se ovdje nego u config.local.php
 * koji se NE commita na GitHub (vidi .gitignore). Tako javni repo ostaje bez
 * lozinki. Ako config.local.php ne postoji, koriste se zadane (XAMPP) vrijednosti.
 */

declare(strict_types=1);

date_default_timezone_set('Europe/Zagreb');

/* ---------- Zadane (lokalne / XAMPP) vrijednosti ---------- */
$cfg = [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'eventhub',
    'DB_USER' => 'root',
    'DB_PASS' => '',

    'TICKETMASTER_API_KEY' => 'OVDJE_UPISITE_SVOJ_API_KLJUC',

    'APP_URL'   => 'http://localhost/eventhub', // bez završne kose crte
    'DEMO_MODE' => true,
];

/* ---------- Produkcija: učitaj tajne/postavke iz config.local.php ---------- */
if (is_file(__DIR__ . '/config.local.php')) {
    $local = require __DIR__ . '/config.local.php';
    if (is_array($local)) {
        $cfg = array_merge($cfg, $local);
    }
}

/* ---------- Baza podataka ---------- */
define('DB_HOST', $cfg['DB_HOST']);
define('DB_NAME', $cfg['DB_NAME']);
define('DB_USER', $cfg['DB_USER']);
define('DB_PASS', $cfg['DB_PASS']);
define('DB_CHARSET', 'utf8mb4');

/* ---------- Vanjski izvori podataka ---------- */

// Ticketmaster Discovery API (JSON / REST)
define('TICKETMASTER_API_KEY', $cfg['TICKETMASTER_API_KEY']);
define('TICKETMASTER_CITY', 'Zagreb');
define('TICKETMASTER_COUNTRY', 'HR');

// Javni iCal feed (npr. Google Calendar javni .ics link)
define('ICAL_FEED_URL', 'https://www.officeholidays.com/ics/croatia');

// Web scraping lokalne stranice s događanjima
define('SCRAPER_URL', 'https://www.infozagreb.hr/hr/dogadanja');

/*
 * DEMO_MODE = true  -> uvoz čita lokalne uzorke iz data/samples/
 *                      (rad bez interneta i bez API ključa)
 * DEMO_MODE = false -> uvoz dohvaća stvarne podatke s interneta
 */
define('DEMO_MODE', (bool)$cfg['DEMO_MODE']);

/* ---------- Aplikacija ---------- */
define('APP_NAME', 'EventHub');
define('APP_URL', $cfg['APP_URL']);
define('EVENTS_PER_PAGE', 12);
