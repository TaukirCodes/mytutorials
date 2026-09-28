 <nav id="sidebar" class="py-3 shadow-sm">
      <div class="px-3 mb-3">
        <h6 class="text-uppercase text-muted fw-bold small" data-i18n="sidebar.heading">Learning paths</h6>
      </div>
      <ul class="list-unstyled sidebar-menu">
        <?php foreach ($courses as $course): ?>
        <?php $courseLessons = array_values(array_filter($navigationLessons, static fn(array $lesson): bool => (int) $lesson['course_id'] === (int) $course['id'])); ?>
        <li class="sidebar-course">
          <a href="index.php?course=<?= e($course['slug']) ?>" class="nav-link d-flex justify-content-between align-items-center">
            <span><i class="bi bi-journal-code me-2 text-primary"></i><?= e($course['title']) ?></span>
            <span class="badge text-bg-light"><?= count($courseLessons) ?></span>
          </a>
          <?php if ($courseLessons !== []): ?>
          <ul class="list-unstyled submenu-subtopic">
            <?php foreach ($courseLessons as $navigationLesson): ?>
            <li><a href="lesson.php?id=<?= (int) $navigationLesson['id'] ?>" class="nav-link small"><i class="bi bi-circle me-2" style="font-size: 6px;"></i><?= e($navigationLesson['title']) ?></a></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <?php if ($courses === []): ?><li class="px-3 small text-muted" data-i18n="sidebar.empty">No published courses yet.</li><?php endif; ?>
      </ul>
    </nav>