<div class="mb-6">
    <a href="<?= e($config['backUrl']) ?>" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        <?= e($config['backLabel']) ?>
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2"><?= e($config['label']) ?></h1>
    <p class="text-sm text-gray-500 mt-1">Used to group items in <?= e($config['backLabel']) ?>.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
        <?php foreach ($items as $item): ?>
            <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
                <div class="min-w-0">
                    <div class="font-medium text-gray-900"><?= e($item['name']) ?></div>
                    <div class="text-xs text-gray-400 font-mono"><?= e($item['slug']) ?></div>
                </div>
                <div class="flex items-center gap-4 text-sm flex-shrink-0">
                    <a href="/admin/categories/<?= e($type) ?>/<?= (int) $item['id'] ?>/edit" class="text-issp-green-700 font-medium">Rename</a>
                    <form method="POST" action="/admin/categories/<?= e($type) ?>/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('Delete this category? Items using it will become uncategorised.');">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="text-red-600 font-medium">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
            <p class="px-5 py-10 text-center text-sm text-gray-500">No categories yet &mdash; add the first one.</p>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 h-fit">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Add category</h2>
        <form method="POST" action="/admin/categories/<?= e($type) ?>" class="space-y-3">
            <?= \App\Core\Csrf::field() ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
                <input name="name" required maxlength="100" placeholder="e.g. Press Release"
                       class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            </div>
            <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">Add category</button>
        </form>
    </div>
</div>
