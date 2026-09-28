<?php
declare(strict_types=1);
require_once __DIR__ . '/config/app.php';

$searchTerm = trim((string) ($_GET['q'] ?? ''));
$courseSlug = trim((string) ($_GET['course'] ?? ''));
$databaseError = '';
$courses = [];
$navigationLessons = [];
$matchingLessons = [];

try {
  $database = db();
  $courses = $database->query('SELECT courses.id, courses.slug, courses.title, courses.category, courses.description, courses.level, COUNT(lessons.id) AS lesson_count FROM courses LEFT JOIN lessons ON lessons.course_id = courses.id AND lessons.is_published = 1 WHERE courses.is_published = 1 GROUP BY courses.id ORDER BY courses.title')->fetchAll();
  $navigationLessons = $database->query('SELECT lessons.id, lessons.course_id, lessons.title, lessons.topic, lessons.slug FROM lessons JOIN courses ON courses.id = lessons.course_id WHERE lessons.is_published = 1 AND courses.is_published = 1 ORDER BY courses.title, lessons.position, lessons.title')->fetchAll();

  $sql = 'SELECT lessons.id, lessons.course_id, lessons.topic, lessons.title, lessons.summary, courses.title AS course_title, courses.slug AS course_slug FROM lessons JOIN courses ON courses.id = lessons.course_id WHERE lessons.is_published = 1 AND courses.is_published = 1';
  $parameters = [];
  if ($searchTerm !== '') {
    $sql .= ' AND (lessons.title LIKE :title_term OR lessons.topic LIKE :topic_term OR lessons.summary LIKE :summary_term OR lessons.body LIKE :body_term OR courses.title LIKE :course_term)';
    $pattern = '%' . $searchTerm . '%';
    $parameters = ['title_term' => $pattern, 'topic_term' => $pattern, 'summary_term' => $pattern, 'body_term' => $pattern, 'course_term' => $pattern];
  }
  if ($courseSlug !== '') {
    $sql .= ' AND courses.slug = :course_slug';
    $parameters['course_slug'] = $courseSlug;
  }
  $sql .= ' ORDER BY courses.title, lessons.position, lessons.title';
  $statement = $database->prepare($sql);
  $statement->execute($parameters);
  $matchingLessons = $statement->fetchAll();
} catch (Throwable $exception) {
  $databaseError = 'The learning library is not ready yet. Start MySQL, import database/schema.sql, and check your local .env settings.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title>DevDocs - Tech Tutorial Portal</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>

  <!-- Top Navbar Header -->
  <?php include('topnav.php')?>

  <!-- Page Body Wrapper -->
  <div class="wrapper" style="padding-top: 56px;">
    
    <!-- Toggleable Side Navbar -->
   <?php include('sidenav.php')?>

    <!-- Main Content Area -->
    <main class="w-100 p-4">
      <div class="container-fluid learner-dashboard">
        <header class="learning-heading mb-4">
          <div>
            <p class="eyebrow mb-2" <?= $searchTerm === '' && $courseSlug === '' ? 'data-i18n="home.kicker"' : '' ?>>DEV DOCS / LEARNING LIBRARY</p>
            <h1 class="h2 fw-bold mb-2" <?= $searchTerm === '' && $courseSlug === '' ? 'data-i18n="home.title"' : '' ?>><?= $searchTerm !== '' ? 'Search results' : ($courseSlug !== '' ? 'Course lessons' : 'Your developer learning space') ?></h1>
            <p class="text-muted mb-0" <?= $searchTerm === '' && $courseSlug === '' ? 'data-i18n="home.description"' : '' ?>><?= $searchTerm !== '' ? 'Lessons matching “' . e($searchTerm) . '”' : 'Pick up where you left off, or choose a new skill to explore.' ?></p>
          </div>
          <div class="learning-stats"><span><strong><?= count($courses) ?></strong> <span data-i18n="stats.paths">paths</span></span><span><strong><?= count($navigationLessons) ?></strong> <span data-i18n="stats.lessons">lessons</span></span><span data-progress-summary>0 completed</span></div>
        </header>
        <p class="content-language-note" data-content-language-note hidden></p>

        <?php if ($databaseError !== ''): ?><div class="alert alert-warning" role="alert"><?= e($databaseError) ?></div><?php endif; ?>

        <?php if ($searchTerm === '' && $courseSlug === ''): ?>
        <section class="continue-panel mb-5" data-next-lesson hidden aria-labelledby="continue-title">
          <div class="continue-panel-icon"><i class="bi bi-play-fill" aria-hidden="true"></i></div>
          <div class="continue-panel-copy"><p class="eyebrow mb-1" data-i18n="home.continue.kicker">YOUR NEXT STEP</p><h2 class="h4 fw-bold mb-1" id="continue-title" data-i18n="home.continue.title">Continue learning</h2><p class="small mb-0" data-next-lesson-meta>Pick up with your last lesson.</p></div>
          <a class="btn btn-light fw-semibold" data-next-lesson-link href="#"><span data-next-lesson-title>Resume lesson</span> <i class="bi bi-arrow-up-right ms-1" aria-hidden="true"></i></a>
        </section>
        <section class="new-learner-panel mb-5" data-new-learner hidden aria-labelledby="new-learner-title">
          <div class="new-learner-copy"><p class="eyebrow mb-2" data-i18n="home.start.kicker">A GOOD PLACE TO START</p><h2 class="h3 fw-bold mb-2" id="new-learner-title" data-i18n="home.start.title">Choose a path. Build a real skill.</h2><p class="mb-0" data-i18n="home.start.description">Follow focused lessons, practice what you learn, and grow at your own pace.</p></div>
          <a class="btn btn-light fw-semibold" href="#courses"><span data-i18n="home.start.action">Explore learning paths</span><i class="bi bi-arrow-down ms-2" aria-hidden="true"></i></a>
          <i class="bi bi-braces-asterisk new-learner-mark" aria-hidden="true"></i>
        </section>
        <?php endif; ?>

        <?php if ($searchTerm === '' && $courseSlug === ''): ?>
        <section id="courses" class="mb-5">
          <div class="section-heading"><div><p class="eyebrow mb-1" data-i18n="home.paths.kicker">LEARNING PATHS</p><h2 class="h4 fw-bold mb-0" data-i18n="home.paths.title">Choose what you want to build</h2></div><span class="section-aside" data-i18n="home.paths.note">Free to start · Learn at your pace</span></div>
          <div class="row g-3 mt-1">
            <?php foreach ($courses as $course): ?>
            <div class="col-md-6 col-xl-4"><article class="course-tile h-100"><div class="d-flex justify-content-between align-items-start gap-3"><span class="course-category"><?= e($course['category']) ?></span><i class="bi bi-journal-code text-primary" aria-hidden="true"></i></div><h3 class="h5 fw-bold mt-3 mb-2"><?= e($course['title']) ?></h3><p class="text-muted small flex-grow-1"><?= e($course['description']) ?></p><div class="d-flex justify-content-between align-items-center mt-3"><span class="small text-muted"><?= (int) $course['lesson_count'] ?> lessons · <?= e($course['level']) ?></span><a class="btn btn-outline-primary btn-sm" href="index.php?course=<?= e($course['slug']) ?>" data-i18n="course.open">Open path <i class="bi bi-arrow-right ms-1"></i></a></div></article></div>
            <?php endforeach; ?>
            <?php if ($courses === [] && $databaseError === ''): ?><p class="text-muted">No published courses are available yet.</p><?php endif; ?>
          </div>
        </section>
        <?php endif; ?>

        <section class="smart-search-section mb-4" aria-labelledby="smart-search-title">
          <div><p class="eyebrow mb-1" data-i18n="home.search.kicker">SMART DISCOVERY</p><h2 class="h6 fw-bold mb-2" id="smart-search-title" data-i18n="home.search.title">Describe what you want to learn</h2></div>
          <form class="smart-search-form" data-smart-search-form>
            <input type="hidden" name="mode" value="search">
            <label class="visually-hidden" for="smartSearchQuery">Search in plain language</label>
            <input class="form-control" id="smartSearchQuery" name="question" maxlength="500" required placeholder="For example: how do I loop through a list?" data-i18n-placeholder="home.search.placeholder"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-stars me-1"></i><span data-i18n="home.search.action">Find lessons</span></button>
            <label class="form-check"><input class="form-check-input" type="checkbox" name="consent" value="1" required><span class="form-check-label small" data-i18n="home.search.consent">I confirm this query may be sent to OpenAI.</span></label>
          </form>
          <div class="smart-search-results" data-smart-search-results aria-live="polite"></div>
        </section>

        <section class="lesson-list-section" aria-labelledby="lesson-list-title">
          <div class="section-heading"><div><p class="eyebrow mb-1" data-i18n="home.lesson.kicker">KEEP GOING</p><h2 class="h4 fw-bold mb-0" id="lesson-list-title" <?= $searchTerm === '' ? 'data-i18n="home.lesson.title"' : '' ?>><?= $searchTerm !== '' ? 'Matching lessons' : 'Lessons' ?></h2></div><div class="d-flex gap-3 align-items-center"><button class="btn btn-sm btn-link p-0" type="button" data-show-bookmarks data-i18n="home.saved">Saved lessons</button><span class="small text-muted"><?= count($matchingLessons) ?> <span data-i18n="stats.results">results</span></span></div></div>
          <div class="lesson-list mt-3">
            <?php foreach ($matchingLessons as $lesson): ?>
            <a class="lesson-row" href="lesson.php?id=<?= (int) $lesson['id'] ?>" data-lesson-row data-lesson-id="<?= (int) $lesson['id'] ?>">
              <span class="lesson-index"><i class="bi bi-play-fill" aria-hidden="true"></i></span>
              <span class="lesson-row-copy"><span class="small text-muted"><?= e($lesson['course_title']) ?> / <?= e($lesson['topic']) ?></span><strong><?= e($lesson['title']) ?></strong><span class="small text-muted"><?= e($lesson['summary']) ?></span></span>
              <span class="lesson-row-bookmark" data-bookmark-state aria-hidden="true"></span>
              <span class="lesson-row-state" data-progress-state>Start lesson</span>
              <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </a>
            <?php endforeach; ?>
            <?php if ($matchingLessons === [] && $databaseError === ''): ?><p class="empty-state">No matching lessons. Try a broader search or choose another track.</p><?php endif; ?>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- Footer -->
  <?php include('footer.php')?>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/index.js"></script>
  
</body>
</html>