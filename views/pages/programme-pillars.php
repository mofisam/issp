<?php
// Same rotating icon set as the homepage pillar cards, for visual continuity.
$pillarIcons = [
    'M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6 M16.5 8.5A2.75 2.75 0 1 0 16.5 3 M19.5 20c0-2.7-1.8-5-4.3-5.7',
    'M12 21s7-6.1 7-11.5A7 7 0 0 0 5 9.5C5 14.9 12 21 12 21Z M12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
    'M3 13.5 10 7l4 3 7-6 M14 4h6v6 M4 21h16',
    'M12 3.5 5 6v5.5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-2.5Z M9.25 12l1.9 1.9L15 10',
    'M4 19V9l8-5 8 5v10 M4 19h16 M9 19v-6h6v6',
    'M5 21V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v16 M15 21v-8h4a1 1 0 0 1 1 1v7',
];
?>
<!-- Hero -->
<section class="bg-issp-green-900 text-white">
    <div class="max-w-5xl mx-auto px-6 pt-20 pb-16 md:pt-28 md:pb-20">
        <h1 class="font-serif text-4xl md:text-6xl leading-[1.08] max-w-3xl">Six Pillars of Transformation</h1>
        <p class="mt-6 text-lg text-white/75 max-w-xl leading-relaxed">The ISSP's framework for sector-wide change &mdash; from policy and public awareness through to capacity, inclusion, innovation and MSME reach.</p>
    </div>
</section>

<!-- Pillars -->
<section>
    <?php foreach ($pillars as $i => $pillar): ?>
        <?php $tint = $i % 2 === 1 ? 'bg-gray-50' : 'bg-white'; ?>
        <div class="<?= $tint ?> border-b border-gray-100">
            <div class="max-w-5xl mx-auto px-6 py-14 md:py-16 grid md:grid-cols-[1fr,1.4fr] gap-8 md:gap-16">
                <div>
                    <span class="inline-flex items-center gap-2 text-xs font-medium text-issp-orange-700 bg-issp-orange-50 px-3 py-1.5 rounded-full">
                        Pillar <?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?>
                    </span>
                    <h2 class="font-serif text-2xl md:text-3xl text-gray-900 mt-4 leading-snug"><?= e($pillar['title']) ?></h2>
                    <div class="h-10 w-10 rounded-lg bg-issp-green-50 text-issp-green-700 flex items-center justify-center mt-5">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $pillarIcons[$i % count($pillarIcons)] ?>"/></svg>
                    </div>
                </div>
                <div>
                    <p class="text-gray-600 leading-relaxed max-w-xl"><?= e($pillar['summary']) ?></p>
                    <?php if (!empty($pillar['body_content'])): ?>
                        <div class="mt-6 text-sm text-gray-600 leading-relaxed [&_ul]:space-y-2.5 [&_li]:pl-5 [&_li]:relative [&_li]:before:content-[''] [&_li]:before:absolute [&_li]:before:left-0 [&_li]:before:top-[0.6em] [&_li]:before:h-1.5 [&_li]:before:w-1.5 [&_li]:before:rounded-full [&_li]:before:bg-issp-orange-400">
                            <?= $pillar['body_content'] ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($pillars)): ?>
        <div class="max-w-5xl mx-auto px-6 py-20 text-center">
            <p class="text-gray-400">The programme pillars will appear here once published.</p>
        </div>
    <?php endif; ?>
</section>

<!-- CTA -->
<section class="max-w-5xl mx-auto px-6 py-16 md:py-24">
    <div class="bg-issp-green-50 rounded-2xl px-8 py-12 md:px-14 md:py-16 flex flex-wrap items-center justify-between gap-8">
        <div class="max-w-md">
            <h2 class="font-serif text-2xl md:text-3xl text-gray-900">See how it all fits together</h2>
            <p class="mt-3 text-gray-600 leading-relaxed">Read more about the programme's goals, targets and the case for action.</p>
        </div>
        <a href="/about" class="inline-flex items-center gap-2 bg-issp-orange-600 hover:bg-issp-orange-700 transition-colors text-white px-6 py-3 rounded-lg font-medium flex-shrink-0">
            About the ISSP
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14 M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</section>
