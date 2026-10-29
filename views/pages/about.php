<?php
// The "Case for Action" figures below are the brochure's own gap-analysis
// numbers (p.12) - a fixed, citeable dataset rather than CMS content, so
// they're kept here the same way other presentational arrays (status
// colours, icon paths, etc.) live directly in their views across this
// codebase. `value` drives the bar height; `display` is what's printed.
$caseForAction = [
    ['label' => 'Insurance penetration (% of GDP)',        'value' => 0.5,  'display' => '0.5%'],
    ['label' => 'Continental average (% of GDP)',          'value' => 3.2,  'display' => '3.2%'],
    ['label' => 'MSMEs with any insurance coverage',       'value' => 8,    'display' => '8%'],
    ['label' => 'Customers aged 18-35',                     'value' => 20,   'display' => '<20%'],
    ['label' => "Women's share of policyholders",          'value' => 32,   'display' => '32%'],
    ['label' => 'Nigerians lacking basic insurance understanding', 'value' => 78, 'display' => '78%'],
];
$chartMax = max(array_column($caseForAction, 'value'));
?>
<!-- Hero -->
<section class="bg-issp-green-900 text-white">
    <div class="max-w-5xl mx-auto px-6 pt-20 pb-16 md:pt-28 md:pb-20">
        <h1 class="font-serif text-4xl md:text-6xl leading-[1.08] max-w-3xl">A stronger, more inclusive insurance sector for Nigeria.</h1>
        <p class="mt-6 text-lg text-white/75 max-w-xl leading-relaxed">The Insurance Sector Strengthening Program is a 36&ndash;60 month initiative to deepen trust, expand access, and build the professional capacity Nigeria's insurance industry needs to grow.</p>

        <div class="mt-14 flex flex-wrap gap-x-16 gap-y-8 border-t border-white/15 pt-10">
            <div>
                <div class="font-serif text-4xl md:text-5xl text-issp-orange-500">0.5% &rarr; 10%</div>
                <div class="mt-1.5 text-sm text-white/60">Insurance-to-GDP ratio, targeted by 2031</div>
            </div>
            <div>
                <div class="font-serif text-4xl md:text-5xl">5M+</div>
                <div class="mt-1.5 text-sm text-white/60">Nigerians reached, across all 36 states and the FCT</div>
            </div>
            <div>
                <div class="font-serif text-4xl md:text-5xl">36&ndash;60</div>
                <div class="mt-1.5 text-sm text-white/60">Months, the length of the programme</div>
            </div>
        </div>
    </div>
</section>

<!-- Narrative -->
<section class="max-w-5xl mx-auto px-6 py-16 md:py-24">
  <div class="grid lg:grid-cols-5 gap-12 lg:gap-14 items-start">
    <div class="lg:col-span-3 max-w-[62ch]">
        <p class="font-serif text-2xl md:text-[1.75rem] leading-snug text-gray-900">Despite being Africa's largest economy, Nigeria's insurance penetration sits at just 0.5% of GDP &mdash; against a continental average of 3.2%.</p>
        <p class="mt-6 text-gray-600 leading-relaxed">The ISSP exists to close that gap: through advocacy, awareness, capacity building and inclusive growth that reaches the women, youth and small businesses the sector has historically left behind. It is designed to respond directly to the structural barriers &mdash; limited awareness, weak product innovation, gender disparities, youth exclusion and thin MSME coverage &mdash; that have kept insurance out of reach for most Nigerians.</p>
        <p class="mt-5 text-gray-600 leading-relaxed">Led by the National Insurance Commission (NAICOM) together with government, regulators, insurers, brokers, development partners and innovators, the programme translates regulatory intent into measurable impact &mdash; coordinated action in place of isolated initiatives.</p>
    </div>
    <figure class="lg:col-span-2 lg:sticky lg:top-8">
        <div class="relative">
            <div class="absolute -inset-3 rounded-3xl bg-issp-green-50 -rotate-2" aria-hidden="true"></div>
            <img src="<?= asset('img/about-launch.jpg') ?>" alt="Programme leaders at the official launch of the ISSP, presenting an appreciation award to the keynote speaker" loading="lazy" class="relative rounded-2xl w-full aspect-[3/2] object-cover object-center shadow-card">
        </div>
        <figcaption class="mt-5 text-sm text-gray-500 leading-snug">The official launch of the ISSP, where partners marked the start of the programme.</figcaption>
    </figure>
  </div>
