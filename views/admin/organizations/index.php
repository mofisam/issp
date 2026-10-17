<?php
$typeStyles = [
    'insurer'    => 'bg-issp-green-50 text-issp-green-700',
    'regulator'  => 'bg-issp-orange-50 text-issp-orange-700',
    'partner'    => 'bg-blue-50 text-blue-600',
    'donor'      => 'bg-purple-50 text-purple-600',
    'government' => 'bg-gray-200 text-gray-600',
    'other'      => 'bg-gray-100 text-gray-500',
];
?>
<div class="flex items-center justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Organizations</h1>
        <p class="text-sm text-gray-500 mt-1">Insurers, regulators, and partner bodies whose staff can log into the stakeholder portal.</p>
    </div>
    <a href="/admin/organizations/create" class="inline-flex items-center gap-2 bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-card flex-shrink-0">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        Add organization
    </a>
</div>

<form method="GET" action="/admin/organizations" class="flex flex-wrap items-end gap-3 mb-5">
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
                <option value="<?= e($value) ?>" <?= $type === $value ? 'selected' : '' ?>><?= e(ucfirst($value)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Status</label>
        <select name="active" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            <option value="">All</option>
            <option value="1" <?= $active === '1' ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= $active === '0' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
    <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    <?php if ($search || $type || $active !== ''): ?>
        <a href="/admin/organizations" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
    <?php foreach ($items as $item): ?>
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
            <div class="flex items-center gap-4 min-w-0">
                <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center">
                    <svg viewBox="0 0 24 24" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 21V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v16 M15 21v-8h4a1 1 0 0 1 1 1v7 M8 7h1M11 7h1M8 10.5h1M11 10.5h1M8 14h1M11 14h1"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gray-900"><?= e($item['name']) ?></span>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full <?= $typeStyles[$item['organization_type']] ?? $typeStyles['other'] ?>"><?= e(ucfirst($item['organization_type'])) ?></span>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full <?= $item['is_active'] ? 'bg-issp-green-50 text-issp-green-700' : 'bg-gray-100 text-gray-500' ?>"><?= $item['is_active'] ? 'Active' : 'Inactive' ?></span>
                    </div>
                    <div class="mt-1 text-xs text-gray-400">
                        <?= e($item['country'] ?? '') ?>
                        <?php if (!empty($item['contact_email'])): ?> &middot; <?= e($item['contact_email']) ?><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-4 text-sm flex-shrink-0">
                <a href="/admin/organizations/<?= (int) $item['id'] ?>/edit" class="text-issp-green-700 font-medium">Edit</a>
                <form method="POST" action="/admin/organizations/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('Remove this organization?');">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="text-red-600 font-medium">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <div class="px-5 py-12 text-center">
            <?php if ($search || $type || $active !== ''): ?>
                <p class="text-sm text-gray-500">No organizations match your search or filter.</p>
                <a href="/admin/organizations" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Clear search &amp; filters</a>
            <?php else: ?>
                <p class="text-sm text-gray-500">No organizations yet.</p>
                <a href="/admin/organizations/create" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Add your first organization &rarr;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/partials/admin-pagination.php'; ?>
