<?php
declare(strict_types=1);
require_once __DIR__ . '/config/app.php';

$databaseError = '';
$lesson = null;
$question = null;
$courses = [];
$navigationLessons = [];

try {
    $database = db();
    $courses = $database->query('SELECT id, slug, title FROM courses WHERE is_published = 1 ORDER BY title')->fetchAll();
    $navigationLessons = $database->query('SELECT lessons.id, lessons.course_id, lessons.title, lessons.topic, lessons.slug FROM lessons JOIN courses ON courses.id = lessons.course_id WHERE lessons.is_published = 1 AND courses.is_published = 1 ORDER BY courses.title, lessons.position, lessons.title')->fetchAll();
    $statement = $database->prepare('SELECT lessons.*, courses.title AS course_title, courses.slug AS course_slug FROM lessons JOIN courses ON courses.id = lessons.course_id WHERE lessons.id = :id AND lessons.is_published = 1 AND courses.is_published = 1 LIMIT 1');
    $statement->execute(['id' => max(0, (int) ($_GET['id'] ?? 0))]);
    $lesson = $statement->fetch() ?: null;

    if ($lesson) {
        $statement = $database->prepare('SELECT id, question, option_a, option_b, option_c FROM quiz_questions WHERE lesson_id = :lesson_id ORDER BY position LIMIT 1');
        $statement->execute(['lesson_id' => $lesson['id']]);
        $question = $statement->fetch() ?: null;
    }
} catch (Throwable $exception) {
    $databaseError = 'The lesson library is not available. Start MySQL and check the database setup.';
}

$currentLessonId = $lesson ? (int) $lesson['id'] : 0;
$currentCourseId = $lesson ? (int) $lesson['course_id'] : 0;

