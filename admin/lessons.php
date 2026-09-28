<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_admin();
$activeAdminPage = 'lessons';

$database = db();
$error = '';
$editing = null;
$editingQuestion = null;
$statusFilter = (string) ($_GET['status'] ?? 'all');
$statusFilter = in_array($statusFilter, ['all', 'draft', 'published'], true) ? $statusFilter : 'all';
$courses = $database->query('SELECT id, title FROM courses ORDER BY title')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? 'save');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);

    try {
        if ($action === 'delete' && $id) {
            $statement = $database->prepare('DELETE FROM lessons WHERE id = :id');
            $statement->execute(['id' => $id]);
            header('Location: lessons.php?deleted=1');
            exit;
        }

        $courseId = filter_var($_POST['course_id'] ?? null, FILTER_VALIDATE_INT);
        $title = trim((string) ($_POST['title'] ?? ''));
        $topic = trim((string) ($_POST['topic'] ?? ''));
        $summary = trim((string) ($_POST['summary'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        $codeSample = trim((string) ($_POST['code_sample'] ?? ''));
        $quizQuestion = trim((string) ($_POST['quiz_question'] ?? ''));
        $quizOptions = [
          'a' => trim((string) ($_POST['option_a'] ?? '')),
          'b' => trim((string) ($_POST['option_b'] ?? '')),
          'c' => trim((string) ($_POST['option_c'] ?? '')),
        ];
        $correctOption = (string) ($_POST['correct_option'] ?? 'a');
        $quizExplanation = trim((string) ($_POST['quiz_explanation'] ?? ''));

        if (!$courseId || $title === '' || $topic === '' || $summary === '' || $body === '') {
            throw new InvalidArgumentException('Course, title, topic, summary, and lesson content are required.');
        }
        if ($quizQuestion !== '' && (in_array('', $quizOptions, true) || !in_array($correctOption, ['a', 'b', 'c'], true) || $quizExplanation === '')) {
          throw new InvalidArgumentException('Complete all quiz choices, the correct answer, and its explanation.');
        }

        $values = [
            'course_id' => $courseId,
            'slug' => slugify($title),
            'topic' => $topic,
            'title' => $title,
            'summary' => $summary,
            'body' => $body,
            'code_sample' => $codeSample,
            'position' => max(0, (int) ($_POST['position'] ?? 0)),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];

        $database->beginTransaction();
        if ($id) {
            $values['id'] = $id;
            $statement = $database->prepare('UPDATE lessons SET course_id = :course_id, slug = :slug, topic = :topic, title = :title, summary = :summary, body = :body, code_sample = :code_sample, position = :position, is_published = :is_published WHERE id = :id');
          $lessonId = (int) $id;
        } else {
            $statement = $database->prepare('INSERT INTO lessons (course_id, slug, topic, title, summary, body, code_sample, position, is_published) VALUES (:course_id, :slug, :topic, :title, :summary, :body, :code_sample, :position, :is_published)');
        }
        $statement->execute($values);
        if (!$id) {
          $lessonId = (int) $database->lastInsertId();
        }
        $statement = $database->prepare('DELETE FROM quiz_questions WHERE lesson_id = :lesson_id');
        $statement->execute(['lesson_id' => $lessonId]);
        if ($quizQuestion !== '') {
          $statement = $database->prepare('INSERT INTO quiz_questions (lesson_id, question, option_a, option_b, option_c, correct_option, explanation) VALUES (:lesson_id, :question, :option_a, :option_b, :option_c, :correct_option, :explanation)');
          $statement->execute([
            'lesson_id' => $lessonId,
            'question' => $quizQuestion,
            'option_a' => $quizOptions['a'],
            'option_b' => $quizOptions['b'],
            'option_c' => $quizOptions['c'],
            'correct_option' => $correctOption,
            'explanation' => $quizExplanation,
          ]);
        }
        $database->commit();
        header('Location: lessons.php?saved=1');
        exit;
    } catch (InvalidArgumentException $exception) {
        if ($database->inTransaction()) $database->rollBack();
        $error = $exception->getMessage();
    } catch (PDOException $exception) {
        if ($database->inTransaction()) $database->rollBack();
        $error = 'Could not save this lesson. Check that its course exists and its title is unique within that course.';
    }
}

if (isset($_GET['edit'])) {
    $statement = $database->prepare('SELECT * FROM lessons WHERE id = :id');
    $statement->execute(['id' => (int) $_GET['edit']]);
    $editing = $statement->fetch() ?: null;
    if ($editing) {
      $statement = $database->prepare('SELECT * FROM quiz_questions WHERE lesson_id = :lesson_id ORDER BY position LIMIT 1');
      $statement->execute(['lesson_id' => $editing['id']]);
      $editingQuestion = $statement->fetch() ?: null;
    }
}

$lessonQuery = 'SELECT lessons.*, courses.title AS course_title FROM lessons JOIN courses ON courses.id = lessons.course_id';
if ($statusFilter === 'draft') {
  $lessonQuery .= ' WHERE lessons.is_published = 0';
} elseif ($statusFilter === 'published') {
  $lessonQuery .= ' WHERE lessons.is_published = 1';
}
$lessonQuery .= ' ORDER BY lessons.is_published ASC, courses.title, lessons.position, lessons.title';
$lessons = $database->query($lessonQuery)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title>Lessons | SkillNovi Studio</title>
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
      <div><p class="text-uppercase small fw-bold text-primary mb-1">Content management</p><h1 class="h3 fw-bold mb-1">Lessons</h1><p class="text-muted mb-0">Write, review, and publish course chapters.</p></div>
      <a class="btn btn-outline-secondary" href="index.php"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
    </div>
    <?php if ($error !== ''): ?><div class="alert alert-warning"><?= e($error) ?></div><?php endif; ?>
    <?php if (isset($_GET['saved'])): ?><div class="alert alert-success">Lesson saved.</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Lesson deleted.</div><?php endif; ?>

    <section class="admin-form-section mb-4">
      <h2 class="h5 fw-bold mb-3"><?= $editing ? 'Edit lesson' : 'Add a lesson' ?></h2>
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">
        <div class="col-md-5"><label class="form-label">Course<select class="form-select" name="course_id" required><option value="">Choose a course</option><?php foreach ($courses as $course): ?><option value="<?= (int) $course['id'] ?>" <?= (int) ($editing['course_id'] ?? 0) === (int) $course['id'] ? 'selected' : '' ?>><?= e($course['title']) ?></option><?php endforeach; ?></select></label></div>
        <div class="col-md-5"><label class="form-label">Lesson title<input class="form-control" name="title" maxlength="190" required value="<?= e($editing['title'] ?? '') ?>"></label></div>
        <div class="col-md-2"><label class="form-label">Order<input class="form-control" type="number" min="0" name="position" value="<?= e($editing['position'] ?? 0) ?>"></label></div>
        <div class="col-md-4"><label class="form-label">Topic/module<input class="form-control" name="topic" maxlength="190" required value="<?= e($editing['topic'] ?? '') ?>"></label></div>
        <div class="col-md-8"><label class="form-label">Short summary<input class="form-control" name="summary" maxlength="1000" required value="<?= e($editing['summary'] ?? '') ?>"></label></div>
        <div class="col-12"><label class="form-label">Lesson content<textarea class="form-control" name="body" rows="5" required><?= e($editing['body'] ?? '') ?></textarea></label></div>
        <div class="col-12"><label class="form-label">Code example<textarea class="form-control font-monospace" name="code_sample" rows="8" spellcheck="false"><?= e($editing['code_sample'] ?? '') ?></textarea></label></div>
        <div class="col-12"><div class="quiz-editor-heading"><div><h3 class="h6 fw-bold mb-1">Lesson quiz</h3><p class="small text-muted mb-0">Optional single-question knowledge check.</p></div><button class="btn btn-outline-primary btn-sm" type="button" data-generate-draft><i class="bi bi-stars me-1"></i>Generate draft with AI</button></div></div>
        <div class="col-md-12"><label class="form-label">Question<input class="form-control" name="quiz_question" maxlength="1000" value="<?= e($editingQuestion['question'] ?? '') ?>"></label></div>
        <div class="col-md-4"><label class="form-label">Choice A<input class="form-control" name="option_a" maxlength="500" value="<?= e($editingQuestion['option_a'] ?? '') ?>"></label></div>
        <div class="col-md-4"><label class="form-label">Choice B<input class="form-control" name="option_b" maxlength="500" value="<?= e($editingQuestion['option_b'] ?? '') ?>"></label></div>
        <div class="col-md-4"><label class="form-label">Choice C<input class="form-control" name="option_c" maxlength="500" value="<?= e($editingQuestion['option_c'] ?? '') ?>"></label></div>
        <div class="col-md-4"><label class="form-label">Correct choice<select class="form-select" name="correct_option"><?php foreach (['a' => 'A', 'b' => 'B', 'c' => 'C'] as $value => $label): ?><option value="<?= e($value) ?>" <?= ($editingQuestion['correct_option'] ?? 'a') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label></div>
        <div class="col-md-8"><label class="form-label">Answer explanation<input class="form-control" name="quiz_explanation" maxlength="2000" value="<?= e($editingQuestion['explanation'] ?? '') ?>"></label></div>
        <div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" data-ai-draft-consent><span class="form-check-label small">I confirm the course and topic may be sent to Groq to generate this draft.</span></label><div class="small mt-2" data-ai-draft-status aria-live="polite"></div></div>
        <div class="col-12 d-flex flex-wrap align-items-center gap-3">
          <label class="form-check"><input class="form-check-input" type="checkbox" name="is_published" value="1" <?= !empty($editing['is_published']) ? 'checked' : '' ?>><span class="form-check-label">Published</span></label>
          <button class="btn btn-blue" type="submit"><i class="bi bi-check2 me-1"></i><?= $editing ? 'Save lesson' : 'Create lesson' ?></button>
          <?php if ($editing): ?><a class="btn btn-link" href="lessons.php">Cancel edit</a><?php endif; ?>
        </div>
      </form>
    </section>

    <section class="admin-form-section">
      <div class="lesson-list-toolbar">
        <div><h2 class="h5 fw-bold mb-1">Lesson library</h2><p class="small text-muted mb-0"><?= count($lessons) ?> <?= $statusFilter === 'all' ? 'total' : e($statusFilter) ?> lessons</p></div>
        <div class="btn-group btn-group-sm" role="group" aria-label="Filter lessons by status">
          <a class="btn <?= $statusFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>" href="lessons.php">All</a>
          <a class="btn <?= $statusFilter === 'draft' ? 'btn-primary' : 'btn-outline-secondary' ?>" href="lessons.php?status=draft">Drafts</a>
          <a class="btn <?= $statusFilter === 'published' ? 'btn-primary' : 'btn-outline-secondary' ?>" href="lessons.php?status=published">Published</a>
        </div>
      </div>
      <div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Lesson</th><th>Course</th><th>Topic</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($lessons as $lesson): ?>
          <tr><td class="fw-semibold"><?= e($lesson['title']) ?></td><td><?= e($lesson['course_title']) ?></td><td><?= e($lesson['topic']) ?></td><td><span class="badge <?= $lesson['is_published'] ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>"><?= $lesson['is_published'] ? 'Published' : 'Draft' ?></span></td><td class="text-end text-nowrap">
            <a class="btn btn-sm btn-light border" href="lessons.php?edit=<?= (int) $lesson['id'] ?>" aria-label="Edit lesson"><i class="bi bi-pencil"></i></a>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this lesson and its quiz questions? This cannot be undone.');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $lesson['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-light border text-danger" type="submit" aria-label="Delete lesson"><i class="bi bi-trash"></i></button></form>
          </td></tr>
        <?php endforeach; ?>
        <?php if ($lessons === []): ?><tr><td colspan="5" class="text-center text-muted py-4">No lessons yet.</td></tr><?php endif; ?>
        </tbody>
      </table></div>
    </section>
      </div>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets1/index.js"></script>
</body>
</html>
