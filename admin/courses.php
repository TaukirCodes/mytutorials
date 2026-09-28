<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_admin();
$activeAdminPage = 'courses';

$database = db();
$error = '';
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? 'save');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    try {
        if ($action === 'delete' && $id) {
            $statement = $database->prepare('DELETE FROM courses WHERE id = :id');
            $statement->execute(['id' => $id]);
            header('Location: courses.php?deleted=1');
            exit;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? 'Development'));
        $level = trim((string) ($_POST['level'] ?? 'Beginner'));
        $slug = slugify($title);

        if ($title === '' || $description === '') {
            throw new InvalidArgumentException('Course title and description are required.');
        }

        $values = [
            'slug' => $slug,
            'title' => $title,
            'category' => $category !== '' ? $category : 'Development',
            'description' => $description,
            'level' => $level !== '' ? $level : 'Beginner',
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];

        if ($id) {
            $values['id'] = $id;
            $statement = $database->prepare('UPDATE courses SET slug = :slug, title = :title, category = :category, description = :description, level = :level, is_published = :is_published WHERE id = :id');
        } else {
            $statement = $database->prepare('INSERT INTO courses (slug, title, category, description, level, is_published) VALUES (:slug, :title, :category, :description, :level, :is_published)');
        }
        $statement->execute($values);
        header('Location: courses.php?saved=1');
        exit;
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    } catch (PDOException $exception) {
        $error = 'Could not save the course. A course with a similar title may already exist.';
    }
}

if (isset($_GET['edit'])) {
    $statement = $database->prepare('SELECT * FROM courses WHERE id = :id');
    $statement->execute(['id' => (int) $_GET['edit']]);
    $editing = $statement->fetch() ?: null;
}

