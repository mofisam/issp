// Toggle password visibility on the stakeholder/admin login form.
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.getElementById('toggle-password');
  var input = document.getElementById('password');
  if (!toggle || !input) return;

  toggle.addEventListener('click', function () {
    input.type = input.type === 'password' ? 'text' : 'password';
  });
});
