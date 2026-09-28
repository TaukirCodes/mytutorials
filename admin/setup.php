<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

$error = '';
$databaseReady = false;
$adminExists = false;
$setupKey = env_value('ADMIN_SETUP_KEY');
$setupKeyConfigured = $setupKey !== '' && $setupKey !== 'replace-with-a-long-random-value-before-first-use';
$localSetupAllowed = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
try {
    $database = db();
    $databaseReady = true;
    $adminExists = (int) $database->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
} catch (Throwable $exception) {
    $error = 'Database is not ready. Create the devdocs database and import database/schema.sql first.';
}

if ($adminExists) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $databaseReady) {
    verify_csrf();
    $providedKey = (string) ($_POST['setup_key'] ?? '');
    $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

    if ($setupKeyConfigured && !hash_equals($setupKey, $providedKey)) {
        $error = 'The setup key is incorrect.';
    } elseif (!$setupKeyConfigured && !$localSetupAllowed) {
      $error = 'Remote first-admin setup is disabled until ADMIN_SETUP_KEY is set in .env.';
    } elseif ($email === false) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 12) {
        $error = 'Use a password with at least 12 characters.';
    } elseif ($password !== $passwordConfirmation) {
        $error = 'The passwords do not match.';
    } else {
        $statement = $database->prepare('INSERT INTO admins (email, password_hash) VALUES (:email, :password_hash)');
        $statement->execute([
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $database->lastInsertId();
        $_SESSION['admin_email'] = $email;
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Set up administrator | DevDocs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/style.css" rel="stylesheet">
</head>
<body class="auth-page">
  <main class="auth-shell">
    <section class="auth-panel">
      <a class="brand-mark" href="../index.php">DevDocs <span>ADMIN</span></a>
      <h1>Create the first admin</h1>
      <p class="text-muted">One-time setup. This page locks after the first administrator is created.</p>
      <?php if (!$setupKeyConfigured && $localSetupAllowed): ?><div class="alert alert-info">Local first-run setup is allowed from this computer only.</div><?php endif; ?>
      <?php if ($error !== ''): ?><div class="alert alert-warning" role="alert"><?= e($error) ?></div><?php endif; ?>
      <?php if ($databaseReady && !$adminExists): ?>
      <form method="post" class="vstack gap-3">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <?php if ($setupKeyConfigured): ?>
        <label class="form-label mb-0">Setup key
          <input class="form-control mt-1" type="password" name="setup_key" required autocomplete="off">
        </label>
        <?php endif; ?>
        <label class="form-label mb-0">Admin email
          <input class="form-control mt-1" type="email" name="email" required autocomplete="email">
        </label>
        <label class="form-label mb-0">Password
          <input class="form-control mt-1" type="password" name="password" minlength="12" required autocomplete="new-password">
        </label>
        <label class="form-label mb-0">Confirm password
          <input class="form-control mt-1" type="password" name="password_confirmation" minlength="12" required autocomplete="new-password">
        </label>
        <button class="btn btn-blue" type="submit">Create admin account</button>
      </form>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>