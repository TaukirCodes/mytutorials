const passwordInput = document.getElementById('adminPassword');
const passwordToggle = document.querySelector('[data-password-toggle]');

passwordToggle?.addEventListener('click', () => {
  if (!passwordInput) return;

  const shouldShow = passwordInput.type === 'password';
  passwordInput.type = shouldShow ? 'text' : 'password';
  passwordToggle.setAttribute('aria-pressed', String(shouldShow));
  passwordToggle.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
  passwordToggle.querySelector('span').textContent = shouldShow ? 'Hide' : 'Show';
  passwordToggle.querySelector('i').className = shouldShow ? 'bi bi-eye-slash' : 'bi bi-eye';
});

document.getElementById('adminLoginForm')?.addEventListener('submit', (event) => {
  const form = event.currentTarget;
  if (!form.checkValidity()) return;

  const submitButton = document.getElementById('adminLoginSubmit');
  if (!submitButton || submitButton.disabled) {
    event.preventDefault();
    return;
  }

  submitButton.disabled = true;
  submitButton.setAttribute('aria-busy', 'true');
  submitButton.querySelector('.admin-auth-submit-label').textContent = 'Signing in...';
});
