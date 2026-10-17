<?php
$typeLabels = [
    'implementing_partner' => 'Implementing Partners',
    'donor'                => 'Donors',
    'institutional_host'   => 'Institutional Hosts',
    'advisory'             => 'Advisory Partners',
    'other'                => 'Other Partners',
];
$grouped = [];
foreach ($partners as $partner) {
    $grouped[$partner['partner_type']][] = $partner;
}
?>
<section class="max-w-6xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold text-issp-green-700 mb-3">Our Partners</h1>
    <p class="text-gray-500 max-w-2xl mb-12">The regulators, insurers, donors and institutions working together to strengthen Nigeria's insurance sector.</p>

    <?php if (empty($partners)): ?>
        <p class="text-center text-sm text-gray-400 py-10">Our partners will appear here once published.</p>
    <?php endif; ?>

    <?php foreach ($typeLabels as $type => $label): ?>
        <?php if (empty($grouped[$type])): continue; endif; ?>
        <div class="mb-14 last:mb-0">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400 mb-5"><?= e($label) ?></h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($grouped[$type] as $partner): ?>
                    <div class="border border-gray-100 rounded-xl p-5 flex flex-col gap-4">
                        <div class="h-16 flex items-center">
                            <?php if (!empty($partner['logo_url'])): ?>
                                <img src="<?= e($partner['logo_url']) ?>" alt="<?= e($partner['name']) ?>" class="max-h-16 max-w-[160px] object-contain">
                            <?php else: ?>
                                <span class="text-base font-semibold text-gray-700"><?= e($partner['name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900"><?= e($partner['name']) ?></div>
                            <?php if (!empty($partner['description'])): ?>
                                <p class="mt-1.5 text-sm text-gray-500 leading-relaxed"><?= e($partner['description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($partner['website_url'])): ?>
                            <a href="<?= e($partner['website_url']) ?>" target="_blank" rel="noopener" class="mt-auto inline-flex items-center gap-1.5 text-sm font-medium text-issp-green-700 hover:text-issp-green-800">
                                Visit website
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14 M13 6l6 6-6 6"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>
