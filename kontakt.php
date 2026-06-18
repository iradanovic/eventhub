<?php
/**
 * EventHub - kontakt forma (firstname, lastname, email, country,
 * newsletter, subject) + Google Maps karta
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$errors  = [];
$success = false;
$old = ['first_name' => '', 'last_name' => '', 'email' => '',
        'country' => 'Hrvatska', 'newsletter' => 0,
        'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['first_name', 'last_name', 'email', 'country', 'subject', 'message'] as $k) {
        $old[$k] = trim($_POST[$k] ?? '');
    }
    $old['newsletter'] = isset($_POST['newsletter']) ? 1 : 0;

    if ($old['first_name'] === '') {
        $errors[] = 'Upišite ime.';
    }
    if ($old['last_name'] === '') {
        $errors[] = 'Upišite prezime.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Upišite ispravnu e-mail adresu.';
    }
    if (!in_array($old['country'], country_options(), true)) {
        $errors[] = 'Odaberite državu s popisa.';
    }
    if ($old['subject'] === '') {
        $errors[] = 'Upišite naslov poruke.';
    }
    if (mb_strlen($old['message']) < 10) {
        $errors[] = 'Poruka mora imati barem 10 znakova.';
    }

    if (!$errors) {
        db()->prepare(
            'INSERT INTO messages
               (first_name, last_name, email, country, newsletter, subject, message)
             VALUES (:fn, :ln, :em, :co, :nl, :su, :me)'
        )->execute([
            ':fn' => $old['first_name'],
            ':ln' => $old['last_name'],
            ':em' => $old['email'],
            ':co' => $old['country'],
            ':nl' => $old['newsletter'],
            ':su' => $old['subject'],
            ':me' => $old['message'],
        ]);
        $success = true;
        $old = ['first_name' => '', 'last_name' => '', 'email' => '',
                'country' => 'Hrvatska', 'newsletter' => 0,
                'subject' => '', 'message' => ''];
    }
}

$pageTitle  = 'Kontakt';
$activePage = 'kontakt';
require __DIR__ . '/includes/header.php';
?>

<section class="container page-narrow">
  <h1 class="page-title">Kontakt</h1>
  <p class="page-intro">
    Nedostaje neko događanje? Pronašli ste grešku u podacima? Pošaljite poruku -
    sprema se u bazu i vidljiva je administratoru.
  </p>

  <div class="map-wrap">
    <iframe
      title="Karta - Veleučilište Velika Gorica, Zagrebačka 5, Velika Gorica"
      src="https://www.google.com/maps?q=Veleu%C4%8Dili%C5%A1te+Velika+Gorica,+Zagreba%C4%8Dka+5,+Velika+Gorica&output=embed"
      width="100%" height="320" style="border:0" loading="lazy"
      referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success" role="status">
      Poruka je spremljena. Hvala na javljanju!
    </div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="alert alert-error" role="alert">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" action="kontakt.php" class="form-card" novalidate>
    <div class="form-row">
      <div class="form-field">
        <label for="first_name">Ime *</label>
        <input type="text" id="first_name" name="first_name" required maxlength="80"
               value="<?= e($old['first_name']) ?>">
      </div>
      <div class="form-field">
        <label for="last_name">Prezime *</label>
        <input type="text" id="last_name" name="last_name" required maxlength="80"
               value="<?= e($old['last_name']) ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-field">
        <label for="email">E-mail adresa *</label>
        <input type="email" id="email" name="email" required maxlength="190"
               value="<?= e($old['email']) ?>">
      </div>
      <div class="form-field">
        <label for="country">Država *</label>
        <select id="country" name="country" required>
          <?php foreach (country_options() as $c): ?>
            <option value="<?= e($c) ?>" <?= $old['country'] === $c ? 'selected' : '' ?>>
              <?= e($c) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-field">
      <label for="subject">Naslov poruke *</label>
      <input type="text" id="subject" name="subject" required maxlength="150"
             value="<?= e($old['subject']) ?>" placeholder="npr. Prijedlog novog događanja">
    </div>

    <div class="form-field">
      <label for="message">Poruka *</label>
      <textarea id="message" name="message" rows="6" required
                minlength="10"><?= e($old['message']) ?></textarea>
    </div>

    <div class="form-field form-checkbox">
      <input type="checkbox" id="newsletter" name="newsletter" value="1"
             <?= $old['newsletter'] ? 'checked' : '' ?>>
      <label for="newsletter">Želim primati newsletter s najavama događanja</label>
    </div>

    <button type="submit" class="btn btn-primary">Pošalji poruku</button>
  </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
