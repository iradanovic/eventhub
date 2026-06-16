<?php
/**
 * EventHub - brisanje događanja
 */

declare(strict_types=1);

require __DIR__ . '/auth.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = db()->prepare('DELETE FROM events WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $_SESSION['flash'] = $stmt->rowCount() > 0
        ? 'Događanje je obrisano.'
        : 'Događanje nije pronađeno.';
}

header('Location: dashboard.php');
exit;
