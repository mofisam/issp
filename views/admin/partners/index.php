<?php
$typeLabels = [
    'implementing_partner' => 'Implementing Partner',
    'donor'                => 'Donor',
    'institutional_host'   => 'Institutional Host',
    'advisory'             => 'Advisory',
    'other'                => 'Other',
];
?>
<div class="flex items-center justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Partners</h1>
        <p class="text-sm text-gray-500 mt-1">Shown on the public <a href="/partners" target="_blank" class="text-issp-green-700 hover:underline">/partners</a> page.</p>
    </div>
    <a href="/admin/partners/create" class="inline-flex items-center gap-2 bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-card flex-shrink-0">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        Add partner
    </a>
</div>

<form method="GET" action="/admin/partners" class="flex flex-wrap items-end gap-3 mb-5">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name&hellip;"
               class="border border-gray-200 rounded-lg px-3.5 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Type</label>
        <select name="type" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            <option value="">All types</option>
            <?php foreach ($types as $value): ?>
                <option value="<?= e($value) ?>" <?= $type === $value ? 'selected' : '' ?>><?= e($typeLabels[$value] ?? ucfirst($value)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    <?php if ($search || $type): ?>
        <a href="/admin/partners" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
    <?php foreach ($items as $item): ?>
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
            <div class="flex items-center gap-4 min-w-0">
                <div class="flex-shrink-0 h-11 w-11 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center overflow-hidden">
                    <?php if (!empty($item['logo_url'])): ?>
                        <img src="<?= e($item['logo_url']) ?>" alt="" class="h-full w-full object-contain p-1">
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m2.5 12 4-4 3 2 3-3 3 2 3-2 3 3-4 4-2.5-2L13 14l-2.5-2-2.5 2L5.5 12 4 13.5"/></svg>
                    <?php endif; ?>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gray-900"><?= e($item['name']) ?></span>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full <?= $item['is_published'] ? 'bg-issp-green-50 text-issp-green-700' : 'bg-gray-100 text-gray-500' ?>"><?= $item['is_published'] ? 'Published' : 'Hidden' ?></span>
                    </div>
                    <div class="mt-1 text-xs text-gray-400"><?= e($typeLabels[$item['partner_type']] ?? ucfirst($item['partner_type'])) ?></div>
                </div>
            </div>
            <div class="flex items-center gap-4 text-sm flex-shrink-0">
                <a href="/admin/partners/<?= (int) $item['id'] ?>/edit" class="text-issp-green-700 font-medium">Edit</a>
                <form method="POST" action="/admin/partners/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('Remove this partner?');">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="text-red-600 font-medium">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <div class="px-5 py-12 text-center">
            <?php if ($search || $type): ?>
                <p class="text-sm text-gray-500">No partners match your search or filter.</p>
                <a href="/admin/partners" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Clear search &amp; filters</a>
            <?php else: ?>
                <p class="text-sm text-gray-500">No partners yet.</p>
                <a href="/admin/partners/create" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Add your first partner &rarr;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/partials/admin-pagination.php'; ?>
