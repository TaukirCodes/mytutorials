  <nav class="sb-topnav navbar navbar-expand fixed-top px-3">
    <a class="navbar-brand fw-bold me-3" href="index.php"><i class="bi bi-braces-asterisk me-2"></i>SkillNovi <span>STUDIO</span></a>
    <button class="btn admin-menu-toggle" id="adminSidebarToggle" type="button" aria-label="Open admin navigation" aria-controls="sbSidenav" aria-expanded="false"><i class="bi bi-list fs-5"></i></button>
    <span class="admin-topnav-context">Content workspace</span>
    <div class="admin-topnav-actions ms-auto">
      <a class="admin-preview-link" href="../index.php"><i class="bi bi-box-arrow-up-right me-1"></i><span>View learner site</span></a>
      <div class="dropdown">
        <button class="admin-account-button" id="navbarDropdownUser" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="admin-avatar"><i class="bi bi-person-fill" aria-hidden="true"></i></span>
          <span class="admin-account-email"><?= e($_SESSION['admin_email'] ?? 'Administrator') ?></span>
          <i class="bi bi-chevron-down small" aria-hidden="true"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="navbarDropdownUser">
          <li><span class="dropdown-item-text small text-muted"><?= e($_SESSION['admin_email'] ?? 'Administrator') ?></span></li>
          <li><hr class="dropdown-divider"></li>
          <li><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button></form></li>
        </ul>
      </div>
    </div>
  </nav>