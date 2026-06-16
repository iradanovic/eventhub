<?php
/**
 * EventHub - brisanje korisnika (ne može se obrisati vlastiti račun)
 */

declare(strict_types=1);

require __DIR__ . '/auth.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0 && $id !== (int)$_SESSION['user_id']) {
    $stmt = db()->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $_SESSION['flash'] = $stmt->rowCount()
        ? 'Korisnik je obrisan.'
        : 'Korisnik nije pronađen.';
} else {
    $_SESSION['flash'] = 'Vlastiti račun nije moguće obrisati.';
}

header('Location: users.php');
exit;
