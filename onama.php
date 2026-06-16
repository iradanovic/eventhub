<?php
/**
 * EventHub - O nama (naslov, podnaslov, odlomci, ugrađeni video)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle  = 'O nama';
$activePage = 'onama';
require __DIR__ . '/includes/header.php';
?>

<section class="container page-narrow">
  <h1 class="page-title">O nama</h1>
  <h2 class="page-subtitle">Sva zagrebačka događanja na jednom mjestu</h2>

  <div class="prose">
    <p>
      EventHub je studentski projekt nastao iz jednostavne frustracije: informacije
      o koncertima, izložbama, utakmicama i festivalima u Zagrebu raspršene su po
      desecima stranica, kalendara i društvenih mreža. Tko želi znati što se događa
      ovaj vikend, mora otvoriti pet kartica u pregledniku - ili propustiti nešto
      dobro. Mi smo te izvore spojili u jedan pregledan kalendar.
    </p>
    <p>
      Tehnički, EventHub je agregator: podatke automatski prikuplja iz Ticketmaster
      Discovery API-ja (REST/JSON), javnih iCal kalendara (vlastiti parser ICS
      formata prema RFC 5545) i web scrapingom lokalnih stranica. Sve se sprema u
      MySQL bazu bez duplikata, a aplikacija zatim i sama nudi vlastiti API koji
      podatke vraća u JSON ili XML formatu, opisan XSD shemom - tako i druge
      aplikacije mogu graditi na našim podacima.
    </p>
    <p>
      Iza scene radi CMS sustav: administratori uređuju događanja, pokreću uvoz iz
      izvora i upravljaju korisnicima i njihovim rolama, dok registrirani korisnici
      imaju vlastiti profil. Projekt je izrađen u sklopu kolegija o web servisima
      i u potpunosti je otvorenog koda - slobodno ga proučite, pokrenite lokalno
      ili nadogradite.
    </p>
  </div>

  <h2 class="detail-subtitle">Kako rade web servisi?</h2>
  <div class="video-wrap">
    <iframe
      src="https://www.youtube.com/embed/lsMQRaeKNDk"
      title="What Is REST API? (video)"
      loading="lazy" allowfullscreen
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      referrerpolicy="strict-origin-when-cross-origin"></iframe>
  </div>
  <p class="form-hint">
    Video: uvod u REST API koncepte koji pokreću EventHub (YouTube, engleski).
  </p>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
