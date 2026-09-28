<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();
$_SESSION = [];
session_regenerate_id(true);
header('Location: login.php');
exit;