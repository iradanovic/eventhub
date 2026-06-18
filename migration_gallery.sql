-- ============================================================
-- EventHub - migracija: dodavanje tablice gallery
-- Za baze koje su nastale PRIJE uvođenja admin galerije (ne diraju
-- se postojeće tablice events/users/messages). Pokrenite samo
-- jednom - u phpMyAdminu na produkcijskoj bazi (Import -> ova
-- datoteka) ili preko mysql CLI-a.
-- ============================================================

CREATE TABLE IF NOT EXISTS gallery (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  image_url  VARCHAR(500) NOT NULL,
  alt_text   VARCHAR(150) NOT NULL,
  caption    VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO gallery (image_url, alt_text, caption) VALUES
('assets/img/gal-1.svg', 'Publika na koncertu',
 'Atmosfera s prošlogodišnjeg ljetnog koncerta na Jarunu.'),
('assets/img/gal-2.svg', 'Ljetno kino',
 'Projekcija pod zvijezdama na Ljetnoj pozornici Tuškanac.'),
('assets/img/gal-3.svg', 'Sajamski paviljon',
 'Interliber - najveći sajam knjiga na Zagrebačkom velesajmu.'),
('assets/img/gal-4.svg', 'Galerijski postav',
 'Postav izložbe bečke secesije u Muzeju za umjetnost i obrt.'),
('assets/img/gal-5.svg', 'Ulični festival',
 'Cest is d\'Best - ulični zabavljači u centru grada.'),
('assets/img/gal-6.svg', 'Advent na Zrinjevcu',
 'Zimska čarolija ispod platana - nagrađivani zagrebački Advent.');
