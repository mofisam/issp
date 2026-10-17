<?php
/**
 * Shared form markup for create.php and edit.php.
 * Expects: $formAction, $organization (array), $isEdit (bool).
 */
$inputCls = 'border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500';
$typeOptions = ['insurer' => 'Insurer', 'regulator' => 'Regulator', 'partner' => 'Partner', 'donor' => 'Donor', 'government' => 'Government', 'other' => 'Other'];
?>
<form method="POST" action="<?= e($formAction) ?>" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <?= \App\Core\Csrf::field() ?>

    <!-- Main content -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Name</label>
                <input name="name" required value="<?= old('name', $organization['name'] ?? '') ?>" class="<?= $inputCls ?>">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                    <select name="organization_type" class="<?= $inputCls ?>">
                        <?php foreach ($typeOptions as $value => $label): ?>
                            <option value="<?= $value ?>" <?= (($organization['organization_type'] ?? 'other') === $value) ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Registration number</label>
                    <input name="registration_number" value="<?= old('registration_number', $organization['registration_number'] ?? '') ?>" class="<?= $inputCls ?>">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Website URL</label>
                <input name="website_url" value="<?= old('website_url', $organization['website_url'] ?? '') ?>" placeholder="https://..." class="<?= $inputCls ?>">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact email</label>
                    <input type="email" name="contact_email" value="<?= old('contact_email', $organization['contact_email'] ?? '') ?>" class="<?= $inputCls ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact phone</label>
                    <input name="contact_phone" value="<?= old('contact_phone', $organization['contact_phone'] ?? '') ?>" class="<?= $inputCls ?>">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                <input name="address" value="<?= old('address', $organization['address'] ?? '') ?>" class="<?= $inputCls ?>">
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Status</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Country</label>
                <input name="country" value="<?= old('country', $organization['country'] ?? 'Nigeria') ?>" class="<?= $inputCls ?>">
            </div>
            <label class="flex items-start gap-3 border border-gray-200 rounded-lg px-4 py-3 cursor-pointer hover:border-issp-green-300">
                <input type="checkbox" name="is_active" value="1" class="mt-0.5" <?= !$isEdit || !empty($organization['is_active']) ? 'checked' : '' ?>>
                <span>
                    <span class="block text-sm font-medium text-gray-800">Active</span>
                    <span class="block text-xs text-gray-400">Inactive organizations can't have new staff accounts linked to them.</span>
                </span>
            </label>
            <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
                <?= $isEdit ? 'Save changes' : 'Add organization' ?>
            </button>
        </div>
    </div>
</form>
