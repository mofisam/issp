<?php
/**
 * Shared form markup for create.php and edit.php.
 * Expects: $formAction, $page (array, may have empty values on create),
 * $isEdit (bool), $liveUrl (string|null), $previewUrl (string|null).
 */
$status = $page['status'] ?? 'draft';
?>
<form method="POST" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <?= \App\Core\Csrf::field() ?>

    <!-- Main content -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title</label>
                <input name="title" id="page-title" required maxlength="200"
                       value="<?= old('title', $page['title'] ?? '') ?>"
                       placeholder="e.g. About ISSP"
                       class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
                <?php if (!$isEdit): ?>
                    <p class="mt-1.5 text-xs text-gray-400">
                        Page URL: <code id="slug-preview" class="font-mono text-gray-500">/pages/…</code> <span class="text-gray-300">— set automatically from the title</span>
                    </p>
                <?php endif; ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Excerpt</label>
                <textarea name="excerpt" rows="2" placeholder="A short one- or two-line summary (optional, used in listings and SEO)"
                          class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500"><?= e($page['excerpt'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Body content</label>
                <div id="quill-body_content" style="min-height: 360px;" class="bg-white border border-gray-200 rounded-lg flex items-center justify-center text-sm text-gray-400">Loading editor&hellip;</div>
                <textarea name="body_content" id="input-body_content" class="hidden"><?= e($page['body_content'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-3">
            <h2 class="text-sm font-semibold text-gray-900">Featured image</h2>
            <p class="text-xs text-gray-400">Shown at the top of the page and used when the link is shared.</p>
            <div class="h-32 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center overflow-hidden text-xs text-gray-400">
                <?php if (!empty($page['featured_image_url'])): ?>
                    <img src="<?= e($page['featured_image_url']) ?>" alt="" class="h-full w-full object-cover">
                <?php else: ?>
                    No image yet
                <?php endif; ?>
            </div>
            <label for="page-featured-image" class="block text-xs font-medium text-issp-green-700 border border-gray-200 rounded-lg px-3 py-2 text-center cursor-pointer hover:border-issp-green-300">
                <?= !empty($page['featured_image_url']) ? 'Replace image' : 'Choose image' ?>
                <input id="page-featured-image" type="file" name="featured_image" accept="image/*" class="hidden">
            </label>
            <?php if (!empty($page['featured_image_url'])): ?>
                <label class="flex items-center gap-2 text-xs text-gray-500">
                    <input type="checkbox" name="remove_featured_image" value="1" class="rounded border-gray-300">
                    Remove current image
                </label>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Publish</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select name="status" class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
                    <?php foreach (['draft' => 'Draft', 'pending_review' => 'Pending review', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $status === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1.5 text-xs text-gray-400">Only "Published" pages are visible on the live site.</p>
            </div>
            <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
                <?= $isEdit ? 'Save changes' : 'Create page' ?>
            </button>
        </div>

        <?php if ($isEdit): ?>
            <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-3">
                <h2 class="text-sm font-semibold text-gray-900">Page link</h2>
                <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                    <svg viewBox="0 0 24 24" class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 17H7a5 5 0 0 1 0-10h2M15 7h2a5 5 0 0 1 0 10h-2M8 12h8"/></svg>
                    <input id="live-url" type="text" readonly value="<?= e($liveUrl) ?>" class="bg-transparent text-xs font-mono text-gray-600 w-full outline-none truncate">
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('live-url').value); this.textContent='Copied';setTimeout(()=>this.textContent='Copy',1200);" class="text-xs font-medium text-issp-green-700 flex-shrink-0">Copy</button>
                </div>
                <div class="flex gap-2">
                    <a href="<?= e($previewUrl) ?>" target="_blank" class="flex-1 text-center text-xs font-medium border border-gray-200 hover:border-issp-green-300 text-gray-700 px-3 py-2 rounded-lg">Preview</a>
                    <?php if ($status === 'published'): ?>
                        <a href="<?= e($liveUrl) ?>" target="_blank" class="flex-1 text-center text-xs font-medium bg-issp-green-50 text-issp-green-700 px-3 py-2 rounded-lg">View live</a>
                    <?php else: ?>
                        <span class="flex-1 text-center text-xs font-medium bg-gray-50 text-gray-400 px-3 py-2 rounded-lg" title="Publish the page to make this link public">Not published</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</form>

<?php require BASE_PATH . '/views/partials/rich-editor-assets.php'; ?>
<script>
    initRichEditor('quill-body_content', 'input-body_content');

    (function () {
        var input = document.getElementById('page-featured-image');
        var label = input.closest('label');
        var defaultText = label.firstChild.textContent.trim();
        input.addEventListener('change', function () {
            label.firstChild.textContent = input.files.length ? input.files[0].name : defaultText;
        });
    })();

    <?php if (!$isEdit): ?>
    (function () {
        var titleInput = document.getElementById('page-title');
        var slugPreview = document.getElementById('slug-preview');
        function slugify(value) {
            return value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        }
        function update() {
            var slug = slugify(titleInput.value) || '…';
            slugPreview.textContent = '/pages/' + slug;
        }
        titleInput.addEventListener('input', update);
        update();
    })();
    <?php endif; ?>
</script>
