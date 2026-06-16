-- ============================================================
-- EventHub - agregator događanja
-- Backup baze podataka (MySQL / MariaDB, XAMPP)
-- Import: phpMyAdmin -> Import -> database.sql
-- NAPOMENA: skripta briše i ponovno kreira tablice (čista
-- instalacija). Uvezena događanja vraćaju se kroz Admin -> Uvoz.
-- ============================================================

CREATE DATABASE IF NOT EXISTS eventhub
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_croatian_ci;

USE eventhub;

DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS events;

-- ------------------------------------------------------------
-- Tablica: events (događanja iz svih izvora)
-- ------------------------------------------------------------
CREATE TABLE events (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title           VARCHAR(255) NOT NULL,
  description     TEXT NULL,
  category        VARCHAR(100) NOT NULL DEFAULT 'Ostalo',
  venue_name      VARCHAR(255) NULL,
  venue_address   VARCHAR(255) NULL,
  city            VARCHAR(100) NOT NULL DEFAULT 'Zagreb',
  start_datetime  DATETIME NOT NULL,
  end_datetime    DATETIME NULL,
  price_info      VARCHAR(150) NULL,
  image_url       VARCHAR(500) NULL,
  event_url       VARCHAR(500) NULL,
  source          ENUM('manual','ticketmaster','ical','scraper') NOT NULL DEFAULT 'manual',
  external_id     VARCHAR(190) NULL,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                  ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_source_external (source, external_id),
  KEY idx_start (start_datetime),
  KEY idx_category (category)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tablica: users (registrirani korisnici + administratori, role)
-- ------------------------------------------------------------
CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  first_name    VARCHAR(80)  NOT NULL,
  last_name     VARCHAR(80)  NOT NULL,
  email         VARCHAR(190) NOT NULL,
  username      VARCHAR(60)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','user') NOT NULL DEFAULT 'user',
  country       VARCHAR(80)  NOT NULL DEFAULT 'Hrvatska',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_username (username),
  UNIQUE KEY uq_email (email)
) ENGINE=InnoDB;

-- Zadani administrator -> admin / admin123
-- Demo korisnik       -> ivan / ivan123
INSERT INTO users (first_name, last_name, email, username, password_hash, role, country) VALUES
('Administrator', 'Sustava', 'admin@eventhub.local', 'admin',
 '$2y$10$hawaw79aLiJoVA2RMSOeweDSrbsT5epkp6pw1k6tkPBcFwcNbAwkC', 'admin', 'Hrvatska'),
('Ivan', 'Demo', 'ivan@eventhub.local', 'ivan',
 '$2y$10$y0XycjqqnDlHJHMx.COyXeUF5qXDLF7W0sMK0PyiDdBg8XEqg6q/S', 'user', 'Hrvatska');

-- ------------------------------------------------------------
-- Tablica: messages (poruke s kontakt forme)
-- ------------------------------------------------------------
CREATE TABLE messages (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  first_name VARCHAR(80)  NOT NULL,
  last_name  VARCHAR(80)  NOT NULL,
  email      VARCHAR(190) NOT NULL,
  country    VARCHAR(80)  NOT NULL,
  newsletter TINYINT(1)   NOT NULL DEFAULT 0,
  subject    VARCHAR(150) NOT NULL,
  message    TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Početni (ručno uneseni) podaci - source = 'manual'
-- ------------------------------------------------------------
INSERT INTO events
(title, description, category, venue_name, venue_address, city,
 start_datetime, end_datetime, price_info, image_url, event_url, source, external_id)
VALUES
('INmusic festival #18',
 'Najveći hrvatski open-air festival na jarunskim otocima. Tri dana glazbe, kampiranja i međunarodnih izvođača.',
 'Glazba', 'Jarun', 'Aleja Matije Ljubeka 1', 'Zagreb',
 '2026-06-22 16:00:00', '2026-06-25 02:00:00', 'od 80 EUR',
 'assets/img/ev-inmusic.svg',
 'https://www.inmusicfestival.com', 'manual', 'manual-inmusic-2026'),

('Zagreb Film Festival - ljetno izdanje',
 'Projekcije nagrađivanih europskih filmova na otvorenom u Tuškancu.',
 'Film', 'Ljetna pozornica Tuškanac', 'Tuškanac 1', 'Zagreb',
 '2026-07-04 21:00:00', NULL, 'ulaz slobodan',
 'assets/img/ev-zff.svg',
 'https://zff.hr', 'manual', 'manual-zff-ljeto-2026'),

('Hrvatska - Italija (kvalifikacije)',
 'Kvalifikacijska nogometna utakmica na stadionu Maksimir.',
 'Sport', 'Stadion Maksimir', 'Maksimirska cesta 128', 'Zagreb',
 '2026-09-07 20:45:00', NULL, 'od 15 EUR',
 'assets/img/ev-sport.svg',
 'https://hns.family', 'manual', 'manual-cro-ita-2026'),

('Interliber 2026',
 'Međunarodni sajam knjiga i učila na Zagrebačkom velesajmu.',
 'Sajam', 'Zagrebački velesajam', 'Avenija Dubrovnik 15', 'Zagreb',
 '2026-11-10 09:00:00', '2026-11-15 20:00:00', 'ulaz slobodan',
 'assets/img/ev-interliber.svg',
 'https://www.zv.hr', 'manual', 'manual-interliber-2026'),

('Izložba: Klimt i bečka secesija',
 'Gostujuća izložba djela bečke secesije u Muzeju za umjetnost i obrt.',
 'Izložba', 'Muzej za umjetnost i obrt', 'Trg Republike Hrvatske 10', 'Zagreb',
 '2026-06-15 10:00:00', '2026-09-30 19:00:00', '10 EUR',
 'assets/img/ev-klimt.svg',
 'https://www.muo.hr', 'manual', 'manual-klimt-2026');
