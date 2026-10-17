<h1 class="text-2xl font-bold mb-6">Media library</h1>

<form method="POST" action="/admin/media" enctype="multipart/form-data" class="bg-white border border-gray-100 rounded-lg p-6 mb-6 flex flex-wrap items-end gap-3">
    <?= \App\Core\Csrf::field() ?>
    <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-medium mb-1">File</label>
        <input type="file" name="file" required class="border rounded px-3 py-2 w-full">
    </div>
    <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-medium mb-1">Alt text / description</label>
        <input name="alt_text" class="border rounded px-3 py-2 w-full">
    </div>
    <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-6 py-2 rounded font-medium">Upload</button>
</form>

<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
    <?php foreach ($items as $item): ?>
        <?php $isImage = str_starts_with($item['mime_type'], 'image/'); ?>
        <div class="bg-white border border-gray-100 rounded-lg overflow-hidden">
            <div class="h-28 bg-gray-50 flex items-center justify-center">
                <?php if ($isImage): ?>
                    <img src="/<?= e(ltrim(str_replace(BASE_PATH . '/public/', '', $item['file_path']), '/')) ?>" alt="<?= e($item['alt_text'] ?? '') ?>" class="h-full w-full object-cover">
                <?php else: ?>
                    <span class="text-xs text-gray-400 uppercase"><?= e(pathinfo($item['file_name'], PATHINFO_EXTENSION)) ?></span>
                <?php endif; ?>
            </div>
            <div class="p-2">
                <p class="text-xs truncate" title="<?= e($item['file_name']) ?>"><?= e($item['file_name']) ?></p>
                <p class="text-[10px] text-gray-400"><?= number_format($item['file_size_bytes'] / 1024, 1) ?> KB</p>
                <form method="POST" action="/admin/media/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('Delete this file?');">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="text-red-600 text-xs font-medium mt-1">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <p class="col-span-full text-sm text-gray-500">No files uploaded yet.</p>
    <?php endif; ?>
</div>
