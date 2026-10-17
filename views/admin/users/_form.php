<?php
/**
 * Shared form markup for create.php and edit.php.
 * Expects: $formAction, $editUser (array), $roles, $organizations,
 * $assignedRoles (int[]), $isEdit (bool).
 */
$inputCls   = 'border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500';
$userType   = $editUser['user_type'] ?? 'staff';
$assignedRoles = $assignedRoles ?? [];
?>
<form method="POST" action="<?= e($formAction) ?>" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <?= \App\Core\Csrf::field() ?>

    <!-- Main content -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-5">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">First name</label>
                    <input name="first_name" required value="<?= old('first_name', $editUser['first_name'] ?? '') ?>" class="<?= $inputCls ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Last name</label>
                    <input name="last_name" required value="<?= old('last_name', $editUser['last_name'] ?? '') ?>" class="<?= $inputCls ?>">
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" name="email" required value="<?= old('email', $editUser['email'] ?? '') ?>" class="<?= $inputCls ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Job title</label>
                    <input name="job_title" value="<?= old('job_title', (string) ($editUser['job_title'] ?? '')) ?>" class="<?= $inputCls ?>">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5"><?= $isEdit ? 'New password' : 'Password' ?></label>
                <input type="password" name="password" <?= $isEdit ? '' : 'required' ?> minlength="8" class="<?= $inputCls ?>">
                <p class="mt-1.5 text-xs text-gray-400"><?= $isEdit ? 'Leave blank to keep the current password.' : 'Minimum 8 characters.' ?></p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Roles</h2>
            <p class="text-xs text-gray-400 -mt-2">Controls what this user can see and do in the admin area.</p>
            <div class="grid sm:grid-cols-2 gap-2">
                <?php foreach ($roles as $role): ?>
                    <label class="flex items-center gap-2.5 border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm cursor-pointer hover:border-issp-green-300">
                        <input type="checkbox" name="roles[]" value="<?= (int) $role['id'] ?>" <?= in_array((int) $role['id'], $assignedRoles, true) ? 'checked' : '' ?>>
                        <?= e(ucfirst(str_replace('_', ' ', $role['name']))) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Account</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Account type</label>
                <select name="user_type" id="user_type" class="<?= $inputCls ?>" onchange="document.getElementById('org-field').classList.toggle('hidden', this.value !== 'stakeholder')">
                    <option value="staff" <?= $userType === 'staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="stakeholder" <?= $userType === 'stakeholder' ? 'selected' : '' ?>>Stakeholder</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select name="status" class="<?= $inputCls ?>">
                    <option value="active" <?= ($editUser['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($editUser['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div id="org-field" class="<?= $userType === 'stakeholder' ? '' : 'hidden' ?>">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Organization</label>
                <select name="organization_id" class="<?= $inputCls ?>">
                    <option value="">&mdash; None &mdash;</option>
                    <?php foreach ($organizations as $org): ?>
                        <option value="<?= (int) $org['id'] ?>" <?= ((int) ($editUser['organization_id'] ?? 0) === (int) $org['id']) ? 'selected' : '' ?>><?= e($org['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
                <?= $isEdit ? 'Save changes' : 'Add user' ?>
            </button>
        </div>
    </div>
</form>