$courses = $database->query('SELECT courses.*, COUNT(lessons.id) AS lesson_count FROM courses LEFT JOIN lessons ON lessons.course_id = courses.id GROUP BY courses.id ORDER BY courses.created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Courses | SkillNovi Studio</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets1/style.css" rel="stylesheet">
  <link href="../assets/theme.css" rel="stylesheet">
  <script src="../assets/theme.js"></script>
</head>
<body>
  <?php include __DIR__ . '/topnav.php'; ?>
  <div class="sb-layout">
    <?php include __DIR__ . '/sidenav.php'; ?>
    <main class="sb-content">
      <div class="container-fluid admin-page-content">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
      <div><p class="text-uppercase small fw-bold text-primary mb-1">Content management</p><h1 class="h3 fw-bold mb-1">Courses</h1><p class="text-muted mb-0">Create and publish learning tracks.</p></div>
      <a class="btn btn-outline-secondary" href="index.php"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
    </div>
    <?php if ($error !== ''): ?><div class="alert alert-warning"><?= e($error) ?></div><?php endif; ?>
    <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Course saved.</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Course and its lessons deleted.</div><?php endif; ?>

    <section class="admin-form-section course-editor-card mb-4" id="course-editor-card">
      <div class="course-panel-heading">
        <span class="course-panel-icon" aria-hidden="true"><i class="bi bi-journal-plus"></i></span>
        <div>
          <p class="course-panel-kicker mb-1"><?= $editing ? 'UPDATE YOUR LIBRARY' : 'BUILD YOUR LIBRARY' ?></p>
          <h2 class="h5 fw-bold mb-1"><?= $editing ? 'Edit course' : 'Add a course' ?></h2>
          <p class="small text-muted mb-0">Create a clear learning track learners can follow.</p>
        </div>
      </div>
      <form method="post" class="row g-3 course-editor-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">
        <div class="col-md-6"><label class="form-label" for="courseTitle">Course title</label><input class="form-control" id="courseTitle" name="title" maxlength="190" required placeholder="e.g. AI Agents &amp; MCP with Python" value="<?= e($editing['title'] ?? '') ?>"></div>
        <div class="col-md-3"><label class="form-label" for="courseCategory">Category</label><input class="form-control" id="courseCategory" name="category" maxlength="100" placeholder="e.g. AI Engineering" value="<?= e($editing['category'] ?? 'Development') ?>"></div>
        <div class="col-md-3"><label class="form-label" for="courseLevel">Level</label><select class="form-select" id="courseLevel" name="level"><?php foreach (['Beginner', 'Intermediate', 'Advanced'] as $level): ?><option value="<?= e($level) ?>" <?= ($editing['level'] ?? 'Beginner') === $level ? 'selected' : '' ?>><?= e($level) ?></option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label" for="courseDescription">Description</label><textarea class="form-control" id="courseDescription" name="description" rows="3" maxlength="2000" required placeholder="Describe what learners will understand or build by the end of this course."><?= e($editing['description'] ?? '') ?></textarea><div class="form-text">A short, specific summary helps learners choose the right course.</div></div>
        <div class="col-12 course-editor-actions">
          <label class="form-check form-switch course-publish-switch"><input class="form-check-input" type="checkbox" role="switch" name="is_published" value="1" <?= !empty($editing['is_published']) ? 'checked' : '' ?>><span><span class="form-check-label d-block">Publish course</span><span class="course-publish-hint">Make this track visible on the learner site.</span></span></label>
          <div class="course-form-buttons">
          <?php if ($editing): ?><a class="btn btn-outline-secondary" href="courses.php">Cancel</a><?php endif; ?>
          <button class="btn btn-blue course-submit-button" type="submit"><i class="bi <?= $editing ? 'bi-check2' : 'bi-plus-lg' ?> me-1"></i><?= $editing ? 'Save changes' : 'Create course' ?><i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></button>
          </div>
        </div>
      </form>
    </section>

    <section class="admin-form-section course-library-panel" aria-labelledby="course-library-title">
      <header class="course-library-heading">
        <div>
          <p class="course-panel-kicker mb-1">YOUR CONTENT</p>
          <h2 class="h5 fw-bold mb-1" id="course-library-title">Course library</h2>
          <p class="small text-muted mb-0">Manage tracks, lesson counts, and publishing status.</p>
        </div>
        <div class="course-library-tools">
          <span class="course-count"><i class="bi bi-collection me-1" aria-hidden="true"></i><?= count($courses) ?> <?= count($courses) === 1 ? 'course' : 'courses' ?></span>
          <a class="btn btn-outline-primary btn-sm" href="#course-editor-card"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add course</a>
        </div>
      </header>
      <?php if ($courses === []): ?>
        <div class="course-empty-state"><span class="course-empty-icon"><i class="bi bi-journal-bookmark"></i></span><h3 class="h6 fw-bold">Your library is ready for its first course</h3><p class="small text-muted mb-0">Use the form above to create a learning track.</p></div>
      <?php else: ?>
        <div class="course-card-grid">
          <?php foreach ($courses as $course): ?>
            <article class="course-card">
              <div class="course-card-topline">
                <span class="course-card-icon" aria-hidden="true"><i class="bi bi-mortarboard-fill"></i></span>
                <span class="course-status <?= $course['is_published'] ? 'is-published' : 'is-draft' ?>"><span aria-hidden="true"></span><?= $course['is_published'] ? 'Published' : 'Draft' ?></span>
              </div>
              <div class="course-card-copy">
                <p class="course-card-category"><?= e($course['category']) ?></p>
                <h3><?= e($course['title']) ?></h3>
                <p class="course-card-description"><?= e($course['description']) ?></p>
              </div>
              <div class="course-card-meta">
                <span><i class="bi bi-bar-chart-line" aria-hidden="true"></i><?= e($course['level']) ?></span>
                <span><i class="bi bi-journal-text" aria-hidden="true"></i><strong><?= (int) $course['lesson_count'] ?></strong> <?= (int) $course['lesson_count'] === 1 ? 'lesson' : 'lessons' ?></span>
              </div>
              <footer class="course-card-footer">
                <span class="course-card-footer-label">COURSE TRACK</span>
                <div class="course-card-actions">
                  <a class="btn btn-sm course-action-button" href="courses.php?edit=<?= (int) $course['id'] ?>" aria-label="Edit <?= e($course['title']) ?>" title="Edit course"><i class="bi bi-pencil-square" aria-hidden="true"></i><span>Edit</span></a>
                  <form method="post" onsubmit="return confirm('Delete this course and all its lessons? This cannot be undone.');">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $course['id'] ?>"><input type="hidden" name="action" value="delete">
                    <button class="btn btn-sm course-action-button course-delete-button" type="submit" aria-label="Delete <?= e($course['title']) ?>" title="Delete course"><i class="bi bi-trash3" aria-hidden="true"></i><span>Delete</span></button>
                  </form>
                </div>
              </footer>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
      </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets1/index.js"></script>
</body>
</html>
