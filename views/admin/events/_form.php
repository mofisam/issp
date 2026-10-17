<?php
/**
 * Shared form markup for create.php and edit.php.
 * Expects: $formAction, $event (array), $categories, $isEdit (bool),
 * $liveUrl (string|null), $previewUrl (string|null).
 */
$status      = $event['status'] ?? 'draft';
$startsLocal = !empty($event['starts_at']) ? date('Y-m-d\TH:i', strtotime($event['starts_at'])) : '';
$endsLocal   = !empty($event['ends_at']) ? date('Y-m-d\TH:i', strtotime($event['ends_at'])) : '';
$inputCls    = 'border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500';
?>
<form method="POST" action="<?= e($formAction) ?>" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <?= \App\Core\Csrf::field() ?>

    <!-- Main content -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title</label>
                <input name="title" id="event-title" required maxlength="220"
                       value="<?= old('title', $event['title'] ?? '') ?>"
                       placeholder="e.g. Insurance Industry Stakeholder Forum"
                       class="<?= $inputCls ?>">
                <?php if (!$isEdit): ?>
                    <p class="mt-1.5 text-xs text-gray-400">
                        Event URL: <code id="slug-preview" class="font-mono text-gray-500">/events/…</code> <span class="text-gray-300">— set automatically from the title</span>
                    </p>
                <?php endif; ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="6" placeholder="What the event is about, who should attend, the agenda..."
                          class="<?= $inputCls ?>"><?= e($event['description'] ?? '') ?></textarea>
                <p class="mt-1.5 text-xs text-gray-400">Plain text (line breaks are preserved) — no HTML formatting here.</p>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <a href="/admin/categories/events" class="text-xs font-medium text-issp-green-700 hover:underline">Manage categories</a>
                </div>
                <select name="category_id" class="<?= $inputCls ?>">
                    <option value="">No category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= ((int) ($event['category_id'] ?? 0) === (int) $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-5">
            <h2 class="text-sm font-semibold text-gray-900">Date &amp; location</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Starts at</label>
                    <input type="datetime-local" name="starts_at" required value="<?= e($startsLocal) ?>" class="<?= $inputCls ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Ends at</label>
                    <input type="datetime-local" name="ends_at" required value="<?= e($endsLocal) ?>" class="<?= $inputCls ?>">
                </div>
            </div>

            <label class="flex items-start gap-3 border border-gray-200 rounded-lg px-4 py-3 cursor-pointer hover:border-issp-green-300">
                <input type="checkbox" name="is_virtual" value="1" id="is_virtual" class="mt-0.5" <?= !empty($event['is_virtual']) ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-medium text-gray-800">This is a virtual event</span>
                    <span class="block text-xs text-gray-400">Shows a meeting link instead of a physical venue.</span>
                </span>
            </label>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Location name</label>
                    <input name="location_name" value="<?= old('location_name', $event['location_name'] ?? '') ?>" placeholder="e.g. Transcorp Hilton, Abuja" class="<?= $inputCls ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Location address</label>
                    <input name="location_address" value="<?= old('location_address', $event['location_address'] ?? '') ?>" class="<?= $inputCls ?>">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Virtual meeting URL</label>
                <input name="virtual_meeting_url" value="<?= old('virtual_meeting_url', $event['virtual_meeting_url'] ?? '') ?>" placeholder="https://..." class="<?= $inputCls ?>">
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Publish</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select name="status" class="<?= $inputCls ?>">
                    <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'cancelled' => 'Cancelled', 'completed' => 'Completed'] as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $status === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1.5 text-xs text-gray-400">Only "Published" events appear on the calendar.</p>
            </div>
            <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
                <?= $isEdit ? 'Save changes' : 'Create event' ?>
            </button>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Registration</h2>
            <label class="flex items-start gap-3 border border-gray-200 rounded-lg px-4 py-3 cursor-pointer hover:border-issp-green-300">
                <input type="checkbox" name="registration_required" value="1" class="mt-0.5" <?= !empty($event['registration_required']) ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-medium text-gray-800">Require registration</span>
                    <span class="block text-xs text-gray-400">Visitors must submit their details to attend.</span>
                </span>
            </label>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Capacity</label>
                <input type="number" name="capacity" min="0" value="<?= old('capacity', (string) ($event['capacity'] ?? '')) ?>" placeholder="Optional" class="<?= $inputCls ?>">
            </div>
        </div>

        <?php if ($isEdit): ?>
            <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-3">
                <h2 class="text-sm font-semibold text-gray-900">Event link</h2>
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
                        <span class="flex-1 text-center text-xs font-medium bg-gray-50 text-gray-400 px-3 py-2 rounded-lg" title="Publish the event to make this link public">Not published</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</form>

<?php if (!$isEdit): ?>
<script>
    (function () {
        var titleInput = document.getElementById('event-title');
        var slugPreview = document.getElementById('slug-preview');
        function slugify(value) {
            return value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        }
        function update() {
            var slug = slugify(titleInput.value) || '…';
            slugPreview.textContent = '/events/' + slug;
        }
        titleInput.addEventListener('input', update);
        update();
    })();
</script>
<?php endif; ?>
