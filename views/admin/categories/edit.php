<div class="mb-6">
    <a href="/admin/categories/<?= e($type) ?>" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        <?= e($config['label']) ?>
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">Rename category</h1>
</div>

<form method="POST" action="/admin/categories/<?= e($type) ?>/<?= (int) $category['id'] ?>" class="max-w-md bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4">
    <?= \App\Core\Csrf::field() ?>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
        <input name="name" required maxlength="100" value="<?= old('name', $category['name']) ?>"
               class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
        <p class="mt-1.5 text-xs text-gray-400">Current slug: <code class="font-mono"><?= e($category['slug']) ?></code> (slug doesn't change when you rename)</p>
    </div>
    <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">Save changes</button>
</form>
