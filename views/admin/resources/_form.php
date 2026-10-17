<?php
/**
 * Shared form markup for create.php and edit.php.
 * Expects: $formAction, $resource (array), $categories, $isEdit (bool), $downloadUrl (string|null),
 * $organizations, $memberCounts, $stakeholderRoles, $grants (access picker).
 */
$visibility = $resource['visibility'] ?? 'public';
$organizations    = $organizations ?? [];
$memberCounts     = $memberCounts ?? [];
$stakeholderRoles = $stakeholderRoles ?? [];
$grants           = $grants ?? ['organizations' => [], 'roles' => []];
$hasGrants        = !empty($grants['organizations']) || !empty($grants['roles']);
$accessMode       = (string) old('access_mode', $hasGrants ? 'selected' : 'all');
$oldOrgs          = isset($_SESSION['_old']['grant_organizations']) ? array_map('intval', (array) $_SESSION['_old']['grant_organizations']) : $grants['organizations'];
$oldRoles         = isset($_SESSION['_old']['grant_roles']) ? array_map('intval', (array) $_SESSION['_old']['grant_roles']) : $grants['roles'];
$orgTypeLabels    = ['insurer' => 'Insurer', 'regulator' => 'Regulator', 'partner' => 'Partner', 'donor' => 'Donor', 'government' => 'Government', 'other' => 'Other'];
$inputCls   = 'border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500';
$visibilityOptions = [
    'public'      => ['label' => 'Public', 'hint' => 'Anyone can download, no login required.'],
    'stakeholder' => ['label' => 'Stakeholder', 'hint' => 'Visible only to logged-in stakeholders.'],
    'private'     => ['label' => 'Private', 'hint' => 'Staff only — never shown publicly.'],
];
?>
<?php if (!empty($_SESSION['_errors'])): ?>
    <div class="lg:col-span-3 bg-red-50 text-red-700 text-sm rounded-lg p-4 mb-2">
        <?php foreach ($_SESSION['_errors'] as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
    </div>
    <?php unset($_SESSION['_errors']); ?>
<?php endif; ?>

<form method="POST" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <?= \App\Core\Csrf::field() ?>

    <!-- Main content -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title</label>
                <input name="title" required maxlength="220" value="<?= old('title', $resource['title'] ?? '') ?>"
                       placeholder="e.g. 2025 Insurance Sector Annual Report" class="<?= $inputCls ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="4" placeholder="A short summary shown alongside the document"
                          class="<?= $inputCls ?>"><?= e($resource['description'] ?? '') ?></textarea>
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <a href="/admin/categories/resources" class="text-xs font-medium text-issp-green-700 hover:underline">Manage categories</a>
                </div>
                <select name="category_id" class="<?= $inputCls ?>">
                    <option value="">No category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= ((int) ($resource['category_id'] ?? 0) === (int) $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-3">
            <h2 class="text-sm font-semibold text-gray-900"><?= $isEdit ? 'Replace file' : 'File' ?></h2>
            <label for="resource-file" class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-200 rounded-lg py-8 px-4 text-center cursor-pointer hover:border-issp-green-400 hover:bg-issp-green-50/30 transition-colors">
                <svg viewBox="0 0 24 24" class="h-8 w-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4m0 0 4 4m-4-4-4 4M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
                <span class="text-sm text-gray-600"><span id="resource-file-label">Click to choose a file, or drag one here</span></span>
                <span class="text-xs text-gray-400">PDF, Word (.doc/.docx), or image — up to the server's upload limit</span>
                <input id="resource-file" type="file" name="file" <?= $isEdit ? '' : 'required' ?> accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp" class="hidden">
            </label>
            <?php if ($isEdit): ?>
                <p class="text-xs text-gray-400">Leave blank to keep the current file: <span class="font-medium text-gray-500"><?= e($resource['file_name'] ?? 'current file') ?></span></p>
            <?php endif; ?>
            <script>
                (function () {
                    var input = document.getElementById('resource-file');
                    var label = document.getElementById('resource-file-label');
                    var defaultLabel = label.textContent;
                    input.addEventListener('change', function () {
                        label.textContent = input.files.length ? input.files[0].name : defaultLabel;
                    });
                })();
            </script>
        </div>

        <!-- Access picker (stakeholder visibility only) -->
        <div id="access-card" class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4 <?= $visibility === 'stakeholder' ? '' : 'hidden' ?>">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Who can open this document?</h2>
                <p class="text-xs text-gray-400 mt-0.5">Only applies to stakeholder documents. Stakeholders see it in their portal Documents list.</p>
            </div>
            <div class="grid sm:grid-cols-2 gap-2">
                <label class="flex items-start gap-3 border rounded-lg px-3.5 py-3 cursor-pointer has-[:checked]:border-issp-green-500 has-[:checked]:bg-issp-green-50/40 border-gray-200">
                    <input type="radio" name="access_mode" value="all" class="mt-0.5" <?= $accessMode !== 'selected' ? 'checked' : '' ?>>
                    <span><span class="block text-sm font-medium text-gray-800">All stakeholders</span><span class="block text-xs text-gray-400">Any signed-in stakeholder account.</span></span>
                </label>
                <label class="flex items-start gap-3 border rounded-lg px-3.5 py-3 cursor-pointer has-[:checked]:border-issp-green-500 has-[:checked]:bg-issp-green-50/40 border-gray-200">
                    <input type="radio" name="access_mode" value="selected" class="mt-0.5" <?= $accessMode === 'selected' ? 'checked' : '' ?>>
                    <span><span class="block text-sm font-medium text-gray-800">Only selected</span><span class="block text-xs text-gray-400">Choose organizations or stakeholder groups.</span></span>
                </label>
            </div>

            <div id="access-selected" class="space-y-5 <?= $accessMode === 'selected' ? '' : 'hidden' ?>">
                <div>
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <h3 class="text-sm font-medium text-gray-700">Organizations <span id="org-count" class="ml-1 text-xs font-normal text-gray-400"></span></h3>
                        <div class="flex items-center gap-3 text-xs">
                            <button type="button" id="org-all" class="font-medium text-issp-green-700 hover:underline">Select all</button>
                            <button type="button" id="org-none" class="font-medium text-gray-500 hover:underline">Clear</button>
                        </div>
                    </div>
                    <?php if ($organizations): ?>
                        <input type="search" id="org-search" placeholder="Search organizations&hellip;" class="<?= $inputCls ?> mb-2">
                        <div id="org-list" class="max-h-64 overflow-y-auto border border-gray-100 rounded-lg divide-y divide-gray-50">
                            <?php foreach ($organizations as $org): ?>
                                <?php $oid = (int) $org['id']; $members = $memberCounts[$oid] ?? 0; ?>
                                <label class="org-row flex items-center gap-3 px-3.5 py-2.5 cursor-pointer hover:bg-gray-50" data-name="<?= e(strtolower($org['name'])) ?>">
                                    <input type="checkbox" name="grant_organizations[]" value="<?= $oid ?>" class="org-box" <?= in_array($oid, $oldOrgs, true) ? 'checked' : '' ?>>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm text-gray-800 truncate"><?= e($org['name']) ?></span>
                                        <span class="block text-xs text-gray-400"><?= e($orgTypeLabels[$org['organization_type']] ?? 'Other') ?> &middot; <?= $members ?> <?= $members === 1 ? 'user' : 'users' ?><?= empty($org['is_active']) ? ' &middot; inactive' : '' ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                            <p id="org-empty" class="hidden px-3.5 py-4 text-sm text-gray-400">No organization matches.</p>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 bg-gray-50 rounded-lg px-4 py-3">No organizations yet. Create one and attach stakeholder users to it, then come back here.</p>
                    <?php endif; ?>
                </div>

                <?php if ($stakeholderRoles): ?>
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Stakeholder groups</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($stakeholderRoles as $role): ?>
                                <label class="inline-flex items-center gap-2 border border-gray-200 rounded-full px-3.5 py-1.5 text-sm text-gray-700 cursor-pointer has-[:checked]:border-issp-green-500 has-[:checked]:bg-issp-green-50 has-[:checked]:text-issp-green-700">
                                    <input type="checkbox" name="grant_roles[]" value="<?= (int) $role['id'] ?>" class="role-box" <?= in_array((int) $role['id'], $oldRoles, true) ? 'checked' : '' ?>>
                                    <?= e(preg_replace('/^Stakeholder\s*-\s*/', '', $role['display_name'])) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">A group gives access to every stakeholder user with that role, whatever their organization.</p>
                    </div>
                <?php endif; ?>

                <p id="access-warning" class="hidden text-xs bg-issp-orange-50 text-issp-orange-700 rounded-lg px-3.5 py-2.5">Nothing selected &mdash; with no selection this document stays open to all stakeholders.</p>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-900">Details</h2>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Publication year</label>
                <input type="number" name="publication_year" min="1990" max="2100"
                       value="<?= old('publication_year', (string) ($resource['publication_year'] ?? '')) ?>" class="<?= $inputCls ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Visibility</label>
                <div class="space-y-2">
                    <?php foreach ($visibilityOptions as $value => $opt): ?>
                        <label class="flex items-start gap-3 border rounded-lg px-3.5 py-2.5 cursor-pointer <?= $visibility === $value ? 'border-issp-green-500 bg-issp-green-50/40' : 'border-gray-200 hover:border-issp-green-300' ?>">
                            <input type="radio" name="visibility" value="<?= $value ?>" class="mt-0.5" <?= $visibility === $value ? 'checked' : '' ?>>
                            <span>
                                <span class="block text-sm font-medium text-gray-800"><?= $opt['label'] ?></span>
                                <span class="block text-xs text-gray-400"><?= $opt['hint'] ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
                <?= $isEdit ? 'Save changes' : 'Upload document' ?>
            </button>
        </div>

        <?php if ($isEdit): ?>
            <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-3">
                <h2 class="text-sm font-semibold text-gray-900">Document link</h2>
                <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                    <svg viewBox="0 0 24 24" class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 17H7a5 5 0 0 1 0-10h2M15 7h2a5 5 0 0 1 0 10h-2M8 12h8"/></svg>
                    <input id="live-url" type="text" readonly value="<?= e($downloadUrl) ?>" class="bg-transparent text-xs font-mono text-gray-600 w-full outline-none truncate">
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('live-url').value); this.textContent='Copied';setTimeout(()=>this.textContent='Copy',1200);" class="text-xs font-medium text-issp-green-700 flex-shrink-0">Copy</button>
                </div>
                <a href="<?= e($downloadUrl) ?>" target="_blank" class="block text-center text-xs font-medium bg-issp-green-50 text-issp-green-700 px-3 py-2 rounded-lg"><?= $visibility === 'public' ? 'Download (public link)' : 'Download (requires access)' ?></a>
                <?php if ($visibility !== 'public'): ?>
                    <p class="text-[11px] text-gray-400">This link only works for visitors with <?= $visibility ?> access.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</form>
<script>
(function () {
    var card = document.getElementById('access-card');
    if (!card) return;
    var selectedBox = document.getElementById('access-selected');
    var warn = document.getElementById('access-warning');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.org-row'));
    var boxes = Array.prototype.slice.call(document.querySelectorAll('.org-box'));
    var roleBoxes = Array.prototype.slice.call(document.querySelectorAll('.role-box'));
    var counter = document.getElementById('org-count');

    function refresh() {
        var n = boxes.filter(function (b) { return b.checked; }).length;
        var r = roleBoxes.filter(function (b) { return b.checked; }).length;
        if (counter) counter.textContent = n ? n + ' selected' : '';
        var mode = document.querySelector('input[name="access_mode"]:checked');
        warn.classList.toggle('hidden', !(mode && mode.value === 'selected' && n + r === 0));
    }
    document.querySelectorAll('input[name="visibility"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            card.classList.toggle('hidden', radio.value !== 'stakeholder' || !radio.checked);
            document.querySelectorAll('input[name="visibility"]').forEach(function (x) {
                x.closest('label').className = x.closest('label').className.replace(/border-issp-green-500 bg-issp-green-50\/40|border-gray-200 hover:border-issp-green-300/, x.checked ? 'border-issp-green-500 bg-issp-green-50/40' : 'border-gray-200 hover:border-issp-green-300');
            });
        });
    });
    document.querySelectorAll('input[name="access_mode"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            selectedBox.classList.toggle('hidden', radio.value !== 'selected' || !radio.checked);
            refresh();
        });
    });
    boxes.concat(roleBoxes).forEach(function (b) { b.addEventListener('change', refresh); });
    var search = document.getElementById('org-search');
    if (search) search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase(), shown = 0;
        rows.forEach(function (row) {
            var hit = row.dataset.name.indexOf(q) !== -1;
            row.classList.toggle('hidden', !hit);
            if (hit) shown++;
        });
        document.getElementById('org-empty').classList.toggle('hidden', shown !== 0);
    });
    function setAll(v) { rows.forEach(function (row) { if (!row.classList.contains('hidden')) row.querySelector('input').checked = v; }); refresh(); }
    var all = document.getElementById('org-all'), none = document.getElementById('org-none');
    if (all) all.addEventListener('click', function () { setAll(true); });
    if (none) none.addEventListener('click', function () { setAll(false); });
    refresh();
})();
</script>
