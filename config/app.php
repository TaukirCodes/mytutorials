<?php
declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$environmentFile = $projectRoot . DIRECTORY_SEPARATOR . '.env';

if (is_file($environmentFile)) {
    $environment = parse_ini_file($environmentFile, false, INI_SCANNER_RAW);
    if ($environment === false) {
        throw new RuntimeException('The .env file could not be parsed.');
    }

    foreach ($environment as $name => $value) {
        if (getenv($name) === false) {
            putenv($name . '=' . $value);
        }
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('devdocs_session');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function env_value(string $name, string $default = ''): string
{
    $value = getenv($name);
    return $value === false ? $default : $value;
}

function db(): PDO
{
    static $connection;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = env_value('DB_HOST', '127.0.0.1');
    $port = env_value('DB_PORT', '3306');
    $name = env_value('DB_NAME', 'devdocs');
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);

    $connection = new PDO($dsn, env_value('DB_USER', 'root'), env_value('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}

function e(string|int|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function slugify(string $value): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $normalized = strtolower($ascii === false ? $value : $ascii);
    $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', $normalized), '-');
    return $slug !== '' ? $slug : 'lesson';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';

    if (!is_string($provided) || $expected === '' || !hash_equals($expected, $provided)) {
        http_response_code(403);
        exit('Invalid or expired request token. Refresh the page and try again.');
    }
}

function is_admin(): bool
{
    return isset($_SESSION['admin_id']) && is_int($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}