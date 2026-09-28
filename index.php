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
            <p class="eyebrow mb-2">DEV DOCS / LEARNING LIBRARY</p>
            <h1 class="h2 fw-bold mb-2"><?= $searchTerm !== '' ? 'Search results' : ($courseSlug !== '' ? 'Course lessons' : 'Build a stronger foundation') ?></h1>
            <p class="text-muted mb-0"><?= $searchTerm !== '' ? 'Lessons matching “' . e($searchTerm) . '”' : 'Focused, practical tutorials with examples you can reuse.' ?></p>
          </div>
          <div class="learning-stats"><span><?= count($courses) ?> tracks</span><span><?= count($navigationLessons) ?> lessons</span><span data-progress-summary>0 completed in this browser</span></div>
        </header>
        <div class="learning-recommendation mb-4" data-next-lesson hidden><span><i class="bi bi-compass me-1" aria-hidden="true"></i>Suggested next</span><a data-next-lesson-link href="#">Continue learning</a></div>

        <?php if ($databaseError !== ''): ?><div class="alert alert-warning" role="alert"><?= e($databaseError) ?></div><?php endif; ?>

        <?php if ($searchTerm === '' && $courseSlug === ''): ?>
        <section class="lesson-feature mb-5" aria-labelledby="feature-title">
          <div><p class="eyebrow mb-2">START LEARNING</p><h2 class="h3 fw-bold" id="feature-title">Small lessons. Real working code.</h2><p class="mb-3">Choose a track, explore its chapters, and keep your progress in this browser.</p><a class="btn btn-light btn-sm fw-semibold" href="#courses">Browse learning tracks <i class="bi bi-arrow-down ms-1"></i></a></div>
          <i class="bi bi-braces feature-mark" aria-hidden="true"></i>
        </section>
        <?php endif; ?>

        <?php if ($searchTerm === '' && $courseSlug === ''): ?>
        <section id="courses" class="mb-5">
          <div class="section-heading"><div><p class="eyebrow mb-1">LEARNING TRACKS</p><h2 class="h4 fw-bold mb-0">Explore courses</h2></div></div>
          <div class="row g-3 mt-1">
            <?php foreach ($courses as $course): ?>
            <div class="col-md-6 col-xl-4"><article class="course-tile h-100"><div class="d-flex justify-content-between align-items-start gap-3"><span class="course-category"><?= e($course['category']) ?></span><i class="bi bi-journal-code text-primary" aria-hidden="true"></i></div><h3 class="h5 fw-bold mt-3 mb-2"><?= e($course['title']) ?></h3><p class="text-muted small flex-grow-1"><?= e($course['description']) ?></p><div class="d-flex justify-content-between align-items-center mt-3"><span class="small text-muted"><?= (int) $course['lesson_count'] ?> lessons · <?= e($course['level']) ?></span><a class="btn btn-outline-primary btn-sm" href="index.php?course=<?= e($course['slug']) ?>">Open track <i class="bi bi-arrow-right ms-1"></i></a></div></article></div>
            <?php endforeach; ?>
            <?php if ($courses === [] && $databaseError === ''): ?><p class="text-muted">No published courses are available yet.</p><?php endif; ?>
          </div>
        </section>
        <?php endif; ?>

        <section class="smart-search-section mb-4" aria-labelledby="smart-search-title">
          <div><p class="eyebrow mb-1">NATURAL-LANGUAGE SEARCH</p><h2 class="h6 fw-bold mb-2" id="smart-search-title">Describe what you want to learn</h2></div>
          <form class="smart-search-form" data-smart-search-form>
            <input type="hidden" name="mode" value="search">
            <label class="visually-hidden" for="smartSearchQuery">Search in plain language</label>
            <input class="form-control" id="smartSearchQuery" name="question" maxlength="500" required placeholder="For example: how do I loop through a list?"><button class="btn btn-outline-primary" type="submit"><i class="bi bi-stars me-1"></i>Find lessons</button>
            <label class="form-check"><input class="form-check-input" type="checkbox" name="consent" value="1" required><span class="form-check-label small">I confirm this query may be sent to OpenAI.</span></label>
          </form>
          <div class="smart-search-results" data-smart-search-results aria-live="polite"></div>
        </section>

        <section class="lesson-list-section" aria-labelledby="lesson-list-title">
          <div class="section-heading"><div><p class="eyebrow mb-1">YOUR NEXT STEP</p><h2 class="h4 fw-bold mb-0" id="lesson-list-title"><?= $searchTerm !== '' ? 'Matching lessons' : 'Lessons' ?></h2></div><div class="d-flex gap-3 align-items-center"><button class="btn btn-sm btn-link p-0" type="button" data-show-bookmarks>Saved lessons</button><span class="small text-muted"><?= count($matchingLessons) ?> results</span></div></div>
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/bootstrap.bundle.min.js"></script>
  <script src="assets/index.js"></script>
  
</body>
</html>