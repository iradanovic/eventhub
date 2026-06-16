<?php
/**
 * EventHub - dodavanje / uređivanje događanja
 */

declare(strict_types=1);

require __DIR__ . '/auth.php';

$pdo    = db();
$id     = (int)($_GET['id'] ?? 0);
$errors = [];

/* Zadane vrijednosti forme */
$ev = [
    'title'          => '',
    'description'    => '',
    'category'       => 'Ostalo',
    'venue_name'     => '',
    'venue_address'  => '',
    'city'           => 'Zagreb',
    'start_datetime' => date('Y-m-d\TH:i'),
    'end_datetime'   => '',
    'price_info'     => '',
    'event_url'      => '',
    'image_url'      => '',
];

/* Učitaj postojeće događanje kod uređivanja */
if ($id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        $_SESSION['flash'] = 'Događanje nije pronađeno.';
        header('Location: dashboard.php');
        exit;
    }
    $ev = array_merge($ev, $row);
    $ev['start_datetime'] = str_replace(' ', 'T', substr($ev['start_datetime'], 0, 16));
    $ev['end_datetime']   = $ev['end_datetime']
        ? str_replace(' ', 'T', substr($ev['end_datetime'], 0, 16)) : '';
}

/* Spremanje */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* image_url se ne šalje kao tekst nego kao datoteka - zadrži postojeću */
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT image_url FROM events WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $ev['image_url'] = (string)($stmt->fetchColumn() ?: '');
    }
    foreach (array_keys($ev) as $key) {
        if ($key === 'image_url') {
            continue;
        }
        $ev[$key] = trim($_POST[$key] ?? '');
    }

    /* Upload slike (opcionalno) */
    if (!empty($_FILES['image']['name'])) {
        $up = $_FILES['image'];
        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png', 'webp' => 'image/webp',
                    'svg' => 'image/svg+xml'];
        $ext = strtolower(pathinfo($up['name'], PATHINFO_EXTENSION));

        if ($up['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Greška pri prijenosu slike (kod ' . (int)$up['error'] . ').';
        } elseif (!isset($allowed[$ext])) {
            $errors[] = 'Dozvoljeni formati slike: JPG, PNG, WebP, SVG.';
        } elseif ($up['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Slika smije imati najviše 2 MB.';
        } else {
            $dir = __DIR__ . '/../assets/uploads';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $fname = 'event-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($up['tmp_name'], $dir . '/' . $fname)) {
                $ev['image_url'] = 'assets/uploads/' . $fname;
            } else {
                $errors[] = 'Sliku nije moguće spremiti na poslužitelj.';
            }
        }
    }

    if ($ev['title'] === '') {
        $errors[] = 'Naziv događanja je obavezan.';
    }
    if ($ev['start_datetime'] === '' || strtotime($ev['start_datetime']) === false) {
        $errors[] = 'Neispravan datum početka.';
    }
    if ($ev['event_url'] !== '' && !filter_var($ev['event_url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Neispravan URL događanja.';
    }

    if (!$errors) {
        $params = [
            ':title'          => $ev['title'],
            ':description'    => $ev['description'] ?: null,
            ':category'       => $ev['category'] ?: 'Ostalo',
            ':venue_name'     => $ev['venue_name'] ?: null,
            ':venue_address'  => $ev['venue_address'] ?: null,
            ':city'           => $ev['city'] ?: 'Zagreb',
            ':start_datetime' => date('Y-m-d H:i:s', strtotime($ev['start_datetime'])),
            ':end_datetime'   => $ev['end_datetime']
                                 ? date('Y-m-d H:i:s', strtotime($ev['end_datetime'])) : null,
            ':price_info'     => $ev['price_info'] ?: null,
            ':image_url'      => $ev['image_url'] ?: null,
            ':event_url'      => $ev['event_url'] ?: null,
        ];

        if ($id > 0) {
            $params[':id'] = $id;
            $pdo->prepare(
                'UPDATE events SET
                   title = :title, description = :description, category = :category,
                   venue_name = :venue_name, venue_address = :venue_address, city = :city,
                   start_datetime = :start_datetime, end_datetime = :end_datetime,
                   price_info = :price_info, image_url = :image_url, event_url = :event_url
                 WHERE id = :id'
            )->execute($params);
            $_SESSION['flash'] = 'Događanje je ažurirano.';
        } else {
            $params[':external_id'] = 'manual-' . uniqid();
            $pdo->prepare(
                'INSERT INTO events
                   (title, description, category, venue_name, venue_address, city,
                    start_datetime, end_datetime, price_info, image_url, event_url, source, external_id)
                 VALUES
                   (:title, :description, :category, :venue_name, :venue_address, :city,
                    :start_datetime, :end_datetime, :price_info, :image_url, :event_url, \'manual\', :external_id)'
            )->execute($params);
            $_SESSION['flash'] = 'Novo događanje je spremljeno.';
        }

        header('Location: dashboard.php');
        exit;
    }
}

$pageTitle = $id > 0 ? 'Uredi događanje' : 'Novo događanje';
require __DIR__ . '/../includes/header.php';
?>

<section class="container page-narrow">
  <p class="breadcrumb"><a href="dashboard.php">&larr; Nadzorna ploča</a></p>
  <h1 class="page-title"><?= $id > 0 ? 'Uredi događanje' : 'Novo događanje' ?></h1>

  <?php if ($errors): ?>
    <div class="alert alert-error" role="alert">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post" class="form-card" enctype="multipart/form-data" novalidate>
    <div class="form-field">
      <label for="title">Naziv događanja *</label>
      <input type="text" id="title" name="title" required maxlength="255"
             value="<?= e($ev['title']) ?>">
    </div>

    <div class="form-field">
      <label for="description">Opis</label>
      <textarea id="description" name="description" rows="5"><?= e($ev['description']) ?></textarea>
    </div>

    <div class="form-row">
      <div class="form-field">
        <label for="category">Kategorija</label>
        <input type="text" id="category" name="category" maxlength="100"
               value="<?= e($ev['category']) ?>">
      </div>
      <div class="form-field">
        <label for="price_info">Cijena</label>
        <input type="text" id="price_info" name="price_info" maxlength="150"
               value="<?= e($ev['price_info']) ?>" placeholder="npr. 25 EUR / ulaz slobodan">
      </div>
    </div>

    <div class="form-row">
      <div class="form-field">
        <label for="start_datetime">Početak *</label>
        <input type="datetime-local" id="start_datetime" name="start_datetime" required
               value="<?= e($ev['start_datetime']) ?>">
      </div>
      <div class="form-field">
        <label for="end_datetime">Završetak</label>
        <input type="datetime-local" id="end_datetime" name="end_datetime"
               value="<?= e($ev['end_datetime']) ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-field">
        <label for="venue_name">Lokacija / dvorana</label>
        <input type="text" id="venue_name" name="venue_name" maxlength="255"
               value="<?= e($ev['venue_name']) ?>">
      </div>
      <div class="form-field">
        <label for="city">Grad</label>
        <input type="text" id="city" name="city" maxlength="100"
               value="<?= e($ev['city']) ?>">
      </div>
    </div>

    <div class="form-field">
      <label for="venue_address">Adresa</label>
      <input type="text" id="venue_address" name="venue_address" maxlength="255"
             value="<?= e($ev['venue_address']) ?>">
    </div>

    <div class="form-field">
      <label for="event_url">Službeni URL</label>
      <input type="url" id="event_url" name="event_url" maxlength="500"
             value="<?= e($ev['event_url']) ?>" placeholder="https://...">
    </div>

    <div class="form-field">
      <label for="image">Slika događanja <small>(JPG/PNG/WebP/SVG, max. 2 MB)</small></label>
      <?php if (!empty($ev['image_url'])): ?>
        <p class="form-current-image">
          <img src="../<?= e($ev['image_url']) ?>" alt="Trenutna slika događanja">
          <small>Trenutna slika - odabirom nove datoteke bit će zamijenjena.</small>
        </p>
      <?php endif; ?>
      <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,.svg">
    </div>

    <button type="submit" class="btn btn-primary">
      <?= $id > 0 ? 'Spremi promjene' : 'Spremi događanje' ?>
    </button>
  </form>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
