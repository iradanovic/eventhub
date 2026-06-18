# EventHub — agregator događanja

Studentski projekt iz kolegija Napredno programiranje. Aplikacija **agregira događanja u Zagrebu iz tri različita tipa izvora podataka**, sprema ih u MySQL bazu i nudi kroz responzivno web sučelje te **vlastiti XML/JSON API**.

## Tehnologije

- **HTML5, CSS3** — responzivan dizajn, W3C valjan, bez frameworka
- **PHP 8** — backend, vlastiti parseri i uvoznici
- **MySQL** — pohrana događanja, korisnika i poruka
- **XML i JSON** — vlastiti API endpoint s oba formata + **XSD shema**
- **API integracije** — Ticketmaster Discovery API, iCal feed, web scraping (DOMDocument + XPath)

## Izvori podataka

| Izvor | Tip | Tehnika |
|---|---|---|
| Ticketmaster Discovery API | REST / JSON | cURL + `json_decode()` |
| Javni kalendar (.ics) | iCalendar (RFC 5545) | vlastiti ICS parser |
| Lokalna stranica s događanjima | HTML | DOMDocument + **XPath** |

## Instalacija (XAMPP)

1. Kopirajte mapu projekta u `htdocs/eventhub`
2. Pokrenite **Apache** i **MySQL** u XAMPP-u
3. U phpMyAdminu importirajte **`database.sql`** (kreira bazu `eventhub` s početnim podacima)
4. Po potrebi prilagodite `config.php` (pristup bazi, API ključ)
5. Otvorite `http://localhost/eventhub`

### Administracija (CMS)

- URL: `http://localhost/eventhub/admin/`
- Prijava: **admin** / **admin123**
- Funkcije: statistika, CRUD događanja, pregled poruka, pokretanje uvoza iz sva tri izvora

### Demo način rada

U `config.php` je zadano `DEMO_MODE = true` — uvoz tada čita **lokalne uzorke** iz `data/samples/` (JSON, ICS i HTML), pa sve radi i bez interneta i bez API ključa. Za stvarne podatke:

1. registrirajte se na <https://developer.ticketmaster.com> i upišite ključ u `TICKETMASTER_API_KEY`
2. postavite `DEMO_MODE = false`

## Vlastiti API

```
GET /api/events.php?format=json
GET /api/events.php?format=xml
```

Parametri: `id`, `q`, `category`, `source`, `from`, `to`, `limit`.
XML odgovor je opisan shemom **`api/events.xsd`**. Dokumentacija s primjerima dostupna je na stranici **API** unutar aplikacije.

## Struktura projekta

```
eventhub/
├── index.php              # popis događanja s filtrima i paginacijom
├── event.php              # detalji događanja
├── kontakt.php            # kontakt forma (spremanje u bazu)
├── api-docs.php           # dokumentacija API-ja
├── config.php             # konfiguracija
├── database.sql           # backup baze (shema + početni podaci)
├── api/
│   ├── events.php         # vlastiti API (JSON/XML)
│   └── events.xsd         # XML Schema odgovora
├── admin/                 # CMS: login, dashboard, CRUD, uvoz
├── includes/
│   ├── db.php             # PDO konekcija
│   ├── functions.php      # pomoćne funkcije, upsert, filtri
│   ├── header.php / footer.php
│   └── import/            # ticketmaster.php, ical.php, scraper.php
├── assets/                # css, js
└── data/samples/          # uzorci za DEMO_MODE (JSON, ICS, HTML)
```

## Sigurnost

- PDO **prepared statements** (zaštita od SQL injectiona)
- `htmlspecialchars()` na svim ispisima (zaštita od XSS-a)
- Lozinke hashirane s `password_hash()` (bcrypt)
- Admin stranice zaštićene sesijom

## Korisnici i role

- **Registracija** (`registracija.php`) - otvara račun s rolom `user`
- **Prijava** (`prijava.php`) - zajednička za korisnike i administratore (korisničko ime ili e-mail)
- **Profil** (`profil.php`) - podaci prijavljenog korisnika
- **Administracija korisnika** (`admin/users.php`) - dodavanje, uređivanje, brisanje i promjena rola (samo admin); vlastiti račun nije moguće obrisati ni degradirati

Demo računi: `admin` / `admin123` (administrator) i `ivan` / `ivan123` (korisnik).

## Slike događanja

U admin formi događanja moguće je učitati sliku (JPG/PNG/WebP/SVG, max. 2 MB).
Slike se spremaju u `assets/uploads/`, a prikazuju se na karticama naslovnice
i na stranici detalja. Početna događanja koriste ilustracije iz `assets/img/`.

## Dodatne stranice

- `onama.php` - o projektu, s ugrađenim videom
- `galerija.php` - galerija slika s opisima
- `kontakt.php` - forma s poljima ime, prezime, e-mail, država, newsletter,
  naslov i poruka + ugrađena Google Maps karta

## Napomena uz database.sql

Skripta briše i ponovno kreira tablice (čista instalacija). Nakon importa
uvezena događanja vraćate kroz **Admin → Uvoz podataka iz izvora**.
