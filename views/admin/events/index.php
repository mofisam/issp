<?php
$origin = (($_SERVER['HTTPS'] ?? 'off') !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$statusStyles = [
    'draft'     => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => 'Draft'],
    'published' => ['bg' => 'bg-issp-green-50', 'text' => 'text-issp-green-700', 'label' => 'Published'],
    'cancelled' => ['bg' => 'bg-red-50', 'text' => 'text-red-600', 'label' => 'Cancelled'],
    'completed' => ['bg' => 'bg-gray-200', 'text' => 'text-gray-500', 'label' => 'Completed'],
];
?>
<div class="flex items-center justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Events</h1>
        <p class="text-sm text-gray-500 mt-1">Conferences, webinars and workshops shown on the public event calendar.</p>
    </div>
    <a href="/admin/events/create" class="inline-flex items-center gap-2 bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-card flex-shrink-0">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        New event
    </a>
</div>

<form method="GET" action="/admin/events" class="flex flex-wrap items-end gap-3 mb-5">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by title&hellip;"
               class="border border-gray-200 rounded-lg px-3.5 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Status</label>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $value): ?>
                <option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= e(ucfirst($value)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Category</label>
        <select name="category_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            <option value="">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= $categoryId === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    <?php if ($search || $status || $categoryId): ?>
        <a href="/admin/events" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
    <?php foreach ($items as $item): ?>
        <?php
            $style = $statusStyles[$item['status']] ?? $statusStyles['draft'];
            $liveUrl    = $origin . '/events/' . $item['slug'];
            $previewUrl = '/admin/events/' . (int) $item['id'] . '/preview';
            $startsTs   = strtotime($item['starts_at']);
        ?>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
            <div class="flex items-start gap-4 min-w-0">
                <div class="flex-shrink-0 w-12 text-center bg-issp-green-50 rounded-lg py-1.5">
                    <div class="text-[10px] font-semibold text-issp-green-700 uppercase"><?= e(date('M', $startsTs)) ?></div>
                    <div class="text-base font-bold text-issp-green-800 leading-none"><?= e(date('d', $startsTs)) ?></div>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gray-900"><?= e($item['title']) ?></span>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full <?= $style['bg'] ?> <?= $style['text'] ?>"><?= $style['label'] ?></span>
                        <?php if ($item['is_virtual']): ?>
                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-blue-50 text-blue-600">Virtual</span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-1 flex items-center gap-3 text-xs text-gray-400 flex-wrap">
                        <span><?= e(format_date($item['starts_at'], 'd M Y, g:ia')) ?></span>
                        <?php if (!empty($item['location_name'])): ?><span>&middot; <?= e($item['location_name']) ?></span><?php endif; ?>
                        <span class="flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 17H7a5 5 0 0 1 0-10h2M15 7h2a5 5 0 0 1 0 10h-2M8 12h8"/></svg>
                            <span class="truncate font-mono"><?= e($liveUrl) ?></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-4 text-sm flex-shrink-0">
                <a href="<?= e($previewUrl) ?>" target="_blank" class="text-gray-500 hover:text-issp-green-700 font-medium">Preview</a>
                <?php if ($item['status'] === 'published'): ?>
                    <a href="<?= e($liveUrl) ?>" target="_blank" class="text-gray-500 hover:text-issp-green-700 font-medium">View live</a>
                <?php endif; ?>
                <a href="/admin/events/<?= (int) $item['id'] ?>/edit" class="text-issp-green-700 font-medium">Edit</a>
                <form method="POST" action="/admin/events/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('Delete this event?');">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="text-red-600 font-medium">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <div class="px-5 py-12 text-center">
            <?php if ($search || $status || $categoryId): ?>
                <p class="text-sm text-gray-500">No events match your search or filter.</p>
                <a href="/admin/events" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Clear search &amp; filters</a>
            <?php else: ?>
                <p class="text-sm text-gray-500">No events yet.</p>
                <a href="/admin/events/create" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Create your first event &rarr;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/partials/admin-pagination.php'; ?>