</section>

<!-- Case for Action -->
<section class="bg-issp-green-900 text-white py-16 md:py-24">
    <div class="max-w-5xl mx-auto px-6">
        <div class="max-w-xl">
            <h2 class="font-serif text-3xl md:text-4xl">The case for action</h2>
            <p class="mt-4 text-white/65 leading-relaxed">Where Nigeria's insurance sector stands today, measured against where it needs to be.</p>
        </div>

        <div class="mt-14 grid grid-cols-3 sm:grid-cols-6 gap-4 md:gap-6 items-end" style="height: 260px;">
            <?php foreach ($caseForAction as $i => $stat): ?>
                <?php
                    $heightPct = max(8, ($stat['value'] / $chartMax) * 100);
                    $isHeadline = $i === 0;
                ?>
                <div class="h-full flex flex-col justify-end items-start">
                    <div class="font-serif text-xl md:text-2xl mb-2 <?= $isHeadline ? 'text-issp-orange-500' : 'text-white' ?>"><?= e($stat['display']) ?></div>
                    <div class="w-full rounded-t-sm <?= $isHeadline ? 'bg-issp-orange-500' : 'bg-white/25' ?>" style="height: <?= $heightPct ?>%;"></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-5 grid grid-cols-3 sm:grid-cols-6 gap-4 md:gap-6">
            <?php foreach ($caseForAction as $stat): ?>
                <div class="text-xs text-white/55 leading-snug"><?= e($stat['label']) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Pillars teaser -->
<section class="max-w-5xl mx-auto px-6 py-16 md:py-24">
    <div class="flex items-end justify-between gap-6 flex-wrap mb-12">
        <div class="max-w-lg">
            <h2 class="font-serif text-3xl md:text-4xl text-gray-900">Six pillars, one shared goal</h2>
            <p class="mt-4 text-gray-500 leading-relaxed">Every intervention the programme runs sits under one of six strategic pillars.</p>
        </div>
        <a href="/programme-pillars" class="inline-flex items-center gap-1.5 text-sm font-medium text-issp-green-700 hover:text-issp-green-800 flex-shrink-0">
            See the full framework
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14 M13 6l6 6-6 6"/></svg>
        </a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-gray-100 border border-gray-100 rounded-xl overflow-hidden">
        <?php foreach ($pillars as $pillar): ?>
            <a href="/programme-pillars" class="bg-white p-6 hover:bg-gray-50 transition-colors">
                <h3 class="font-semibold text-gray-900"><?= e($pillar['title']) ?></h3>
                <p class="mt-2 text-sm text-gray-500 leading-relaxed"><?= e($pillar['summary']) ?></p>
            </a>
        <?php endforeach; ?>
        <?php if (empty($pillars)): ?>
            <p class="col-span-full bg-white p-8 text-center text-sm text-gray-400">The programme pillars will appear here once published.</p>
        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section class="max-w-5xl mx-auto px-6 pb-20 md:pb-28">
    <div class="bg-issp-green-50 rounded-2xl px-8 py-12 md:px-14 md:py-16 flex flex-wrap items-center justify-between gap-8">
        <div class="max-w-md">
            <h2 class="font-serif text-2xl md:text-3xl text-gray-900">Join the coalition</h2>
            <p class="mt-3 text-gray-600 leading-relaxed">Whether you're a regulator, insurer, donor or innovator, there's a defined role for you in strengthening Nigeria's insurance sector.</p>
        </div>
        <a href="/contact" class="inline-flex items-center gap-2 bg-issp-orange-600 hover:bg-issp-orange-700 transition-colors text-white px-6 py-3 rounded-lg font-medium flex-shrink-0">
            Get in touch
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14 M13 6l6 6-6 6"/></svg>
        </a>
    </div>
</section>
