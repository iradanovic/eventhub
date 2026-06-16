<?php
/**
 * EventHub - ulaz u administraciju
 * Prijava je zajednička (../prijava.php); ovdje samo preusmjeravamo.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
ensure_session();

header('Location: ' . (is_admin() ? 'dashboard.php' : '../prijava.php'));
exit;
