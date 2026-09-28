(() => {
  const storageKey = 'skillnovi-theme';
  const root = document.documentElement;
  const preference = window.matchMedia?.('(prefers-color-scheme: dark)');
  const readSavedTheme = () => {
    try {
      const saved = window.localStorage.getItem(storageKey);
      return saved === 'light' || saved === 'dark' ? saved : null;
    } catch {
      return null;
    }
  };

  const savedTheme = readSavedTheme();
  let theme = savedTheme ?? (preference?.matches ? 'dark' : 'light');

  const applyTheme = () => {
    const isDark = theme === 'dark';
    root.dataset.theme = theme;
    root.style.colorScheme = theme;

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      const action = isDark ? 'Switch to light theme' : 'Switch to dark theme';
      button.setAttribute('aria-label', action);
      button.setAttribute('title', action);
      button.setAttribute('aria-pressed', String(isDark));
      const icon = button.querySelector('[data-theme-icon]');
      if (icon) icon.className = `bi ${isDark ? 'bi-sun' : 'bi-moon-stars'}`;
      const label = button.querySelector('[data-theme-label]');
      if (label) label.textContent = action;
    });
  };

  applyTheme();
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', applyTheme, { once: true });
  }

  document.addEventListener('click', (event) => {
    const button = event.target instanceof Element ? event.target.closest('[data-theme-toggle]') : null;
    if (!button) return;

    theme = theme === 'dark' ? 'light' : 'dark';
    try {
      window.localStorage.setItem(storageKey, theme);
    } catch {
      // The selected theme still applies when storage is unavailable.
    }
    applyTheme();
  });

  preference?.addEventListener?.('change', (event) => {
    if (readSavedTheme() !== null) return;
    theme = event.matches ? 'dark' : 'light';
    applyTheme();
  });
})();
