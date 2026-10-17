// Global site behaviour: mobile nav toggle + flash message auto-dismiss.
document.addEventListener('DOMContentLoaded', function () {
  var navToggle = document.getElementById('nav-toggle');
  var mobileMenu = document.getElementById('mobile-menu');
  if (navToggle && mobileMenu) {
    navToggle.addEventListener('click', function () {
      mobileMenu.classList.toggle('hidden');
    });
  }

  document.querySelectorAll('[data-flash]').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity .4s';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 400);
    }, 4000);
  });
});
