<?php
$activityVerbs = ['created' => 'added', 'updated' => 'updated', 'deleted' => 'removed', 'emailed' => 'emailed'];

$buildUrl = static function (int $page) use ($action, $entityType): string {
    $query = array_filter(['page' => $page, 'action' => $action, 'entity_type' => $entityType]);
    return '/admin/activity' . ($query ? '?' . http_build_query($query) : '');
};
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Activity log</h1>
    <p class="text-sm text-gray-500 mt-1">Every create, update, delete, and email action taken across the CMS.</p>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-card p-5 mb-6">
    <form method="GET" action="/admin/activity" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Action</label>
            <select name="action" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
                <option value="">All actions</option>
                <?php foreach ($actions as $value): ?>
                    <option value="<?= e($value) ?>" <?= $action === $value ? 'selected' : '' ?>><?= e(ucfirst($value)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Entity type</label>
            <select name="entity_type" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
                <option value="">All types</option>
                <?php foreach ($entityTypes as $value): ?>
                    <option value="<?= e($value) ?>" <?= $entityType === $value ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $value))) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
        <?php if ($action || $entityType): ?>
            <a href="/admin/activity" class="text-sm text-gray-500 hover:text-gray-700">Clear filters</a>
        <?php endif; ?>
        <span class="ml-auto text-xs text-gray-400"><?= number_format($total) ?> total <?= $total === 1 ? 'entry' : 'entries' ?></span>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
    <?php if (empty($logs)): ?>
        <p class="px-5 py-10 text-center text-sm text-gray-500">No activity matches these filters.</p>
    <?php else: ?>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-400 uppercase tracking-wide border-b border-gray-100">
                    <th class="px-5 py-3 font-medium">Who</th>
                    <th class="px-5 py-3 font-medium">Action</th>
                    <th class="px-5 py-3 font-medium">Entity</th>
                    <th class="px-5 py-3 font-medium">IP address</th>
                    <th class="px-5 py-3 font-medium">When</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-gray-900"><?= e(trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?: 'System') ?></div>
                            <?php if (!empty($log['email'])): ?>
                                <div class="text-xs text-gray-400"><?= e($log['email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-issp-green-50 text-issp-green-700"><?= e($activityVerbs[$log['action']] ?? $log['action']) ?></span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">
                            <?= e(ucfirst(str_replace('_', ' ', $log['entity_type']))) ?>
                            <span class="text-gray-400">#<?= (int) $log['entity_id'] ?></span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-400 font-mono text-xs"><?= e($log['ip_address'] ?? '—') ?></td>
                        <td class="px-5 py-3.5 text-gray-500 text-xs whitespace-nowrap"><?= e(format_date($log['created_at'], 'd M Y, g:ia')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php if ($lastPage > 1): ?>
    <div class="flex items-center justify-between mt-5 text-sm">
        <span class="text-gray-400">Page <?= (int) $page ?> of <?= (int) $lastPage ?></span>
        <div class="flex items-center gap-2">
            <?php if ($page > 1): ?>
                <a href="<?= e($buildUrl($page - 1)) ?>" class="px-3.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:border-issp-green-300">Previous</a>
            <?php endif; ?>
            <?php if ($page < $lastPage): ?>
                <a href="<?= e($buildUrl($page + 1)) ?>" class="px-3.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:border-issp-green-300">Next</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
