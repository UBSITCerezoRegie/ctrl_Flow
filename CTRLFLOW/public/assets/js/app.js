// app.js — lightweight shared behaviors for Ctrl+Flow

// Debounce helper
function debounce(fn, delay = 250) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), delay);
  };
}

document.addEventListener('DOMContentLoaded', () => {

  // Live email-availability check on any input[name="email"] with a sibling
  // #email-status element (used on the admin "create citizen" form).
  const emailInput = document.querySelector('input[name="email"]');
  const emailStatus = document.getElementById('email-status');

  if (emailInput && emailStatus) {
    emailInput.addEventListener('input', debounce(async () => {
      const email = emailInput.value.trim();
      if (!email) { emailStatus.innerHTML = ''; return; }

      try {
        const res = await fetch('/CTRLFLOW/public/?route=ajax/check_email&email=' + encodeURIComponent(email));
        const data = await res.json();
        emailStatus.innerHTML = data.valid
          ? `<span class="text-success">${data.message}</span>`
          : `<span class="text-danger">${data.message}</span>`;
      } catch (e) {
        emailStatus.innerHTML = '';
      }
    }, 300));
  }

  // Password confirm match helper, wherever both fields are present
  const pw = document.querySelector('input[name="password"]');
  const confirm = document.querySelector('input[name="confirm_password"]');
  if (pw && confirm) {
    const check = () => {
      confirm.setCustomValidity(
        confirm.value && confirm.value !== pw.value ? 'Passwords do not match' : ''
      );
    };
    pw.addEventListener('input', check);
    confirm.addEventListener('input', check);
  }
});