if ($lesson === null && $databaseError === '') {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title><?= $lesson ? e($lesson['title']) . ' | SkillNovi' : 'Lesson unavailable | SkillNovi' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body data-current-lesson-id="<?= $lesson ? (int) $lesson['id'] : '' ?>">
  <?php include __DIR__ . '/topnav.php'; ?>
  <div class="wrapper lesson-layout" style="padding-top: 56px;">
    <?php include __DIR__ . '/sidenav.php'; ?>
    <main class="w-100 p-4 lesson-main">
      <div class="container-fluid lesson-container">
        <?php if ($databaseError !== ''): ?>
          <div class="alert alert-warning" role="alert"><?= e($databaseError) ?></div>
        <?php elseif (!$lesson): ?>
          <div class="empty-state"><h1 class="h3">Lesson not found</h1><p>This lesson may be unpublished or no longer available.</p><a href="index.php">Browse lessons</a></div>
        <?php else: ?>
          <nav aria-label="breadcrumb"><ol class="breadcrumb small"><li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><?= e($lesson['course_title']) ?></a></li><li class="breadcrumb-item active" aria-current="page"><?= e($lesson['topic']) ?></li></ol></nav>
          <header class="lesson-page-heading mb-4">
            <div><p class="eyebrow mb-2"><?= e($lesson['course_title']) ?> / <?= e($lesson['topic']) ?></p><h1 class="h2 fw-bold mb-2"><?= e($lesson['title']) ?></h1><p class="text-muted mb-0"><?= e($lesson['summary']) ?></p></div>
            <div class="lesson-actions"><button type="button" class="btn btn-outline-secondary" data-progress-toggle data-lesson-id="<?= (int) $lesson['id'] ?>"><i class="bi bi-check2-circle me-1"></i><span data-i18n="lesson.mark">Mark complete</span></button><button type="button" class="btn btn-outline-secondary" data-bookmark-toggle data-lesson-id="<?= (int) $lesson['id'] ?>"><i class="bi bi-bookmark me-1"></i><span data-i18n="lesson.save">Save lesson</span></button></div>
          </header>

          <article class="lesson-article mb-4"><div class="lesson-prose"><?= nl2br(e($lesson['body'])) ?></div></article>

          <?php if (trim($lesson['code_sample']) !== ''): ?>
          <section class="card shadow-sm border-0 mb-4 code-snippet-card" aria-labelledby="code-title">
            <div class="card-header text-white d-flex justify-content-between align-items-center py-2"><span id="code-title" class="small fw-semibold"><i class="bi bi-code-slash me-2 text-primary"></i><span data-i18n="lesson.code">Code example</span></span><button class="btn btn-sm btn-outline-light border-secondary" type="button" data-copy-code="lessonCode"><i class="bi bi-clipboard me-1"></i><span data-i18n="lesson.copy">Copy code</span></button></div>
            <div class="card-body p-0"><pre><code id="lessonCode"><?= e($lesson['code_sample']) ?></code></pre></div>
          </section>
          <?php endif; ?>

          <?php if ($question): ?>
          <section class="learning-tool-section mb-4" aria-labelledby="quiz-title">
            <div class="section-heading"><div><p class="eyebrow mb-1" data-i18n="lesson.quiz.kicker">QUICK CHECK</p><h2 class="h5 fw-bold mb-0" id="quiz-title" data-i18n="lesson.quiz.title">Check your understanding</h2></div></div>
            <form class="quiz-form mt-3" data-quiz-form>
              <input type="hidden" name="question_id" value="<?= (int) $question['id'] ?>"><p class="fw-semibold"><?= e($question['question']) ?></p>
              <?php foreach (['a' => 'option_a', 'b' => 'option_b', 'c' => 'option_c'] as $key => $column): ?>
              <label class="quiz-option"><input type="radio" name="answer" value="<?= e($key) ?>" required><span><?= e($question[$column]) ?></span></label>
              <?php endforeach; ?>
              <button class="btn btn-outline-primary btn-sm mt-3" type="submit" data-i18n="lesson.quiz.submit">Check answer</button><p class="small mt-3 mb-0" data-quiz-result aria-live="polite"></p>
            </form>
          </section>
          <?php endif; ?>

          <section class="learning-tool-section ai-tutor-section" aria-labelledby="tutor-title">
            <div class="section-heading"><div><p class="eyebrow mb-1" data-i18n="lesson.ai.kicker">LEARN WITH CONTEXT</p><h2 class="h5 fw-bold mb-0" id="tutor-title" data-i18n="lesson.ai.title">Ask the AI tutor</h2></div></div>
            <p class="small text-muted mt-2" data-i18n="lesson.ai.description">Your question, this lesson, and any code you add below will be sent to OpenAI only after you confirm this request.</p>
            <form class="vstack gap-3" data-ai-form>
              <input type="hidden" name="lesson_id" value="<?= (int) $lesson['id'] ?>">
              <label class="form-label mb-0"><span data-i18n="lesson.ai.mode">What do you want help with?</span>
                <select class="form-select mt-1" name="mode"><option value="tutor" data-i18n="lesson.ai.explain">Explain this lesson</option><option value="debug" data-i18n="lesson.ai.debug">Review my code</option></select>
              </label>
              <label class="form-label mb-0"><span data-i18n="lesson.ai.question">Question</span><textarea class="form-control mt-1" name="question" rows="2" maxlength="2000" required placeholder="Ask a focused question about this lesson" data-i18n-placeholder="lesson.ai.question.placeholder"></textarea></label>
              <label class="form-label mb-0"><span data-i18n="lesson.ai.code">Your code (optional)</span><textarea class="form-control font-monospace mt-1" name="user_code" rows="4" maxlength="12000" spellcheck="false" placeholder="Paste only code you are comfortable sharing with OpenAI" data-i18n-placeholder="lesson.ai.code.placeholder"></textarea></label>
              <label class="form-check"><input class="form-check-input" type="checkbox" name="consent" value="1" required><span class="form-check-label small" data-i18n="lesson.ai.consent">I confirm that this question and lesson context may be sent to OpenAI for this request.</span></label>
              <div><button class="btn btn-blue" type="submit"><i class="bi bi-stars me-1"></i><span data-i18n="lesson.ai.submit">Ask tutor</span></button></div>
              <div class="ai-response" data-ai-response aria-live="polite" hidden></div>
            </form>
          </section>
        <?php endif; ?>
      </div>
    </main>
  </div>
  <?php include __DIR__ . '/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/index.js"></script>
</body>
</html>