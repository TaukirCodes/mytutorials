<nav id="sidebar" class="learning-sidebar" aria-label="Learning paths">
  <div class="sidebar-header">
    <p class="eyebrow mb-1" data-i18n="sidebar.kicker">YOUR CURRICULUM</p>
    <div class="sidebar-heading-row">
      <h2 data-i18n="sidebar.heading">Learning paths</h2>
      <button class="sidebar-close" type="button" data-sidebar-close aria-label="Close learning navigation" data-i18n-aria-label="sidebar.close"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
    <p class="sidebar-summary"><strong><?= count($courses) ?></strong> <span data-i18n="stats.paths">paths</span><span class="sidebar-summary-separator">/</span><strong><?= count($navigationLessons) ?></strong> <span data-i18n="stats.lessons">lessons</span></p>
  </div>
  <div class="sidebar-path-list">
    <?php foreach ($courses as $course): ?>
    <?php
      $courseLessons = array_values(array_filter($navigationLessons, static fn(array $lesson): bool => (int) $lesson['course_id'] === (int) $course['id']));
      $isCurrentPath = (int) $course['id'] === (int) ($currentCourseId ?? 0);
      $pathId = 'learningPath-' . (int) $course['id'];
    ?>
    <section class="sidebar-path <?= $isCurrentPath ? 'is-current' : '' ?>" data-path-card>
          <button class="sidebar-path-toggle" type="button" data-path-toggle aria-expanded="<?= $isCurrentPath ? 'true' : 'false' ?>" aria-controls="<?= e($pathId) ?>">
        <span class="sidebar-path-icon"><i class="bi bi-journal-code" aria-hidden="true"></i></span>
        <span class="sidebar-path-copy"><strong><?= e($course['title']) ?></strong><small><?= e($course['level']) ?> <span aria-hidden="true">·</span> <?= count($courseLessons) ?> <span data-i18n="stats.lessons">lessons</span></small></span>
        <i class="bi bi-chevron-down sidebar-path-chevron" aria-hidden="true"></i>
      </button>
      <div class="sidebar-path-content" id="<?= e($pathId) ?>" <?= $isCurrentPath ? '' : 'hidden' ?>>
        <a class="sidebar-overview <?= $isCurrentPath ? 'is-active' : '' ?>" href="index.php?course=<?= e($course['slug']) ?>" <?= $isCurrentPath && $currentLessonId === 0 ? 'aria-current="page"' : '' ?>><i class="bi bi-grid-1x2" aria-hidden="true"></i><span data-i18n="sidebar.overview">Path overview</span></a>
        <?php if ($courseLessons !== []): ?>
        <ul class="sidebar-lesson-list">
          <?php foreach ($courseLessons as $navigationLesson): ?>
          <?php $isCurrentLesson = (int) $navigationLesson['id'] === (int) ($currentLessonId ?? 0); ?>
          <li>
            <a href="lesson.php?id=<?= (int) $navigationLesson['id'] ?>" class="sidebar-lesson <?= $isCurrentLesson ? 'is-active' : '' ?>" data-sidebar-lesson data-lesson-id="<?= (int) $navigationLesson['id'] ?>" <?= $isCurrentLesson ? 'aria-current="page"' : '' ?>>
              <span class="sidebar-lesson-marker"><i class="bi <?= $isCurrentLesson ? 'bi-play-fill' : 'bi-circle' ?>" aria-hidden="true"></i></span>
              <span class="sidebar-lesson-title"><?= e($navigationLesson['title']) ?></span>
              <span class="sidebar-lesson-state" data-sidebar-lesson-state <?= $isCurrentLesson ? 'data-state-key="sidebar.now"' : '' ?>><?= $isCurrentLesson ? 'Now' : '' ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p class="sidebar-empty-path" data-i18n="sidebar.no-lessons">Lessons coming soon</p>
        <?php endif; ?>
      </div>
    </section>
    <?php endforeach; ?>
    <?php if ($courses === []): ?><p class="sidebar-empty-path" data-i18n="sidebar.empty">No published courses yet.</p><?php endif; ?>
  </div>
  <div class="sidebar-footer"><i class="bi bi-lightning-charge" aria-hidden="true"></i><span data-i18n="sidebar.promise">Small lessons. Real progress.</span></div>
</nav>
<button class="sidebar-backdrop" type="button" data-sidebar-close aria-label="Close learning navigation" data-i18n-aria-label="sidebar.close"></button>