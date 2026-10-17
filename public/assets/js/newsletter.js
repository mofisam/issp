// AJAX newsletter signup (footer form). Expects an element with id="newsletter-form".
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('newsletter-form');
  if (!form) return;

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var status = document.getElementById('newsletter-status');
    var data = new FormData(form);

    fetch('/newsletter/subscribe', { method: 'POST', body: data, headers: { 'X-Requested-With': 'fetch' } })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        status.textContent = json.message;
        status.className = json.success ? 'text-issp-green-600 text-sm mt-2' : 'text-red-600 text-sm mt-2';
        if (json.success) form.reset();
      })
      .catch(function () {
        status.textContent = 'Something went wrong. Please try again.';
        status.className = 'text-red-600 text-sm mt-2';
      });
  });
});
