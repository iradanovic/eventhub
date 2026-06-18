<?php
/**
 * EventHub - dodavanje / uređivanje slike u galeriji
 */

declare(strict_types=1);

require __DIR__ . '/auth.php';

$pdo    = db();
$id     = (int)($_GET['id'] ?? 0);
$errors = [];

$img = ['image_url' => '', 'alt_text' => '', 'caption' => ''];

/* Učitaj postojeću sliku kod uređivanja */
if ($id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM gallery WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        $_SESSION['flash'] = 'Slika nije pronađena.';
        header('Location: gallery.php');
        exit;
    }
    $img = array_merge($img, $row);
}

/* Spremanje */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* image_url se ne šalje kao tekst nego kao datoteka - zadrži postojeću */
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT image_url FROM gallery WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $img['image_url'] = (string)($stmt->fetchColumn() ?: '');
    }
    $img['alt_text'] = trim($_POST['alt_text'] ?? '');
    $img['caption']  = trim($_POST['caption'] ?? '');

    /* Upload slike (obavezan kod dodavanja, opcionalan kod uređivanja) */
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
            $fname = 'gallery-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($up['tmp_name'], $dir . '/' . $fname)) {
                $img['image_url'] = 'assets/uploads/' . $fname;
            } else {
                $errors[] = 'Sliku nije moguće spremiti na poslužitelj.';
            }
        }
    } elseif ($img['image_url'] === '') {
        $errors[] = 'Odaberite sliku.';
    }

    if ($img['alt_text'] === '') {
        $errors[] = 'Naslov slike je obavezan.';
    }
    if ($img['caption'] === '') {
        $errors[] = 'Opis slike je obavezan.';
    }

    if (!$errors) {
        $params = [
            ':image_url' => $img['image_url'],
            ':alt_text'  => $img['alt_text'],
            ':caption'   => $img['caption'],
        ];

        if ($id > 0) {
            $params[':id'] = $id;
            $pdo->prepare(
                'UPDATE gallery SET image_url = :image_url, alt_text = :alt_text, caption = :caption
                 WHERE id = :id'
            )->execute($params);
            $_SESSION['flash'] = 'Slika je ažurirana.';
        } else {
            $pdo->prepare(
                'INSERT INTO gallery (image_url, alt_text, caption)
                 VALUES (:image_url, :alt_text, :caption)'
            )->execute($params);
            $_SESSION['flash'] = 'Nova slika je spremljena.';
        }

        header('Location: gallery.php');
        exit;
    }
}

$pageTitle = $id > 0 ? 'Uredi sliku' : 'Nova slika';
require __DIR__ . '/../includes/header.php';
?>

<section class="container page-narrow">
  <p class="breadcrumb"><a href="gallery.php">&larr; Galerija</a></p>
  <h1 class="page-title"><?= $id > 0 ? 'Uredi sliku' : 'Nova slika' ?></h1>

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
      <label for="image">Slika <small>(JPG/PNG/WebP/SVG, max. 2 MB)</small></label>
      <?php if (!empty($img['image_url'])): ?>
        <p class="form-current-image">
          <img src="../<?= e($img['image_url']) ?>" alt="Trenutna slika">
          <small>Trenutna slika - odabirom nove datoteke bit će zamijenjena.</small>
        </p>
      <?php endif; ?>
      <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,.svg"
             <?= $id > 0 ? '' : 'required' ?>>
    </div>

    <div class="form-field">
      <label for="alt_text">Naslov slike *</label>
      <input type="text" id="alt_text" name="alt_text" required maxlength="150"
             value="<?= e($img['alt_text']) ?>" placeholder="npr. Publika na koncertu">
    </div>

    <div class="form-field">
      <label for="caption">Opis *</label>
      <textarea id="caption" name="caption" rows="3" required
                maxlength="255"><?= e($img['caption']) ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">
      <?= $id > 0 ? 'Spremi promjene' : 'Spremi sliku' ?>
    </button>
  </form>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
