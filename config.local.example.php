<?php
/**
 * EventHub - PRODUKCIJSKE postavke (primjer)
 *
 * UPUTE ZA CPANEL (eventhub.kodistik.com):
 *  1. Kopirajte ovu datoteku u "config.local.php" (u istoj mapi).
 *  2. Upišite stvarne podatke baze i Ticketmaster ključ.
 *  3. config.local.php se NE postavlja na GitHub (vidi .gitignore) -
 *     tako lozinke ostaju tajne iako je repozitorij javan.
 *
 * Vraća polje koje config.php spaja preko svojih zadanih vrijednosti.
 */

return [
    // Baza (cPanel imena su prefiksirana, npr. kodistik_eventhub)
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'kodistik_eventhub',
    'DB_USER' => 'kodistik_eventuser',
    'DB_PASS' => 'UPISITE_LOZINKU_BAZE',

    // Ticketmaster Discovery API kljuc -> https://developer.ticketmaster.com
    'TICKETMASTER_API_KEY' => 'UPISITE_TICKETMASTER_KLJUC',

    // Javni URL bez zavrsne kose crte
    'APP_URL' => 'https://eventhub.kodistik.com',

    // false = uvoz dohvaca stvarne podatke s interneta
    'DEMO_MODE' => false,
];
