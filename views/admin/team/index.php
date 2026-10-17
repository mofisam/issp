<?php
$groupLabels = ['leadership' => 'Leadership', 'advisory_board' => 'Advisory Board', 'secretariat' => 'Secretariat'];
$groupStyles = ['leadership' => 'bg-issp-green-50 text-issp-green-700', 'advisory_board' => 'bg-issp-orange-50 text-issp-orange-700', 'secretariat' => 'bg-blue-50 text-blue-600'];
?>
<div class="flex items-center justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Team</h1>
        <p class="text-sm text-gray-500 mt-1">Shown on the public <a href="/team" target="_blank" class="text-issp-green-700 hover:underline">/team</a> page.</p>
    </div>
    <a href="/admin/team/create" class="inline-flex items-center gap-2 bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-card flex-shrink-0">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        Add team member
    </a>
</div>

<form method="GET" action="/admin/team" class="flex flex-wrap items-end gap-3 mb-5">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name&hellip;"
               class="border border-gray-200 rounded-lg px-3.5 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Group</label>
        <select name="group" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            <option value="">All groups</option>
            <?php foreach ($groups as $value): ?>
                <option value="<?= e($value) ?>" <?= $group === $value ? 'selected' : '' ?>><?= e($groupLabels[$value] ?? ucfirst($value)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    <?php if ($search || $group): ?>
        <a href="/admin/team" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
    <?php foreach ($items as $item): ?>
        <?php $initials = strtoupper(mb_substr($item['full_name'], 0, 1)); ?>
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
            <div class="flex items-center gap-4 min-w-0">
                <div class="flex-shrink-0 h-11 w-11 rounded-full bg-issp-green-50 text-issp-green-700 flex items-center justify-center overflow-hidden text-sm font-semibold">
                    <?php if (!empty($item['photo_url'])): ?>
                        <img src="<?= e($item['photo_url']) ?>" alt="" class="h-full w-full object-cover">
                    <?php else: ?>
                        <?= e($initials) ?>
                    <?php endif; ?>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gray-900"><?= e($item['full_name']) ?></span>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full <?= $groupStyles[$item['group']] ?? 'bg-gray-100 text-gray-600' ?>"><?= e($groupLabels[$item['group']] ?? ucfirst($item['group'])) ?></span>
                        <?php if (!$item['is_published']): ?>
                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Hidden</span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-1 text-xs text-gray-400"><?= e($item['role_title']) ?></div>
                </div>
            </div>
            <div class="flex items-center gap-4 text-sm flex-shrink-0">
                <a href="/admin/team/<?= (int) $item['id'] ?>/edit" class="text-issp-green-700 font-medium">Edit</a>
                <form method="POST" action="/admin/team/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('Remove this team member?');">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="text-red-600 font-medium">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <div class="px-5 py-12 text-center">
            <?php if ($search || $group): ?>
                <p class="text-sm text-gray-500">No team members match your search or filter.</p>
                <a href="/admin/team" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Clear search &amp; filters</a>
            <?php else: ?>
                <p class="text-sm text-gray-500">No team members yet.</p>
                <a href="/admin/team/create" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Add your first team member &rarr;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/partials/admin-pagination.php'; ?>
