<?php
/** Expects: $types, $counts, $grand, $type, $items, $total, $page, $lastPage. */
$trash = 'M4 7h16M10 11v6M14 11v6M6 7l1 12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-12M9 7V4h6v3';
?>
<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Recycle bin</h1>
        <p class="text-sm text-gray-500 mt-1">Deleted items wait here. Restore them, or remove them permanently &mdash; permanent removal cannot be undone.</p>
    </div>
    <?php if ($grand > 0): ?>
        <form method="POST" action="/admin/recycle-bin/empty" onsubmit="return confirm('Permanently delete <?= $type !== '' ? 'everything in this category' : 'EVERYTHING in the recycle bin' ?>? This cannot be undone.');">
            <?= \App\Core\Csrf::field() ?>
            <input type="hidden" name="type" value="<?= e($type) ?>">
            <button type="submit" class="inline-flex items-center gap-2 text-sm font-medium text-red-600 border border-red-200 hover:bg-red-50 rounded-lg px-4 py-2">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $trash ?>"/></svg>
                <?= $type !== '' ? 'Empty this category' : 'Empty recycle bin' ?>
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="flex flex-wrap gap-2 mb-5">
    <a href="/admin/recycle-bin" class="rounded-full border px-3.5 py-1.5 text-sm font-medium <?= $type === '' ? 'bg-issp-green-700 border-issp-green-700 text-white' : 'bg-white border-gray-200 text-gray-600 hover:border-issp-green-500' ?>">Overview <span class="opacity-70"><?= (int) $grand ?></span></a>
    <?php foreach ($types as $key => $label): ?>
        <a href="/admin/recycle-bin?type=<?= e($key) ?>" class="rounded-full border px-3.5 py-1.5 text-sm font-medium <?= $type === $key ? 'bg-issp-green-700 border-issp-green-700 text-white' : 'bg-white border-gray-200 hover:border-issp-green-500 ' . ($counts[$key] ? 'text-gray-700' : 'text-gray-400') ?>"><?= e($label) ?> <span class="opacity-70"><?= (int) $counts[$key] ?></span></a>
    <?php endforeach; ?>
</div>

<?php if ($type === ''): ?>
    <div class="bg-white rounded-xl border border-gray-100 shadow-card">
        <?php if ($grand === 0): ?>
            <div class="px-6 py-16 text-center">
                <div class="mx-auto h-12 w-12 rounded-full bg-issp-green-50 text-issp-green-700 flex items-center justify-center">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $trash ?>"/></svg>
                </div>
                <p class="mt-4 font-medium text-gray-900">The recycle bin is empty</p>
                <p class="text-sm text-gray-500 mt-1">Items deleted from the admin area will show up here.</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-50">
                <?php foreach ($types as $key => $label): if (!$counts[$key]) continue; ?>
                    <a href="/admin/recycle-bin?type=<?= e($key) ?>" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50/60">
                        <span class="font-medium text-gray-800"><?= e($label) ?></span>
                        <span class="text-sm text-gray-500"><?= (int) $counts[$key] ?> deleted &rarr;</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
        <?php foreach ($items as $item): ?>
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-gray-50 last:border-0">
                <div class="min-w-0">
                    <div class="font-medium text-gray-900 truncate"><?= e(trim((string) $item['title']) ?: '(untitled)') ?></div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        <?= e(implode(' · ', array_filter([$item['detail'], 'Deleted ' . format_date($item['deleted_at'], 'd M Y, H:i')]))) ?>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-sm flex-shrink-0">
                    <form method="POST" action="/admin/recycle-bin/<?= e($type) ?>/<?= (int) $item['id'] ?>/restore">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="text-issp-green-700 font-medium">Restore</button>
                    </form>
                    <form method="POST" action="/admin/recycle-bin/<?= e($type) ?>/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('Permanently delete this item? This cannot be undone.');">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="text-red-600 font-medium">Delete forever</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
            <div class="px-5 py-14 text-center text-sm text-gray-500">Nothing deleted in <?= e($types[$type]) ?>.</div>
        <?php endif; ?>
    </div>
    <?php require BASE_PATH . '/views/partials/admin-pagination.php'; ?>
<?php endif; ?>
