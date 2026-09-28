document.getElementById('adminSidebarToggle')?.addEventListener('click', () => {
  const sidebar = document.getElementById('sbSidenav');
  const isMobile = window.matchMedia('(max-width: 767.98px)').matches;
  const isOpen = isMobile
    ? document.body.classList.contains('admin-sidebar-open')
    : !sidebar?.classList.contains('toggled');
  const shouldOpen = !isOpen;

  document.body.classList.toggle('admin-sidebar-open', isMobile && shouldOpen);
  if (window.matchMedia('(max-width: 767.98px)').matches) {
    sidebar?.classList.toggle('mobile-open', shouldOpen);
  } else {
    sidebar?.classList.toggle('toggled', !shouldOpen);
  }
  document.getElementById('adminSidebarToggle')?.setAttribute('aria-expanded', String(shouldOpen));
  sidebar?.setAttribute('aria-hidden', String(!shouldOpen));
});

document.querySelectorAll('[data-admin-sidebar-close]').forEach((button) => {
  button.addEventListener('click', () => {
    document.body.classList.remove('admin-sidebar-open');
    document.getElementById('sbSidenav')?.classList.remove('mobile-open');
    document.getElementById('adminSidebarToggle')?.setAttribute('aria-expanded', 'false');
    document.getElementById('sbSidenav')?.setAttribute('aria-hidden', 'true');
  });
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && document.body.classList.contains('admin-sidebar-open')) {
    document.querySelector('[data-admin-sidebar-close]')?.click();
  }
});

document.querySelector('[data-generate-draft]')?.addEventListener('click', async (event) => {
  const button = event.currentTarget;
  const form = button.closest('form');
  const consent = form?.querySelector('[data-ai-draft-consent]');
  const status = form?.querySelector('[data-ai-draft-status]');
  const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  const courseId = form?.elements.course_id?.value ?? '';
  const topic = form?.elements.topic?.value?.trim() ?? '';

  if (!courseId || !topic) {
    if (status) status.textContent = 'Choose a course and enter the topic first.';
    return;
  }
  if (!consent?.checked) {
    if (status) status.textContent = 'Confirm the AI data-sharing notice first.';
    return;
  }
  if (!window.confirm('This sends the selected course and topic to OpenAI to generate a lesson and quiz draft. Continue?')) return;

  const data = new FormData();
  data.set('mode', 'draft');
  data.set('consent', '1');
  data.set('course_id', courseId);
  data.set('topic', topic);
  button.disabled = true;
  if (status) status.textContent = 'Generating a draft for review…';

  try {
    const response = await fetch('../api/ai.php', {
      method: 'POST',
      headers: { 'X-CSRF-Token': token },
      body: data,
      credentials: 'same-origin',
    });
    const payload = await response.json();
    if (!response.ok) throw new Error(payload.error ?? 'Draft generation failed.');

    const draft = payload.draft;
    for (const field of ['title', 'summary', 'body', 'code_sample', 'quiz_question', 'option_a', 'option_b', 'option_c', 'correct_option', 'quiz_explanation']) {
      if (form.elements[field] && typeof draft[field] === 'string') form.elements[field].value = draft[field];
    }
    if (form.elements.is_published) form.elements.is_published.checked = false;
    if (status) status.textContent = 'Draft added to the form. Review every field before saving.';
  } catch (error) {
    if (status) status.textContent = error.message;
  } finally {
    consent.checked = false;
    button.disabled = false;
  }
});
