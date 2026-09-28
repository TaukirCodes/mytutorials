
const progressKey = 'devdocs.progress.v1';
const bookmarksKey = 'devdocs.bookmarks.v1';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
let showSavedOnly = false;

function readIds(key) {
  try {
    const value = JSON.parse(localStorage.getItem(key) ?? '[]');
    return Array.isArray(value) ? value.map(String) : [];
  } catch {
    return [];
  }
}

function writeIds(key, ids) {
  try {
    localStorage.setItem(key, JSON.stringify([...new Set(ids)]));
  } catch {
    return;
  }
}

function setToggleLabel(button, active, activeText, inactiveText, activeIcon, inactiveIcon) {
  const label = button.querySelector('span');
  const icon = button.querySelector('i');
  if (label) label.textContent = active ? activeText : inactiveText;
  if (icon) icon.className = `bi ${active ? activeIcon : inactiveIcon} me-1`;
  button.setAttribute('aria-pressed', String(active));
}

function refreshProgress() {
  const completed = readIds(progressKey);
  const bookmarks = readIds(bookmarksKey);
  document.querySelectorAll('[data-lesson-row]').forEach((row) => {
    const state = row.querySelector('[data-progress-state]');
    if (state && completed.includes(row.dataset.lessonId)) state.textContent = 'Completed';
    const bookmark = row.querySelector('[data-bookmark-state]');
    if (bookmark) bookmark.textContent = bookmarks.includes(row.dataset.lessonId) ? 'Saved' : '';
    row.hidden = showSavedOnly && !bookmarks.includes(row.dataset.lessonId);
  });

  const summary = document.querySelector('[data-progress-summary]');
  if (summary) summary.textContent = `${completed.length} completed in this browser`;

  const recommendation = document.querySelector('[data-next-lesson]');
  const recommendationLink = recommendation?.querySelector('[data-next-lesson-link]');
  const nextRow = Array.from(document.querySelectorAll('[data-lesson-row]')).find((row) => !completed.includes(row.dataset.lessonId));
  if (recommendation && recommendationLink && nextRow) {
    recommendation.hidden = false;
    recommendationLink.href = nextRow.href;
    recommendationLink.textContent = nextRow.querySelector('strong')?.textContent ?? 'Continue learning';
  } else if (recommendation) {
    recommendation.hidden = true;
  }

  const lessonId = document.querySelector('[data-progress-toggle]')?.dataset.lessonId;
  const progressButton = document.querySelector('[data-progress-toggle]');
  if (progressButton && lessonId) {
    setToggleLabel(progressButton, completed.includes(lessonId), 'Completed', 'Mark complete', 'bi-check2-circle', 'bi-circle');
  }

  const bookmarkButton = document.querySelector('[data-bookmark-toggle]');
  const bookmarkId = bookmarkButton?.dataset.lessonId;
  if (bookmarkButton && bookmarkId) {
    setToggleLabel(bookmarkButton, readIds(bookmarksKey).includes(bookmarkId), 'Saved', 'Save lesson', 'bi-bookmark-check', 'bi-bookmark');
  }
}

async function copyCode(button) {
  const code = document.getElementById(button.dataset.copyCode);
  if (!code) return;

  try {
    await navigator.clipboard.writeText(code.textContent ?? '');
    const original = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check2 me-1"></i>Copied';
    window.setTimeout(() => { button.innerHTML = original; }, 1600);
  } catch {
    button.textContent = 'Copy unavailable';
  }
}

async function submitQuiz(form) {
  const result = form.querySelector('[data-quiz-result]');
  const submit = form.querySelector('button[type="submit"]');
  const data = new FormData(form);
  data.set('answer', form.querySelector('input[name="answer"]:checked')?.value ?? '');
  if (result) result.textContent = 'Checking answer…';
  if (submit) submit.disabled = true;

  try {
    const response = await fetch('api/quiz.php', {
      method: 'POST',
      headers: { 'X-CSRF-Token': csrfToken },
      body: data,
      credentials: 'same-origin',
    });
    const payload = await response.json();
    if (!response.ok) throw new Error(payload.error ?? 'Could not check this answer.');
    if (result) result.textContent = `${payload.correct ? 'Correct.' : 'Not quite.'} ${payload.explanation}`;
    if (payload.correct) {
      writeIds(progressKey, [...readIds(progressKey), String(payload.lesson_id)]);
      refreshProgress();
    }
  } catch (error) {
    if (result) result.textContent = error.message;
  } finally {
    if (submit) submit.disabled = false;
  }
}

