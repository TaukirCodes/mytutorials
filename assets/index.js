
const progressKey = 'devdocs.progress.v1';
const bookmarksKey = 'devdocs.bookmarks.v1';
const lastVisitedKey = 'devdocs.last-visited.v1';
const localeKey = 'devdocs.locale.v1';
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
let showSavedOnly = false;

const translations = {
  en: {
    'nav.explorer': 'Explore', 'nav.courses': 'Learning paths', 'nav.admin': 'Admin portal',
    'nav.language': 'Language', 'course.open': 'Open path',
    'home.kicker': 'DEV DOCS / LEARNING LIBRARY', 'home.title': 'Your developer learning space',
    'home.description': 'Pick up where you left off, or choose a new skill to explore.',
    'home.continue.kicker': 'PICK UP WHERE YOU LEFT OFF', 'home.continue.title': 'Continue learning',
    'home.start.kicker': 'A GOOD PLACE TO START', 'home.start.title': 'Choose a path. Build a real skill.',
    'home.start.description': 'Follow focused lessons, practice what you learn, and grow at your own pace.',
    'home.start.action': 'Explore learning paths', 'home.paths.kicker': 'LEARNING PATHS',
    'home.paths.title': 'Choose what you want to build', 'home.paths.note': 'Free to start · Learn at your pace',
    'home.search.kicker': 'SMART DISCOVERY', 'home.search.title': 'Describe what you want to learn',
    'home.search.placeholder': 'For example: how do I loop through a list?',
    'home.search.action': 'Find lessons', 'home.search.consent': 'I confirm this query may be sent to OpenAI.',
    'home.lesson.kicker': 'KEEP GOING', 'home.lesson.title': 'Lessons', 'home.saved': 'Saved lessons',
    'home.saved.show': 'Show all lessons', 'home.saved.filter': 'Saved lessons',
    'lesson.mark': 'Mark complete', 'lesson.save': 'Save lesson', 'lesson.code': 'Code example',
    'lesson.copy': 'Copy code', 'lesson.quiz.kicker': 'QUICK CHECK', 'lesson.quiz.title': 'Check your understanding',
    'lesson.quiz.submit': 'Check answer', 'lesson.ai.kicker': 'LEARN WITH CONTEXT', 'lesson.ai.title': 'Ask the AI tutor',
    'lesson.ai.description': 'Your question, this lesson, and any code you add below will be sent to OpenAI only after you confirm this request.',
    'lesson.ai.mode': 'What do you want help with?', 'lesson.ai.explain': 'Explain this lesson',
    'lesson.ai.debug': 'Review my code', 'lesson.ai.question': 'Question', 'lesson.ai.question.placeholder': 'Ask a focused question about this lesson',
    'lesson.ai.code': 'Your code (optional)', 'lesson.ai.code.placeholder': 'Paste only code you are comfortable sharing with OpenAI',
    'lesson.ai.consent': 'I confirm that this question and lesson context may be sent to OpenAI for this request.',
    'lesson.ai.submit': 'Ask tutor',
    'home.language.notice': 'Interface language changed. Lesson content remains in its author-provided language.',
    'stats.paths': 'paths', 'stats.lessons': 'lessons', 'stats.results': 'results',
    'stats.completed': '{count} completed in this browser', 'lesson.start': 'Start lesson', 'lesson.done': 'Completed',
  },
  hi: {
    'nav.explorer': 'सीखें', 'nav.courses': 'लर्निंग पाथ', 'nav.admin': 'एडमिन पोर्टल',
    'nav.language': 'भाषा', 'course.open': 'पाथ खोलें',
    'home.kicker': 'DEV DOCS / लर्निंग लाइब्रेरी', 'home.title': 'आपकी डेवलपर लर्निंग स्पेस',
    'home.description': 'जहाँ छोड़ा था वहीं से शुरू करें, या कोई नई स्किल चुनें।',
    'home.continue.kicker': 'यहीं से आगे बढ़ें', 'home.continue.title': 'सीखना जारी रखें',
    'home.start.kicker': 'यहाँ से शुरू करें', 'home.start.title': 'एक पाथ चुनें। काम की स्किल बनाएँ।',
    'home.start.description': 'छोटे lessons करें, सीखी हुई चीज़ों की practice करें और अपनी गति से आगे बढ़ें।',
    'home.start.action': 'लर्निंग पाथ देखें', 'home.paths.kicker': 'लर्निंग पाथ',
    'home.paths.title': 'आप क्या बनाना सीखना चाहते हैं?', 'home.paths.note': 'शुरुआत मुफ़्त · अपनी गति से सीखें',
    'home.search.kicker': 'स्मार्ट खोज', 'home.search.title': 'बताएँ कि आप क्या सीखना चाहते हैं',
    'home.search.placeholder': 'उदाहरण: list के items पर loop कैसे चलाएँ?',
    'home.search.action': 'Lessons खोजें', 'home.search.consent': 'मैं सहमत हूँ कि यह query इस request के लिए OpenAI को भेजी जा सकती है।',
    'home.lesson.kicker': 'आगे सीखें', 'home.lesson.title': 'Lessons', 'home.saved': 'सेव किए lessons',
    'home.saved.show': 'सभी lessons दिखाएँ', 'home.saved.filter': 'सेव किए lessons',
    'lesson.mark': 'पूरा मार्क करें', 'lesson.save': 'Lesson सेव करें', 'lesson.code': 'Code उदाहरण',
    'lesson.copy': 'Code कॉपी करें', 'lesson.quiz.kicker': 'छोटी जाँच', 'lesson.quiz.title': 'समझ को जाँचें',
    'lesson.quiz.submit': 'जवाब जाँचें', 'lesson.ai.kicker': 'पाठ के संदर्भ में सीखें', 'lesson.ai.title': 'AI tutor से पूछें',
    'lesson.ai.description': 'आपकी पुष्टि के बाद ही आपका सवाल, यह lesson और दिया गया code OpenAI को भेजा जाएगा।',
    'lesson.ai.mode': 'आप किस चीज़ में मदद चाहते हैं?', 'lesson.ai.explain': 'यह lesson समझाएँ',
    'lesson.ai.debug': 'मेरे code की जाँच करें', 'lesson.ai.question': 'सवाल', 'lesson.ai.question.placeholder': 'इस lesson के बारे में साफ़ सवाल पूछें',
    'lesson.ai.code': 'आपका code (ज़रूरी नहीं)', 'lesson.ai.code.placeholder': 'सिर्फ वही code डालें जिसे OpenAI के साथ साझा करना ठीक हो',
    'lesson.ai.consent': 'मैं पुष्टि करता हूँ कि यह सवाल और lesson का संदर्भ इस request के लिए OpenAI को भेजा जा सकता है।',
    'lesson.ai.submit': 'Tutor से पूछें',
    'home.language.notice': 'Interface की भाषा बदली गई है। Lesson का content लेखक की चुनी हुई भाषा में ही रहेगा।',
    'stats.paths': 'पाथ', 'stats.lessons': 'lessons', 'stats.results': 'नतीजे',
    'stats.completed': 'इस browser में {count} पूरे किए', 'lesson.start': 'Lesson शुरू करें', 'lesson.done': 'पूरा हुआ',
  },
  hinglish: {
    'nav.explorer': 'Explore karo', 'nav.courses': 'Learning paths', 'nav.admin': 'Admin portal',
    'nav.language': 'Language', 'course.open': 'Path kholein',
    'home.kicker': 'DEV DOCS / LEARNING LIBRARY', 'home.title': 'Aapki developer learning space',
    'home.description': 'Jahan chhoda tha wahan se continue karein, ya nayi skill explore karein.',
    'home.continue.kicker': 'YAHIN SE AAGE BADHEIN', 'home.continue.title': 'Learning continue karein',
    'home.start.kicker': 'YAHAN SE SHURU KAREIN', 'home.start.title': 'Ek path choose karein. Real skill build karein.',
    'home.start.description': 'Focused lessons follow karein, seekhi hui cheez practice karein, aur apni pace se grow karein.',
    'home.start.action': 'Learning paths dekhein', 'home.paths.kicker': 'LEARNING PATHS',
    'home.paths.title': 'Aap kya build karna seekhna chahte hain?', 'home.paths.note': 'Start free · Apni pace se seekhein',
    'home.search.kicker': 'SMART DISCOVERY', 'home.search.title': 'Batayein aap kya seekhna chahte hain',
    'home.search.placeholder': 'Example: list ke items par loop kaise chalate hain?',
    'home.search.action': 'Lessons dhoondein', 'home.search.consent': 'Main confirm karta hoon ki yeh query is request ke liye OpenAI ko bheji ja sakti hai.',
    'home.lesson.kicker': 'AAPKA NEXT STEP', 'home.lesson.title': 'Lessons', 'home.saved': 'Saved lessons',
    'home.saved.show': 'Saare lessons dekhein', 'home.saved.filter': 'Saved lessons',
    'lesson.mark': 'Complete mark karein', 'lesson.save': 'Lesson save karein', 'lesson.code': 'Code example',
    'lesson.copy': 'Code copy karein', 'lesson.quiz.kicker': 'QUICK CHECK', 'lesson.quiz.title': 'Samajh check karein',
    'lesson.quiz.submit': 'Answer check karein', 'lesson.ai.kicker': 'CONTEXT KE SAATH SEEKHEIN', 'lesson.ai.title': 'AI tutor se poochhein',
    'lesson.ai.description': 'Aapki confirmation ke baad hi aapka question, yeh lesson aur diya gaya code OpenAI ko bheja jayega.',
    'lesson.ai.mode': 'Kis cheez mein help chahiye?', 'lesson.ai.explain': 'Yeh lesson samjhein',
    'lesson.ai.debug': 'Mera code review karein', 'lesson.ai.question': 'Question', 'lesson.ai.question.placeholder': 'Is lesson ke baare mein focused question poochhein',
    'lesson.ai.code': 'Aapka code (optional)', 'lesson.ai.code.placeholder': 'Sirf wahi code paste karein jo OpenAI ke saath share karna theek ho',
    'lesson.ai.consent': 'Main confirm karta hoon ki yeh question aur lesson context is request ke liye OpenAI ko bheja ja sakta hai.',
    'lesson.ai.submit': 'Tutor se poochhein',
    'home.language.notice': 'Interface language badli gayi hai. Lesson content author ki language mein hi rahega.',
    'stats.paths': 'paths', 'stats.lessons': 'lessons', 'stats.results': 'results',
    'stats.completed': 'Is browser mein {count} complete hue', 'lesson.start': 'Lesson shuru karein', 'lesson.done': 'Complete',
  },
};

