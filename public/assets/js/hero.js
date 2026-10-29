// Home hero slider: autoplay, dots with progress, arrows, swipe, pause on hover/focus/hidden tab.
(function () {
    var root = document.getElementById('hero');
    if (!root) return;
    var slides = root.querySelectorAll('.hero-slide');
    var dots = root.querySelectorAll('.hero-dot');
    var copies = root.querySelectorAll('.hero-copy');
    var DELAY = 6500;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var current = 0, timer = null;
    root.style.setProperty('--hero-ms', DELAY + 'ms');

    function show(i) {
        current = (i + slides.length) % slides.length;
        slides.forEach(function (s, n) { s.classList.toggle('is-active', n === current); });
        dots.forEach(function (d, n) {
            d.classList.remove('is-active');
            if (n === current) { void d.offsetWidth; d.classList.add('is-active'); } // restart progress bar
            d.setAttribute('aria-current', n === current ? 'true' : 'false');
        });
        copies.forEach(function (c, n) {
            var on = n === current;
            if (on && !c.classList.contains('is-active')) { void c.offsetWidth; }
            c.classList.toggle('is-active', on);
            c.setAttribute('aria-hidden', on ? 'false' : 'true');
        });
    }
    function stop() { clearTimeout(timer); timer = null; }
    function start() {
        stop();
        if (reduce || slides.length < 2) return;
        timer = setTimeout(function () { show(current + 1); start(); }, DELAY);
    }
    function go(i) { show(i); start(); }

    dots.forEach(function (d) { d.addEventListener('click', function () { go(parseInt(d.dataset.index, 10)); }); });
    var prev = document.getElementById('hero-prev'), next = document.getElementById('hero-next');
    if (prev) prev.addEventListener('click', function () { go(current - 1); });
    if (next) next.addEventListener('click', function () { go(current + 1); });

    function pause() { root.classList.add('hero-paused'); stop(); }
    function resume() { root.classList.remove('hero-paused'); show(current); start(); }
    root.addEventListener('mouseenter', pause);
    root.addEventListener('mouseleave', resume);
    document.addEventListener('visibilitychange', function () { document.hidden ? stop() : start(); });

    var x0 = null;
    root.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    root.addEventListener('touchend', function (e) {
        if (x0 === null) return;
        var dx = e.changedTouches[0].clientX - x0;
        if (Math.abs(dx) > 50) go(current + (dx < 0 ? 1 : -1));
        x0 = null;
    });
    root.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') go(current - 1);
        if (e.key === 'ArrowRight') go(current + 1);
    });

    show(0);
    start();
})();