async function submitAiRequest(form) {
  const consent = form.elements.consent;
  if (!consent.checked) return;
  const confirmed = window.confirm('This sends the lesson, your question, and any code you pasted to OpenAI for this request. Continue?');
  if (!confirmed) return;

  const result = form.querySelector('[data-ai-response]');
  const submit = form.querySelector('button[type="submit"]');
  if (result) {
    result.hidden = false;
    result.textContent = 'Thinking…';
  }
  if (submit) submit.disabled = true;

  try {
    const response = await fetch('api/ai.php', {
      method: 'POST',
      headers: { 'X-CSRF-Token': csrfToken },
      body: new FormData(form),
      credentials: 'same-origin',
    });
    const payload = await response.json();
    if (!response.ok) throw new Error(payload.error ?? 'The AI request failed.');
    if (result) result.textContent = payload.answer;
  } catch (error) {
    if (result) result.textContent = error.message;
  } finally {
    consent.checked = false;
    if (submit) submit.disabled = false;
  }
}

document.getElementById('sidebarToggle')?.addEventListener('click', () => {
  const sidebar = document.getElementById('sidebar');
  if (window.matchMedia('(max-width: 767.98px)').matches) {
    document.body.classList.toggle('sidebar-open');
  } else {
    sidebar?.classList.toggle('collapsed');
  }
});

document.querySelectorAll('[data-copy-code]').forEach((button) => {
  button.addEventListener('click', () => copyCode(button));
});

document.querySelector('[data-progress-toggle]')?.addEventListener('click', (event) => {
  const button = event.currentTarget;
  const id = button.dataset.lessonId;
  const completed = readIds(progressKey);
  writeIds(progressKey, completed.includes(id) ? completed.filter((item) => item !== id) : [...completed, id]);
  refreshProgress();
});

document.querySelector('[data-bookmark-toggle]')?.addEventListener('click', (event) => {
  const button = event.currentTarget;
  const id = button.dataset.lessonId;
  const bookmarks = readIds(bookmarksKey);
  writeIds(bookmarksKey, bookmarks.includes(id) ? bookmarks.filter((item) => item !== id) : [...bookmarks, id]);
  refreshProgress();
});

document.querySelector('[data-quiz-form]')?.addEventListener('submit', (event) => {
  event.preventDefault();
  submitQuiz(event.currentTarget);
});

document.querySelector('[data-ai-form]')?.addEventListener('submit', (event) => {
  event.preventDefault();
  submitAiRequest(event.currentTarget);
});

document.querySelector('[data-show-bookmarks]')?.addEventListener('click', (event) => {
  showSavedOnly = !showSavedOnly;
  event.currentTarget.setAttribute('aria-pressed', String(showSavedOnly));
  event.currentTarget.textContent = showSavedOnly ? 'Show all lessons' : 'Saved lessons';
  refreshProgress();
});

document.querySelector('[data-smart-search-form]')?.addEventListener('submit', async (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const consent = form.elements.consent;
  if (!consent.checked || !window.confirm('This sends your search query to OpenAI. Continue?')) return;

  const results = document.querySelector('[data-smart-search-results]');
  const submit = form.querySelector('button[type="submit"]');
  if (results) results.textContent = 'Finding relevant lessons…';
  if (submit) submit.disabled = true;

  try {
    const response = await fetch('api/ai.php', {
      method: 'POST',
      headers: { 'X-CSRF-Token': csrfToken },
      body: new FormData(form),
      credentials: 'same-origin',
    });
    const payload = await response.json();
    if (!response.ok) throw new Error(payload.error ?? 'Smart search failed.');

    if (results) {
      results.replaceChildren();
      if (!payload.lessons?.length) {
        results.textContent = 'No relevant lessons found.';
      } else {
        payload.lessons.forEach((lesson) => {
          const link = document.createElement('a');
          link.className = 'smart-result-row';
          link.href = `lesson.php?id=${encodeURIComponent(lesson.id)}`;
          const copy = document.createElement('span');
          copy.className = 'smart-result-copy';
          const meta = document.createElement('span');
          meta.className = 'small text-muted';
          meta.textContent = `${lesson.course_title} / ${lesson.topic}`;
          const title = document.createElement('strong');
          title.textContent = lesson.title;
          const summary = document.createElement('span');
          summary.className = 'small text-muted';
          summary.textContent = lesson.summary;
          copy.append(meta, title, summary);
          const arrow = document.createElement('i');
          arrow.className = 'bi bi-arrow-up-right';
          arrow.setAttribute('aria-hidden', 'true');
          link.append(copy, arrow);
          results.append(link);
        });
      }
    }
  } catch (error) {
    if (results) results.textContent = error.message;
  } finally {
    consent.checked = false;
    if (submit) submit.disabled = false;
  }
});

refreshProgress();