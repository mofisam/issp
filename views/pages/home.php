<?php
// A small rotating icon set so pillar/metric cards don't all look identical.
$pillarIcons = [
    'M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6 M16.5 8.5A2.75 2.75 0 1 0 16.5 3 M19.5 20c0-2.7-1.8-5-4.3-5.7',
    'M12 21s7-6.1 7-11.5A7 7 0 0 0 5 9.5C5 14.9 12 21 12 21Z M12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
    'M3 13.5 10 7l4 3 7-6 M14 4h6v6 M4 21h16',
    'M12 3.5 5 6v5.5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-2.5Z M9.25 12l1.9 1.9L15 10',
    'M4 19V9l8-5 8 5v10 M4 19h16 M9 19v-6h6v6',
    'M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M3.5 7l8.5 6 8.5-6',
    'M5 21V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v16 M15 21v-8h4a1 1 0 0 1 1 1v7',
];
?>
<!-- Hero -->
<?php
$heroSlides = [
    ['img' => 'launch-1.jpg', 'pos' => '50% 25%', 'title' => 'Transparent. Accountable. Results-Driven.',
     'text' => 'Led by EMDI Capacity Development and NINE to build a sustainable, world-class insurance ecosystem.'],
    ['img' => 'launch-3.jpg', 'pos' => '50% 40%', 'title' => "Transforming Nigeria's Insurance Landscape",
     'text' => 'Driving trust, expanding retail access, and pushing penetration of 10% of GDP by 2031.'],
    ['img' => 'launch-4.jpg', 'pos' => '55% 30%', 'title' => 'Unlocking a $2 Trillion Opportunity',
     'text' => 'Reaching 25 million Nigerians and training 6,000 professionals across all 36 states and the FCT.'],
    ['img' => 'launch-2.jpg', 'pos' => '50% 30%', 'title' => 'Driving Inclusive & Ethical Protection',
     'text' => 'Expanding alternative insurance solutions to protect vulnerable communities, MSMEs, women, and youth.'],
];
?>
<style>
    .hero-slide { opacity: 0; transition: opacity 1.2s ease; }
    .hero-slide.is-active { opacity: 1; }
    .hero-slide img { transform: scale(1.02); }
    .hero-slide.is-active img { animation: heroZoom 9s ease-out forwards; }
    @keyframes heroZoom { from { transform: scale(1.02); } to { transform: scale(1.1); } }
    @keyframes heroRise { from { opacity: 0; transform: translateY(26px); } to { opacity: 1; transform: none; } }
    @keyframes heroFade { from { opacity: 0; } to { opacity: 1; } }
    .hero-rise { opacity: 0; animation: heroRise .8s cubic-bezier(.2,.7,.2,1) forwards; }
    .hero-copy { grid-area: 1 / 1; visibility: hidden; }
    .hero-copy.is-active { visibility: visible; }
    .hero-word, .hero-copy .hero-sub { opacity: 0; }
    .hero-copy.is-active .hero-word { display: inline-block; animation: heroRise .8s cubic-bezier(.2,.7,.2,1) forwards; }
    .hero-copy.is-active .hero-sub { animation: heroRise .8s cubic-bezier(.2,.7,.2,1) .75s forwards; }
    .hero-word { display: inline-block; }
    .hero-bar > span { display: block; height: 100%; width: 0; background: #E8841F; }
    .hero-dot.is-active .hero-bar > span { animation: heroBar var(--hero-ms, 6500ms) linear forwards; }
    .hero-paused .hero-dot.is-active .hero-bar > span { animation-play-state: paused; }
    @keyframes heroBar { from { width: 0; } to { width: 100%; } }
    @media (prefers-reduced-motion: reduce) {
        .hero-slide, .hero-slide img, .hero-rise, .hero-word, .hero-copy .hero-sub { animation: none !important; transition: none !important; opacity: 1; transform: none; }
        .hero-slide:not(.is-active) { opacity: 0; }
    }
</style>
<section id="hero" class="relative overflow-hidden bg-issp-green-900 text-white" aria-roledescription="carousel" aria-label="Programme highlights">
    <div class="absolute inset-0" id="hero-slides">
        <?php foreach ($heroSlides as $i => $slide): ?>
            <div class="hero-slide absolute inset-0 <?= $i === 0 ? 'is-active' : '' ?>" role="group" aria-roledescription="slide" aria-label="<?= $i + 1 ?> of <?= count($heroSlides) ?>">
                <img src="<?= asset('img/hero/' . $slide['img']) ?>" alt="" class="h-full w-full object-cover" style="object-position: <?= e($slide['pos']) ?>" <?= $i > 0 ? 'loading="lazy"' : 'fetchpriority="high"' ?>>
            </div>
        <?php endforeach; ?>
        <div class="absolute inset-0 bg-gradient-to-r from-issp-green-900 via-issp-green-900/80 to-issp-green-900/20"></div>
        <div class="absolute inset-0 bg-issp-green-900/40 md:hidden"></div>
        <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-issp-green-900/90 to-transparent"></div>
    </div>

    <div class="relative max-w-6xl mx-auto px-6 pt-24 pb-36 md:pt-32 md:pb-44 min-h-[620px] md:min-h-[680px] flex flex-col justify-center">
        <span class="hero-rise inline-flex w-fit items-center gap-2 bg-white/10 backdrop-blur text-white/90 text-xs font-medium px-3 py-1.5 rounded-full mb-6 border border-white/10" style="animation-delay:.1s">
            <span class="h-1.5 w-1.5 rounded-full bg-issp-orange-500"></span> Insurance Sector Strengthening Program
        </span>
        <div class="grid" id="hero-copy">
            <?php foreach ($heroSlides as $i => $slide): ?>
                <div class="hero-copy <?= $i === 0 ? 'is-active' : '' ?>" <?= $i > 0 ? 'aria-hidden="true"' : '' ?>>
                    <<?= $i === 0 ? 'h1' : 'h2' ?> class="font-serif text-4xl sm:text-5xl md:text-6xl lg:text-7xl leading-[1.07] max-w-3xl">
                        <?php foreach (preg_split('/\s+/', $slide['title']) as $w => $word): ?><span class="hero-word" style="animation-delay:<?= 0.1 + $w * 0.09 ?>s"><?= e($word) ?></span> <?php endforeach; ?>
                    </<?= $i === 0 ? 'h1' : 'h2' ?>>
                    <p class="hero-sub text-lg md:text-xl text-white/80 max-w-xl mt-6"><?= e($slide['text']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="hero-rise flex flex-wrap items-center gap-4 mt-9" style="animation-delay:.9s">
            <a href="/contact" class="inline-flex items-center gap-2 bg-issp-orange-600 hover:bg-issp-orange-700 transition-colors text-white px-6 py-3 rounded-lg font-medium">
                Partner With Us
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14 M13 6l6 6-6 6"/></svg>
            </a>
            <a href="/about" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/15 backdrop-blur transition-colors text-white px-6 py-3 rounded-lg font-medium border border-white/20">Learn more</a>
        </div>
    </div>

    <!-- Controls -->
    <div class="absolute inset-x-0 bottom-0">
        <div class="max-w-6xl mx-auto px-6 pb-7 flex items-end justify-between gap-6">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <?php foreach ($heroSlides as $i => $slide): ?>
                        <button type="button" class="hero-dot group py-2 <?= $i === 0 ? 'is-active' : '' ?>" data-index="<?= $i ?>" aria-label="Go to slide <?= $i + 1 ?>">
                            <span class="hero-bar block h-1 w-10 md:w-14 rounded-full bg-white/30 group-hover:bg-white/50 overflow-hidden"><span></span></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2 flex-shrink-0">
                <button type="button" id="hero-prev" aria-label="Previous slide" class="h-11 w-11 rounded-full border border-white/25 bg-white/10 hover:bg-white/20 backdrop-blur flex items-center justify-center">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <button type="button" id="hero-next" aria-label="Next slide" class="h-11 w-11 rounded-full border border-white/25 bg-white/10 hover:bg-white/20 backdrop-blur flex items-center justify-center">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                </button>
            </div>
        </div>
    </div>
</section>
<script src="<?= asset('js/hero.js') ?>" defer></script>

<!-- Impact & Results -->
<section class="max-w-6xl mx-auto px-6 py-16 md:py-20">
    <div class="text-center mb-12">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Impact &amp; Results</h2>
        <p class="text-gray-500 mt-2 max-w-xl mx-auto">Measurable progress toward a more inclusive, trusted insurance market.</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
        <?php foreach ($metrics as $metric): ?>
            <div class="text-center p-6 bg-white border border-gray-100 rounded-xl shadow-card hover:shadow-md transition-shadow">
                <div class="text-2xl md:text-3xl font-bold text-issp-green-700"><?= e($metric['display_value']) ?></div>
                <div class="text-sm text-gray-600 mt-1.5"><?= e($metric['label']) ?></div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($metrics)): ?>
            <p class="col-span-full text-center text-gray-400 text-sm">Impact figures will appear here once published.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Six Pillars -->
<section class="bg-gray-50 py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Six Pillars of Transformation</h2>
            <p class="text-gray-500 mt-2 max-w-xl mx-auto">The programme's strategic pillars that anchor every intervention.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($pillars as $i => $pillar): ?>
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-card hover:border-issp-green-300 transition-colors">
                    <div class="h-10 w-10 rounded-lg bg-issp-green-50 text-issp-green-700 flex items-center justify-center mb-4">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $pillarIcons[$i % count($pillarIcons)] ?>"/></svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2"><?= e($pillar['title']) ?></h3>
                    <p class="text-sm text-gray-600 leading-relaxed"><?= e($pillar['summary']) ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (empty($pillars)): ?>
                <p class="col-span-full text-center text-gray-400 text-sm">Programme pillars will appear here once published.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Latest News -->
<?php if (!empty($posts)): ?>
<section class="max-w-6xl mx-auto px-6 py-16 md:py-20">
    <div class="flex items-end justify-between mb-12 flex-wrap gap-4">
        <div>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Latest News &amp; Updates</h2>
            <p class="text-gray-500 mt-2">What's happening across the programme.</p>
        </div>
        <a href="/news" class="text-sm font-medium text-issp-green-700 hover:text-issp-green-800 flex items-center gap-1 flex-shrink-0">
            View all news
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14 M13 6l6 6-6 6"/></svg>
        </a>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
        <?php foreach ($posts as $post): ?>
            <a href="/news/<?= e($post['slug']) ?>" class="group block bg-white border border-gray-100 rounded-xl shadow-card overflow-hidden hover:border-issp-green-300 transition-colors">
                <div class="h-40 bg-issp-green-50 overflow-hidden">
                    <?php if (!empty($post['image_url'])): ?>
                        <img src="<?= e($post['image_url']) ?>" alt="<?= e($post['title']) ?>" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <?php else: ?>
                        <div class="h-full w-full flex items-center justify-center text-issp-green-200">
                            <svg viewBox="0 0 24 24" class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h13a2 2 0 0 1 2 2v12a1 1 0 0 1-1 1H6a2 2 0 0 1-2-2V5Z M20 9H17a1 1 0 0 0-1 1v10 M7 9h6 M7 12.5h6 M7 16h4"/></svg>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-5">
                    <div class="text-xs text-issp-orange-600 font-medium"><?= format_date($post['published_at']) ?></div>
                    <h3 class="font-semibold text-gray-900 mt-1.5 group-hover:text-issp-green-700 transition-colors"><?= e($post['title']) ?></h3>
                    <?php if (!empty($post['excerpt'])): ?>
                        <p class="text-sm text-gray-600 mt-2 line-clamp-2"><?= e($post['excerpt']) ?></p>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Upcoming Events -->
<section class="bg-gray-50 py-16 md:py-20">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex items-end justify-between mb-12 flex-wrap gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Upcoming Events</h2>
                <p class="text-gray-500 mt-2">Join us at our next programme activities.</p>
            </div>
            <a href="/events" class="text-sm font-medium text-issp-green-700 hover:text-issp-green-800 flex items-center gap-1 flex-shrink-0">
                View all events
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14 M13 6l6 6-6 6"/></svg>
            </a>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <?php foreach ($events as $event): ?>
                <a href="/events/<?= e($event['slug']) ?>" class="group flex items-start gap-4 p-6 bg-white border border-gray-100 rounded-xl shadow-card hover:border-issp-green-300 transition-colors">
                    <div class="flex-shrink-0 w-14 text-center bg-issp-green-50 rounded-lg py-2">
                        <div class="text-[10px] font-semibold text-issp-green-700 uppercase"><?= e(date('M', strtotime($event['starts_at']))) ?></div>
                        <div class="text-xl font-bold text-issp-green-800 leading-none mt-0.5"><?= e(date('d', strtotime($event['starts_at']))) ?></div>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs text-issp-orange-600 font-medium"><?= format_date($event['starts_at']) ?></div>
                        <div class="font-semibold mt-1 text-gray-900 group-hover:text-issp-green-700 transition-colors truncate"><?= e($event['title']) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if (empty($events)): ?>
                <p class="col-span-full text-center text-gray-400 text-sm">No upcoming events yet &mdash; check back soon.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Partners -->
<?php if (!empty($partners)): ?>
<section class="max-w-6xl mx-auto px-6 py-16 md:py-20">
    <div class="text-center mb-12">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Our Partners</h2>
        <p class="text-gray-500 mt-2 max-w-xl mx-auto">Working alongside donors, regulators, and institutional partners.</p>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 items-center">
        <?php foreach ($partners as $partner): ?>
            <a href="/partners" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:bg-gray-50 transition-colors" title="<?= e($partner['name']) ?>">
                <div class="h-16 w-full flex items-center justify-center bg-gray-50 rounded-lg">
                    <?php if (!empty($partner['logo_url'])): ?>
                        <img src="<?= e($partner['logo_url']) ?>" alt="<?= e($partner['name']) ?>" class="h-10 max-w-[80%] object-contain">
                    <?php else: ?>
                        <span class="text-xs text-gray-400">Logo</span>
                    <?php endif; ?>
                </div>
                <div class="text-xs text-gray-600 text-center"><?= e($partner['name']) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- FAQ -->
<section class="bg-issp-green-800 text-white py-16 md:py-20">
    <div class="max-w-4xl mx-auto px-6">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-bold">Frequently Asked Questions</h2>
            <p class="text-white/70 mt-2">Answers to common questions about the programme.</p>
        </div>
        <div class="space-y-3">
            <?php foreach ($faqs as $faq): ?>
                <details class="bg-white/5 rounded-lg p-4 group">
                    <summary class="font-medium cursor-pointer list-none flex items-center justify-between gap-4">
                        <?= e($faq['question']) ?>
                        <svg viewBox="0 0 24 24" class="h-4 w-4 flex-shrink-0 transition-transform group-open:rotate-45" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14 M5 12h14"/></svg>
                    </summary>
                    <p class="text-sm text-white/80 mt-3 leading-relaxed"><?= e($faq['answer']) ?></p>
                </details>
            <?php endforeach; ?>
            <?php if (empty($faqs)): ?>
                <p class="text-center text-white/60 text-sm">FAQs will appear here once published.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="max-w-6xl mx-auto px-6 py-16 md:py-20 text-center">
    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">Ready to work with us?</h2>
    <p class="text-gray-600 max-w-xl mx-auto mb-8">Whether you're a donor, regulator, insurer, or implementing partner, there's a role for you in strengthening Nigeria's insurance sector.</p>
    <a href="/contact" class="inline-flex items-center gap-2 bg-issp-orange-600 hover:bg-issp-orange-700 transition-colors text-white px-6 py-3 rounded-lg font-medium">
        Get in touch
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14 M13 6l6 6-6 6"/></svg>
    </a>
</section>
