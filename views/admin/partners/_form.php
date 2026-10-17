<?php
/**
 * Shared form markup for create.php and edit.php.
 * Expects: $formAction, $partner (array), $organizations, $isEdit (bool).
 */
$inputCls = 'border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500';
$typeOptions = [
    'implementing_partner' => 'Implementing Partner',
    'donor'                => 'Donor',
    'institutional_host'   => 'Institutional Host',
    'advisory'             => 'Advisory',
    'other'                => 'Other',
];
?>
<form method="POST" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <?= \App\Core\Csrf::field() ?>

    <!-- Main content -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
                <input name="name" required value="<?= old('name', $partner['name'] ?? '') ?>" placeholder="e.g. National Insurance Commission" class="<?= $inputCls ?>">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                    <select name="partner_type" class="<?= $inputCls ?>">
                        <?php foreach ($typeOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= (($partner['partner_type'] ?? 'other') === $value) ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Linked organization</label>
                    <select name="organization_id" class="<?= $inputCls ?>">
                        <option value="">None</option>
                        <?php foreach ($organizations as $org): ?>
                            <option value="<?= (int) $org['id'] ?>" <?= ((int) ($partner['organization_id'] ?? 0) === (int) $org['id']) ? 'selected' : '' ?>><?= e($org['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Website URL</label>
                <input name="website_url" value="<?= old('website_url', $partner['website_url'] ?? '') ?>" placeholder="https://..." class="<?= $inputCls ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="4" class="<?= $inputCls ?>"><?= e($partner['description'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Logo</h2>
            <div class="flex items-center gap-4">
                <div class="h-16 w-16 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                    <?php if (!empty($partner['logo_url'])): ?>
                        <img src="<?= e($partner['logo_url']) ?>" alt="" class="h-full w-full object-contain p-1">
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m2.5 12 4-4 3 2 3-3 3 2 3-2 3 3-4 4-2.5-2L13 14l-2.5-2-2.5 2L5.5 12 4 13.5"/></svg>
                    <?php endif; ?>
                </div>
                <label for="partner-logo" class="flex-1 text-xs font-medium text-issp-green-700 border border-gray-200 rounded-lg px-3 py-2 text-center cursor-pointer hover:border-issp-green-300">
                    <?= $isEdit ? 'Replace logo' : 'Choose logo' ?>
                    <input id="partner-logo" type="file" name="logo" accept="image/*" class="hidden">
                </label>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Display</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Sort order</label>
                <input type="number" name="sort_order" value="<?= old('sort_order', (string) ($partner['sort_order'] ?? 0)) ?>" class="<?= $inputCls ?>">
                <p class="mt-1.5 text-xs text-gray-400">Lower numbers appear first.</p>
            </div>
            <label class="flex items-start gap-3 border border-gray-200 rounded-lg px-4 py-3 cursor-pointer hover:border-issp-green-300">
                <input type="checkbox" name="is_published" value="1" class="mt-0.5" <?= !$isEdit || !empty($partner['is_published']) ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-medium text-gray-800">Published</span>
                    <span class="block text-xs text-gray-400">Visible on the public partners page.</span>
                </span>
            </label>
            <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
                <?= $isEdit ? 'Save changes' : 'Add partner' ?>
            </button>
        </div>
    </div>
</form>

<script>
    (function () {
        var input = document.getElementById('partner-logo');
        var label = input.closest('label');
        var defaultText = label.firstChild.textContent.trim();
        input.addEventListener('change', function () {
            label.firstChild.textContent = input.files.length ? input.files[0].name : defaultText;
        });
    })();
</script>
