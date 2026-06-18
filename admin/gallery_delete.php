<?php
/**
 * EventHub - brisanje slike iz galerije
 */

declare(strict_types=1);

require __DIR__ . '/auth.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM gallery WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $_SESSION['flash'] = $stmt->rowCount() > 0
        ? 'Slika je obrisana.'
        : 'Slika nije pronađena.';
}

header('Location: gallery.php');
exit;
