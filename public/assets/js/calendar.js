// Lightweight month filter for the event calendar list view.
document.addEventListener('DOMContentLoaded', function () {
  var select = document.getElementById('event-month-filter');
  if (!select) return;

  select.addEventListener('change', function () {
    var month = this.value;
    document.querySelectorAll('[data-event-month]').forEach(function (row) {
      row.style.display = (!month || row.dataset.eventMonth === month) ? '' : 'none';
    });
  });
});
