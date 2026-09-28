<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';
require_admin();

$metrics = [
  'courses' => (int) db()->query('SELECT COUNT(*) FROM courses')->fetchColumn(),
  'lessons' => (int) db()->query('SELECT COUNT(*) FROM lessons')->fetchColumn(),
  'published' => (int) db()->query('SELECT COUNT(*) FROM lessons WHERE is_published = 1')->fetchColumn(),
  'drafts' => (int) db()->query('SELECT COUNT(*) FROM lessons WHERE is_published = 0')->fetchColumn(),
];
$recentLessons = db()->query('SELECT lessons.id, lessons.topic, lessons.title, lessons.is_published, courses.title AS course_title FROM lessons JOIN courses ON courses.id = lessons.course_id ORDER BY lessons.updated_at DESC LIMIT 8')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DevAdmin - Dashboard</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <link href="assets1/style.css" rel="stylesheet">
</head>
<body>

  <!-- Top Navbar (Fixed Top) -->
  <?php include('topnav.php')?>

  <!-- SB-Admin Layout -->
  <div class="sb-layout">
    
    <!-- Fixed Side Navigation -->
    <div id="sbSidenav">
      <div class="d-flex flex-column h-100 justify-content-between">
        <div class="sb-sidenav-menu py-3">
          <div class="text-uppercase text-secondary px-3 pb-2 small fw-bold" style="font-size: 11px;">Overview</div>
          <a class="nav-link active" href="index.php">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
          </a>

          <div class="text-uppercase text-secondary px-3 pt-4 pb-2 small fw-bold" style="font-size: 11px;">Management</div>
          <a class="nav-link" href="courses.php">
            <i class="bi bi-folder-plus me-2"></i> Courses
          </a>
          <a class="nav-link" href="lessons.php">
            <i class="bi bi-file-earmark-plus me-2"></i> Lessons
          </a>
          
          <div class="text-uppercase text-secondary px-3 pt-4 pb-2 small fw-bold" style="font-size: 11px;">Administration</div>
          <a class="nav-link" href="../index.php">
            <i class="bi bi-box-arrow-up-right me-2"></i> View learner site
          </a>
        </div>

        <!-- Sidenav Footer -->
        <div class="sb-sidenav-footer small">
          <div class="text-muted">Authenticated User:</div>
          <div class="fw-bold text-white"><?= e($_SESSION['admin_email'] ?? 'Administrator') ?></div>
        </div>
      </div>
    </div>

    <!-- Dashboard Content Area -->
    <div class="sb-content">
      <div class="container-fluid">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h3 class="fw-bold text-dark mb-0">System Control Panel</h3>
            <p class="text-muted small mb-0">Overview of active documentation and publishing metrics.</p>
          </div>
            <a class="btn btn-primary btn-sm" href="lessons.php"><i class="bi bi-plus-lg me-1"></i> Add Lesson</a>
        </div>

        <!-- Quick Analysis Metrics Cards -->
        <div class="row g-3 mb-4">
          <div class="col-xl-3 col-md-6">
            <div class="card card-metric bg-white shadow-sm">
              <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase">Total Courses</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= $metrics['courses'] ?></div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="card card-metric bg-white shadow-sm" style="border-left-color: #10b981;">
              <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase">Active Topics</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= $metrics['lessons'] ?></div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="card card-metric bg-white shadow-sm" style="border-left-color: #f59e0b;">
              <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase">Code Snippets</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= $metrics['published'] ?></div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6">
            <div class="card card-metric bg-white shadow-sm" style="border-left-color: #ef4444;">
              <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase">Pending Reviews</div>
                <div class="fs-3 fw-bold text-dark mt-1"><?= $metrics['drafts'] ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content Data Quick Access Table -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3 border-bottom">
            <span class="text-dark"><i class="bi bi-journal-text me-2 text-primary"></i> Documentation Content Status</span>
            <button class="btn btn-sm btn-outline-secondary">Export Log</button>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase text-muted">
                  <tr>
                    <th>Course Track</th>
                    <th>Topic Module</th>
                    <th>Chapter</th>
                    <th>Snippet Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody class="small">
                  <?php foreach ($recentLessons as $lesson): ?>
                  <tr>
                    <td><?= e($lesson['course_title']) ?></td>
                    <td><?= e($lesson['topic']) ?></td>
                    <td><?= e($lesson['title']) ?></td>
                    <td><span class="badge <?= $lesson['is_published'] ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>"><?= $lesson['is_published'] ? 'Published' : 'Draft' ?></span></td>
                    <td><a class="btn btn-sm btn-light border" href="lessons.php?edit=<?= (int) $lesson['id'] ?>" aria-label="Edit <?= e($lesson['title']) ?>"><i class="bi bi-pencil-fill text-secondary"></i></a></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if ($recentLessons === []): ?><tr><td colspan="5" class="text-center text-muted py-4">No lessons yet. <a href="lessons.php">Create your first lesson</a>.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Bootstrap 5 Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script src="assets1/index.js"></script>
</body>
</html>