function getInitialLocale() {
  try {
    const saved = localStorage.getItem(localeKey);
    if (saved && translations[saved]) return saved;
  } catch {
    return navigator.language?.toLowerCase().startsWith('hi') ? 'hi' : 'en';
  }
  return navigator.language?.toLowerCase().startsWith('hi') ? 'hi' : 'en';
}

let currentLocale = getInitialLocale();

function translate(key) {
  return translations[currentLocale]?.[key] ?? translations.en[key] ?? key;
}

function applyLocale(locale, persist = true) {
  currentLocale = translations[locale] ? locale : 'en';
  document.documentElement.lang = currentLocale === 'hi' ? 'hi' : currentLocale === 'hinglish' ? 'hi-Latn' : 'en';
  document.querySelectorAll('[data-i18n]').forEach((element) => {
    element.textContent = translate(element.dataset.i18n);
  });
  document.querySelectorAll('[data-i18n-placeholder]').forEach((element) => {
    element.setAttribute('placeholder', translate(element.dataset.i18nPlaceholder));
  });

  const picker = document.querySelector('[data-language-picker]');
  if (picker) picker.value = currentLocale;
  const notice = document.querySelector('[data-content-language-note]');
  if (notice) {
    notice.hidden = currentLocale === 'en';
    notice.textContent = currentLocale === 'en' ? '' : translate('home.language.notice');
  }
  if (persist) {
    try {
      localStorage.setItem(localeKey, currentLocale);
    } catch {
      return;
    }
  }
}

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
  let lastVisited = '';
  try {
    lastVisited = localStorage.getItem(lastVisitedKey) ?? '';
  } catch {
    lastVisited = '';
  }
  document.querySelectorAll('[data-lesson-row]').forEach((row) => {
    const state = row.querySelector('[data-progress-state]');
    if (state) state.textContent = completed.includes(row.dataset.lessonId) ? translate('lesson.done') : translate('lesson.start');
    const bookmark = row.querySelector('[data-bookmark-state]');
    if (bookmark) bookmark.textContent = bookmarks.includes(row.dataset.lessonId) ? 'Saved' : '';
    row.hidden = showSavedOnly && !bookmarks.includes(row.dataset.lessonId);
  });

  const summary = document.querySelector('[data-progress-summary]');
  if (summary) summary.textContent = translate('stats.completed').replace('{count}', completed.length);

  const recommendation = document.querySelector('[data-next-lesson]');
  const recommendationLink = recommendation?.querySelector('[data-next-lesson-link]');
  const newLearner = document.querySelector('[data-new-learner]');
  const rows = Array.from(document.querySelectorAll('[data-lesson-row]'));
  const lastVisitedRow = rows.find((row) => row.dataset.lessonId === lastVisited);
  const nextRow = lastVisitedRow ?? rows.find((row) => !completed.includes(row.dataset.lessonId));
  const needsFirstPath = !lastVisited && completed.length === 0;
  if (recommendation && recommendationLink && nextRow && !needsFirstPath) {
    recommendation.hidden = false;
    if (newLearner) newLearner.hidden = true;
    recommendationLink.href = nextRow.href;
    const lessonTitle = nextRow.querySelector('strong')?.textContent ?? translate('home.continue.title');
    const title = recommendationLink.querySelector('[data-next-lesson-title]');
    if (title) title.textContent = `${translate('home.continue.title')}: ${lessonTitle}`;
    const meta = recommendation.querySelector('[data-next-lesson-meta]');
    if (meta) meta.textContent = nextRow.querySelector('.lesson-row-copy > span:first-child')?.textContent ?? 'Picked for your next step.';
  } else if (recommendation) {
    recommendation.hidden = true;
    if (newLearner) newLearner.hidden = !needsFirstPath;
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
  event.currentTarget.textContent = showSavedOnly ? translate('home.saved.show') : translate('home.saved.filter');
  refreshProgress();
});

document.querySelector('[data-language-picker]')?.addEventListener('change', (event) => {
  applyLocale(event.currentTarget.value);
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

const currentLessonId = document.body.dataset.currentLessonId;
if (currentLessonId) {
  try {
    localStorage.setItem(lastVisitedKey, currentLessonId);
  } catch {
    // Learning pages remain usable if browser storage is unavailable.
  }
}

applyLocale(currentLocale, false);
refreshProgress();