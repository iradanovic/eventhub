<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
ensure_session();
$_SESSION = [];
session_destroy();
header('Location: ../index.php');
exit;
