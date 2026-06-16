<?php
/**
 * EventHub - konfiguracija aplikacije
 *
 * Prije pokretanja:
 *  1. U phpMyAdminu (XAMPP) importirajte database.sql
 *  2. Po potrebi prilagodite pristupne podatke baze
 *  3. Upišite svoj Ticketmaster API ključ (https://developer.ticketmaster.com)
 */

declare(strict_types=1);

date_default_timezone_set('Europe/Zagreb');

/* ---------- Baza podataka (XAMPP zadane postavke) ---------- */
define('DB_HOST', 'localhost');
define('DB_NAME', 'eventhub');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/* ---------- Vanjski izvori podataka ---------- */

// Ticketmaster Discovery API (JSON / REST)
define('TICKETMASTER_API_KEY', 'OVDJE_UPISITE_SVOJ_API_KLJUC');
define('TICKETMASTER_CITY', 'Zagreb');
define('TICKETMASTER_COUNTRY', 'HR');

// Javni iCal feed (npr. Google Calendar javni .ics link)
define('ICAL_FEED_URL', 'https://www.officeholidays.com/ics/croatia');

// Web scraping lokalne stranice s događanjima
define('SCRAPER_URL', 'https://www.infozagreb.hr/hr/dogadanja');

/*
 * DEMO_MODE = true  -> uvoz čita lokalne uzorke iz data/samples/
 *                      (rad bez interneta i bez API ključa, idealno za snimanje videa)
 * DEMO_MODE = false -> uvoz dohvaća stvarne podatke s interneta
 */
define('DEMO_MODE', true);

/* ---------- Aplikacija ---------- */
define('APP_NAME', 'EventHub');
define('APP_URL', 'http://localhost/eventhub'); // bez završne kose crte
define('EVENTS_PER_PAGE', 12);
