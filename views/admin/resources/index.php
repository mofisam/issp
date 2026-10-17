<?php
$origin = (($_SERVER['HTTPS'] ?? 'off') !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$visibilityStyles = [
    'public'      => ['bg' => 'bg-issp-green-50', 'text' => 'text-issp-green-700', 'label' => 'Public'],
    'stakeholder' => ['bg' => 'bg-issp-orange-50', 'text' => 'text-issp-orange-700', 'label' => 'Stakeholder'],
    'private'     => ['bg' => 'bg-gray-200', 'text' => 'text-gray-600', 'label' => 'Private'],
];
?>
<div class="flex items-center justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Downloads Library</h1>
        <p class="text-sm text-gray-500 mt-1">Reports, policy briefs and documents available from the resource library.</p>
    </div>
    <a href="/admin/resources/create" class="inline-flex items-center gap-2 bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-card flex-shrink-0">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        Upload document
    </a>
</div>

<form method="GET" action="/admin/resources" class="flex flex-wrap items-end gap-3 mb-5">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by title&hellip;"
               class="border border-gray-200 rounded-lg px-3.5 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Visibility</label>
        <select name="visibility" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            <option value="">All</option>
            <?php foreach ($visibilities as $value): ?>
                <option value="<?= e($value) ?>" <?= $visibility === $value ? 'selected' : '' ?>><?= e(ucfirst($value)) ?></option>
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
    <?php if ($search || $visibility || $categoryId): ?>
        <a href="/admin/resources" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
    <?php foreach ($items as $item): ?>
        <?php
            $style = $visibilityStyles[$item['visibility']] ?? $visibilityStyles['private'];
            $downloadUrl = $origin . '/resources/' . (int) $item['id'] . '/download';
        ?>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
            <div class="flex items-start gap-4 min-w-0">
                <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-issp-green-50 text-issp-green-700 flex items-center justify-center">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z M14 3v5h5"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gray-900"><?= e($item['title']) ?></span>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full <?= $style['bg'] ?> <?= $style['text'] ?>"><?= $style['label'] ?></span>
                        <?php if (!empty($item['publication_year'])): ?>
                            <span class="text-[11px] text-gray-400"><?= (int) $item['publication_year'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-1 flex items-center gap-3 text-xs text-gray-400 flex-wrap">
                        <span class="flex items-center gap-1"><svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v12m0 0 4-4m-4 4-4-4M4 20h16"/></svg><?= (int) $item['download_count'] ?> downloads</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-4 text-sm flex-shrink-0">
                <a href="<?= e($downloadUrl) ?>" target="_blank" class="text-gray-500 hover:text-issp-green-700 font-medium">Download</a>
                <a href="/admin/resources/<?= (int) $item['id'] ?>/edit" class="text-issp-green-700 font-medium">Edit</a>
                <form method="POST" action="/admin/resources/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('Delete this document?');">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="text-red-600 font-medium">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <div class="px-5 py-12 text-center">
            <?php if ($search || $visibility || $categoryId): ?>
                <p class="text-sm text-gray-500">No documents match your search or filter.</p>
                <a href="/admin/resources" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Clear search &amp; filters</a>
            <?php else: ?>
                <p class="text-sm text-gray-500">No documents uploaded yet.</p>
                <a href="/admin/resources/create" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Upload your first document &rarr;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/partials/admin-pagination.php'; ?>
