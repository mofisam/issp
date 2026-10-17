// Basic client-side validation for the contact form before it submits normally.
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('contact-form');
  if (!form) return;

  form.addEventListener('submit', function (e) {
    var required = form.querySelectorAll('[required]');
    var valid = true;
    required.forEach(function (field) {
      if (!field.value.trim()) {
        valid = false;
        field.classList.add('border-red-500');
      } else {
        field.classList.remove('border-red-500');
      }
    });
    if (!valid) e.preventDefault();
  });
});
