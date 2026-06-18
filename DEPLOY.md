# EventHub - objava na cPanel (eventhub.kodistik.com)

Kratke upute za postavljanje projekta na GitHub i na poddomenu preko SSH-a.

## 1. GitHub (s računala)

U mapi projekta (`C:\xampp\htdocs\eventhub`):

```bash
git init
git add .
git commit -m "EventHub - agregator dogadanja"
git branch -M main
git remote add origin https://github.com/TVOJ-USER/eventhub.git
git push -u origin main
```

Repozitorij neka bude **public**. Datoteka `config.local.php` se NE postavlja
na GitHub (nalazi se u `.gitignore`), pa lozinke i API ključ ostaju tajni.

## 2. SSH ključ (cPanel -> SSH Access)

1. **Generate a New Key** (RSA, 2048+ bita, po želji passphrase).
2. Pod **Public Keys** -> **Manage** -> **Authorize**.
3. Pod **Private Keys** -> **View/Download** (za PuTTY preuzmite `.ppk`).
4. Spajanje (port kod shared hostinga često nije 22 - provjerite kod providera):
   ```bash
   ssh -p PORT kodistik_user@kodistik.com
   ```

## 3. Poddomena (cPanel -> Domains / Subdomains)

Kreirajte poddomenu `eventhub`. Zapamtite njezin **document root**
(npr. `~/eventhub.kodistik.com` ili `~/public_html/eventhub`).

## 4. Git clone na server (preko SSH)

```bash
cd ~/eventhub.kodistik.com        # document root poddomene
git clone https://github.com/TVOJ-USER/eventhub.git .
```
Ubuduće za update: `git pull`.

## 5. Baza (cPanel -> MySQL Databases)

1. Kreirajte bazu (npr. `kodistik_eventhub`) i korisnika s lozinkom;
   dodijelite korisniku sva prava na bazi.
2. cPanel -> **phpMyAdmin** -> odaberite tu bazu -> **Import** ->
   uvezite **`database_cpanel.sql`** (verzija bez `CREATE DATABASE`/`USE`).

## 6. Produkcijske postavke (config.local.php)

Na serveru kopirajte predložak i upišite stvarne podatke:

```bash
cp config.local.example.php config.local.php
nano config.local.php
```

Ispunite: `DB_NAME`, `DB_USER`, `DB_PASS` (s prefiksom kao u cPanelu),
`TICKETMASTER_API_KEY`, `APP_URL = https://eventhub.kodistik.com`,
`DEMO_MODE = false`.

> Ticketmaster ključ (besplatno): https://developer.ticketmaster.com -> registracija
> -> My Apps -> kopirajte **Consumer Key**.

## 7. Dozvole za upload slika

```bash
chmod 755 assets/uploads
```

## 8. Provjera

- Otvorite `https://eventhub.kodistik.com` -> učitava se naslovnica.
- Prijava: `admin` / `admin123` -> **Admin -> Uvoz podataka iz izvora** ->
  pokrenite uvoz (s pravim ključem dohvaća živa događanja).
- Provjerite API: `https://eventhub.kodistik.com/api/events.php?format=json`
  i `...?format=xml`.

## Lokalni razvoj (XAMPP)

Bez `config.local.php` aplikacija koristi zadane XAMPP postavke
(`root`, bez lozinke, baza `eventhub`, `DEMO_MODE = true`). Lokalno uvezite
`database.sql` (ima `CREATE DATABASE`).

## Ažuriranje postojeće baze (dodana tablica gallery)

Ako je baza uvezena PRIJE uvođenja admin galerije, `database_cpanel.sql` se
ne smije ponovno uvoziti (briše i nanovo kreira tablice, pa bi se izgubila
postojeća događanja i korisnici). Umjesto toga, u phpMyAdminu -> Import
uvezite samo **`migration_gallery.sql`** - dodaje tablicu `gallery` i
početne slike, bez diranja ostalih tablica.
