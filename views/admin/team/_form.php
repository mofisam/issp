<?php
/**
 * Shared form markup for create.php and edit.php.
 * Expects: $formAction, $member (array), $isEdit (bool).
 */
$inputCls = 'border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500';
$groupOptions = ['leadership' => 'Leadership', 'advisory_board' => 'Advisory Board', 'secretariat' => 'Secretariat'];
$initials = trim($member['full_name'] ?? '') !== '' ? strtoupper(mb_substr($member['full_name'], 0, 1)) : '?';
?>
<form method="POST" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <?= \App\Core\Csrf::field() ?>

    <!-- Main content -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-5">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Full name</label>
                    <input name="full_name" required value="<?= old('full_name', $member['full_name'] ?? '') ?>" class="<?= $inputCls ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Role / title</label>
                    <input name="role_title" required value="<?= old('role_title', $member['role_title'] ?? '') ?>" placeholder="e.g. Programme Director" class="<?= $inputCls ?>">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Bio</label>
                <textarea name="bio" rows="4" class="<?= $inputCls ?>"><?= e($member['bio'] ?? '') ?></textarea>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">LinkedIn URL</label>
                    <input name="linkedin_url" value="<?= old('linkedin_url', $member['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/in/..." class="<?= $inputCls ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="<?= old('email', $member['email'] ?? '') ?>" class="<?= $inputCls ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Photo</h2>
            <div class="flex items-center gap-4">
                <div class="h-16 w-16 rounded-full bg-issp-green-50 text-issp-green-700 flex items-center justify-center overflow-hidden text-lg font-semibold flex-shrink-0">
                    <?php if (!empty($member['photo_url'])): ?>
                        <img src="<?= e($member['photo_url']) ?>" alt="" class="h-full w-full object-cover">
                    <?php else: ?>
                        <?= e($initials) ?>
                    <?php endif; ?>
                </div>
                <label for="member-photo" class="flex-1 text-xs font-medium text-issp-green-700 border border-gray-200 rounded-lg px-3 py-2 text-center cursor-pointer hover:border-issp-green-300">
                    <?= $isEdit ? 'Replace photo' : 'Choose photo' ?>
                    <input id="member-photo" type="file" name="photo" accept="image/*" class="hidden">
                </label>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Display</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Group</label>
                <select name="group" class="<?= $inputCls ?>">
                    <?php foreach ($groupOptions as $value => $label): ?>
                        <option value="<?= $value ?>" <?= (($member['group'] ?? 'leadership') === $value) ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Sort order</label>
                <input type="number" name="sort_order" value="<?= old('sort_order', (string) ($member['sort_order'] ?? 0)) ?>" class="<?= $inputCls ?>">
                <p class="mt-1.5 text-xs text-gray-400">Lower numbers appear first.</p>
            </div>
            <label class="flex items-start gap-3 border border-gray-200 rounded-lg px-4 py-3 cursor-pointer hover:border-issp-green-300">
                <input type="checkbox" name="is_published" value="1" class="mt-0.5" <?= !$isEdit || !empty($member['is_published']) ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-medium text-gray-800">Published</span>
                    <span class="block text-xs text-gray-400">Visible on the public team page.</span>
                </span>
            </label>
            <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
                <?= $isEdit ? 'Save changes' : 'Add team member' ?>
            </button>
        </div>
    </div>
</form>

<script>
    (function () {
        var input = document.getElementById('member-photo');
        var label = input.closest('label');
        var defaultText = label.firstChild.textContent.trim();
        input.addEventListener('change', function () {
            label.firstChild.textContent = input.files.length ? input.files[0].name : defaultText;
        });
    })();
</script>
