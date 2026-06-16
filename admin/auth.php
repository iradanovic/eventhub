<?php
/**
 * EventHub - zaštita admin stranica (samo rola 'admin')
 * Uključiti na vrhu svake admin stranice.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

ensure_session();

if (!is_admin()) {
    header('Location: ../prijava.php');
    exit;
}
