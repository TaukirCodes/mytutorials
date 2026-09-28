<?php
$activeAdminPage = $activeAdminPage ?? 'dashboard';
?>
<aside class="admin-sidebar" id="sbSidenav" aria-label="Admin workspace navigation">
  <div class="admin-sidebar-label">WORKSPACE</div>
  <nav class="admin-sidebar-nav">
    <a class="admin-side-link <?= $activeAdminPage === 'dashboard' ? 'active' : '' ?>" href="index.php" <?= $activeAdminPage === 'dashboard' ? 'aria-current="page"' : '' ?>>
      <i class="bi bi-grid-1x2" aria-hidden="true"></i><span>Publishing desk</span>
    </a>
    <div class="admin-sidebar-label admin-sidebar-section-label">MANAGE</div>
    <a class="admin-side-link <?= $activeAdminPage === 'courses' ? 'active' : '' ?>" href="courses.php" <?= $activeAdminPage === 'courses' ? 'aria-current="page"' : '' ?>>
      <i class="bi bi-collection" aria-hidden="true"></i><span>Courses</span>
    </a>
    <a class="admin-side-link <?= $activeAdminPage === 'lessons' ? 'active' : '' ?>" href="lessons.php" <?= $activeAdminPage === 'lessons' ? 'aria-current="page"' : '' ?>>
      <i class="bi bi-journal-text" aria-hidden="true"></i><span>Lessons</span>
    </a>
  </nav>
  <div class="admin-sidebar-bottom">
    <a class="admin-side-link" href="../index.php"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i><span>Preview learner site</span></a>
  </div>
</aside>
<button class="admin-sidebar-backdrop" type="button" data-admin-sidebar-close aria-label="Close admin navigation"></button>
