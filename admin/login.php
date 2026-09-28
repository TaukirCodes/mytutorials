<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

if (is_admin()) {
    header('Location: index.php');
    exit;
}

$error = '';
$submittedEmail = '';
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
    $submittedEmail = trim((string) ($_POST['email'] ?? ''));
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
  <meta name="color-scheme" content="light dark">
  <title>Admin sign in | SkillNovi Studio</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/style.css" rel="stylesheet">
  <link href="assets1/login.css" rel="stylesheet">
  <link href="../assets/theme.css" rel="stylesheet">
  <script src="../assets/theme.js"></script>
</head>
<body class="auth-page admin-auth-page">
  <main class="admin-auth-layout">
    <aside class="admin-auth-story" aria-label="SkillNovi Studio">
      <a class="admin-auth-brand" href="../index.php" aria-label="SkillNovi home">
        <span class="admin-auth-brand-mark"><i class="bi bi-braces-asterisk" aria-hidden="true"></i></span>
        <span>SkillNovi <small>STUDIO</small></span>
      </a>

      <div class="admin-auth-story-copy">
        <p class="admin-auth-kicker"><span></span> YOUR CONTENT WORKSPACE</p>
        <h1>Make every lesson<br><em>worth learning.</em></h1>
        <p class="admin-auth-story-description">A calm space to shape great tutorials, review drafts, and keep your learning library growing.</p>
        <div class="admin-auth-benefits" aria-label="Workspace features">
          <div><span><i class="bi bi-collection" aria-hidden="true"></i></span><p><strong>Course library</strong><small>Keep every learning path in order.</small></p></div>
          <div><span><i class="bi bi-journal-check" aria-hidden="true"></i></span><p><strong>Thoughtful publishing</strong><small>Review lessons before they go live.</small></p></div>
        </div>
      </div>

      <p class="admin-auth-story-footer"><i class="bi bi-shield-check me-2" aria-hidden="true"></i>Private workspace for SkillNovi administrators</p>
      <span class="admin-auth-decoration admin-auth-decoration-one" aria-hidden="true"></span>
      <span class="admin-auth-decoration admin-auth-decoration-two" aria-hidden="true"></span>
    </aside>

    <section class="admin-auth-form-panel" aria-labelledby="sign-in-title">
      <div class="admin-auth-form-topline">
        <span class="admin-auth-mobile-brand"><i class="bi bi-braces-asterisk me-2" aria-hidden="true"></i>SkillNovi <small>STUDIO</small></span>
        <div class="admin-auth-top-actions">
          <a href="../index.php" class="admin-auth-back"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Learner site</a>
          <button class="theme-mode-toggle" type="button" data-theme-toggle aria-label="Switch to dark theme" aria-pressed="false"><i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i><span class="visually-hidden" data-theme-label>Switch to dark theme</span></button>
        </div>
      </div>

      <div class="admin-auth-form-content">
        <div class="admin-auth-heading-icon" aria-hidden="true"><i class="bi bi-person-lock"></i></div>
        <p class="admin-auth-form-eyebrow">ADMINISTRATOR ACCESS</p>
        <h2 id="sign-in-title">Welcome back</h2>
        <p class="admin-auth-form-description">Sign in to continue to your publishing desk.</p>

        <?php if ($error !== '' && $databaseReady): ?>
          <div class="admin-auth-alert" role="alert" aria-live="assertive">
            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
            <span><?= e($error) ?></span>
          </div>
        <?php endif; ?>

        <?php if ($databaseReady && $setupRequired): ?>
          <div class="admin-auth-setup-note" role="status">
            <span><i class="bi bi-stars" aria-hidden="true"></i></span>
            <div><strong>One last setup step</strong><p>No administrator account exists yet. Create the first account to unlock the workspace.</p>
              <a href="setup.php" class="admin-auth-setup-link">Create your admin account <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i></a>
            </div>
          </div>
        <?php elseif ($databaseReady): ?>
          <form method="post" class="admin-auth-form" id="adminLoginForm">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <label class="admin-auth-label" for="adminEmail">Email address</label>
            <div class="admin-auth-input-wrap">
              <i class="bi bi-envelope" aria-hidden="true"></i>
              <input class="form-control" id="adminEmail" type="email" name="email" value="<?= e($submittedEmail) ?>" placeholder="admin@example.com" required autocomplete="username" autocapitalize="none" spellcheck="false" inputmode="email">
            </div>

            <div class="admin-auth-password-heading">
              <label class="admin-auth-label mb-0" for="adminPassword">Password</label>
            </div>
            <div class="admin-auth-input-wrap admin-auth-password-wrap">
              <i class="bi bi-lock" aria-hidden="true"></i>
              <input class="form-control" id="adminPassword" type="password" name="password" placeholder="Enter your admin password" required autocomplete="current-password">
              <button class="admin-auth-password-toggle" type="button" data-password-toggle aria-label="Show password" aria-pressed="false">
                <i class="bi bi-eye" aria-hidden="true"></i><span>Show</span>
              </button>
            </div>

            <button class="admin-auth-submit" type="submit" id="adminLoginSubmit">
              <span class="admin-auth-submit-label">Sign in to workspace</span>
              <i class="bi bi-arrow-right" aria-hidden="true"></i>
              <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
            </button>
            <p class="admin-auth-secure-note"><i class="bi bi-shield-lock me-1" aria-hidden="true"></i>Secure sign-in <span aria-hidden="true">&middot;</span> Your session stays private</p>
          </form>
        <?php else: ?>
          <div class="admin-auth-setup-note admin-auth-setup-note-error" role="status">
            <span><i class="bi bi-database-exclamation" aria-hidden="true"></i></span>
            <div><strong>Workspace unavailable</strong><p><?= $error !== '' ? e($error) : 'Check that MySQL is running and your local database settings are correct, then refresh this page.' ?></p></div>
          </div>
        <?php endif; ?>
      </div>

      <footer class="admin-auth-form-footer"><span>&copy; <?= date('Y') ?> SkillNovi Studio</span><span>Learn &middot; Create &middot; Share</span></footer>
    </section>
  </main>
  <script src="assets1/login.js" defer></script>
</body>
</html>
