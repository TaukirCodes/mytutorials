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

    <section class="admin-form-section mb-4">
      <h2 class="h5 fw-bold mb-3"><?= $editing ? 'Edit course' : 'Add a course' ?></h2>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">
        <div class="col-md-6"><label class="form-label">Course title<input class="form-control" name="title" maxlength="190" required value="<?= e($editing['title'] ?? '') ?>"></label></div>
        <div class="col-md-3"><label class="form-label">Category<input class="form-control" name="category" maxlength="100" value="<?= e($editing['category'] ?? 'Development') ?>"></label></div>
        <div class="col-md-3"><label class="form-label">Level<select class="form-select" name="level"><?php foreach (['Beginner', 'Intermediate', 'Advanced'] as $level): ?><option value="<?= e($level) ?>" <?= ($editing['level'] ?? 'Beginner') === $level ? 'selected' : '' ?>><?= e($level) ?></option><?php endforeach; ?></select></label></div>
        <div class="col-12"><label class="form-label">Description<textarea class="form-control" name="description" rows="2" maxlength="2000" required><?= e($editing['description'] ?? '') ?></textarea></label></div>
        <div class="col-12 d-flex flex-wrap align-items-center gap-3">
          <label class="form-check"><input class="form-check-input" type="checkbox" name="is_published" value="1" <?= !empty($editing['is_published']) ? 'checked' : '' ?>><span class="form-check-label">Published</span></label>
          <button class="btn btn-blue" type="submit"><i class="bi bi-check2 me-1"></i><?= $editing ? 'Save course' : 'Create course' ?></button>
          <?php if ($editing): ?><a class="btn btn-link" href="courses.php">Cancel edit</a><?php endif; ?>
        </div>
      </form>
    </section>

    <section class="admin-form-section">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead><tr><th>Course</th><th>Level</th><th>Lessons</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
          <?php foreach ($courses as $course): ?>
            <tr>
              <td><div class="fw-semibold"><?= e($course['title']) ?></div><div class="small text-muted"><?= e($course['category']) ?></div></td>
              <td><?= e($course['level']) ?></td><td><?= (int) $course['lesson_count'] ?></td>
              <td><span class="badge <?= $course['is_published'] ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>"><?= $course['is_published'] ? 'Published' : 'Draft' ?></span></td>
              <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-light border" href="courses.php?edit=<?= (int) $course['id'] ?>" aria-label="Edit course"><i class="bi bi-pencil"></i></a>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this course and all its lessons? This cannot be undone.');">
                  <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $course['id'] ?>"><input type="hidden" name="action" value="delete">
                  <button class="btn btn-sm btn-light border text-danger" type="submit" aria-label="Delete course"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($courses === []): ?><tr><td colspan="5" class="text-center text-muted py-4">No courses yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
      </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets1/index.js"></script>
</body>
</html>