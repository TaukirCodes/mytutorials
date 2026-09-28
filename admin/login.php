<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

if (is_admin()) {
    header('Location: index.php');
    exit;
}

$error = '';
$databaseReady = false;
$setupRequired = false;
try {
    $database = db();
    $databaseReady = true;
    $setupRequired = (int) $database->query('SELECT COUNT(*) FROM admins')->fetchColumn() === 0;
} catch (Throwable $exception) {
    $error = 'Database is not ready. Import database/schema.sql and check your local .env settings.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $databaseReady) {
    verify_csrf();
    $lockUntil = (int) ($_SESSION['admin_login_lock_until'] ?? 0);
    if ($lockUntil > time()) {
        $error = 'Too many attempts. Try again in ' . (int) ceil(($lockUntil - time()) / 60) . ' minute(s).';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $statement = $database->prepare('SELECT id, email, password_hash FROM admins WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $admin = $statement->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            unset($_SESSION['admin_login_attempts'], $_SESSION['admin_login_lock_until']);
            header('Location: index.php');
            exit;
        }

        $attempts = (int) ($_SESSION['admin_login_attempts'] ?? 0) + 1;
        $_SESSION['admin_login_attempts'] = $attempts;
        if ($attempts >= 5) {
            $_SESSION['admin_login_lock_until'] = time() + 900;
            $_SESSION['admin_login_attempts'] = 0;
            $error = 'Too many attempts. Try again in 15 minutes.';
        } else {
            $error = 'Email or password is incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin sign in | LearnLooma</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/style.css" rel="stylesheet">
</head>
<body class="auth-page">
  <main class="auth-shell">
    <section class="auth-panel">
      <a class="brand-mark" href="../index.php">LearnLooma <span>ADMIN</span></a>
      <h1>Sign in</h1>
      <p class="text-muted">Manage courses, lessons, and publishing.</p>
      <?php if ($error !== ''): ?><div class="alert alert-warning" role="alert"><?= e($error) ?></div><?php endif; ?>
      <?php if ($databaseReady && $setupRequired): ?>
        <div class="alert alert-info">No admin account exists yet. <a href="setup.php">Complete one-time setup</a>.</div>
      <?php elseif ($databaseReady): ?>
      <form method="post" class="vstack gap-3">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label class="form-label mb-0">Email
          <input class="form-control mt-1" type="email" name="email" required autocomplete="username">
        </label>
        <label class="form-label mb-0">Password
          <input class="form-control mt-1" type="password" name="password" required autocomplete="current-password">
        </label>
        <button class="btn btn-blue" type="submit">Sign in to admin</button>
      </form>
      <?php endif; ?>
      <a class="small d-inline-block mt-4" href="../index.php">Back to tutorials</a>
    </section>
  </main>
</body>
</html>