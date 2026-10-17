<div class="flex items-center justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Users &amp; Roles</h1>
        <p class="text-sm text-gray-500 mt-1">Staff and stakeholder accounts with access to this admin area or the partner portal.</p>
    </div>
    <a href="/admin/users/create" class="inline-flex items-center gap-2 bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium shadow-card flex-shrink-0">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        Add user
    </a>
</div>

<form method="GET" action="/admin/users" class="flex flex-wrap items-end gap-3 mb-5">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name or email&hellip;"
               class="border border-gray-200 rounded-lg px-3.5 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Type</label>
        <select name="user_type" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            <option value="">All types</option>
            <?php foreach ($userTypes as $value): ?>
                <option value="<?= e($value) ?>" <?= $userType === $value ? 'selected' : '' ?>><?= e(ucfirst($value)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Status</label>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            <option value="">All</option>
            <?php foreach ($statuses as $value): ?>
                <option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= e(ucfirst($value)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Role</label>
        <select name="role" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            <option value="">All roles</option>
            <?php foreach ($roles as $r): ?>
                <option value="<?= e($r['name']) ?>" <?= $role === $r['name'] ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $r['name']))) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    <?php if ($search || $userType || $status || $role): ?>
        <a href="/admin/users" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
    <?php foreach ($items as $item): ?>
        <?php
            $isSelf   = (int) $item['id'] === (int) \App\Core\Auth::user()['id'];
            $initials = strtoupper(mb_substr($item['first_name'], 0, 1) . mb_substr($item['last_name'], 0, 1));
        ?>
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
            <div class="flex items-center gap-4 min-w-0">
                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-issp-green-50 text-issp-green-700 flex items-center justify-center text-sm font-semibold"><?= e($initials) ?></div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gray-900"><?= e($item['first_name'] . ' ' . $item['last_name']) ?></span>
                        <?php if ($isSelf): ?><span class="text-[11px] text-gray-400">(you)</span><?php endif; ?>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full <?= $item['status'] === 'active' ? 'bg-issp-green-50 text-issp-green-700' : 'bg-gray-100 text-gray-500' ?>"><?= e(ucfirst($item['status'])) ?></span>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-blue-50 text-blue-600"><?= e(ucfirst($item['user_type'])) ?></span>
                    </div>
                    <div class="mt-1 flex items-center gap-2 text-xs text-gray-400 flex-wrap">
                        <span><?= e($item['email']) ?></span>
                        <?php if (!empty($item['role_names'])): ?>
                            <span>&middot;</span>
                            <?php foreach ($item['role_names'] as $role): ?>
                                <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-500"><?= e(ucfirst(str_replace('_', ' ', $role))) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-4 text-sm flex-shrink-0">
                <a href="/admin/users/<?= (int) $item['id'] ?>/edit" class="text-issp-green-700 font-medium">Edit</a>
                <?php if (!$isSelf): ?>
                    <form method="POST" action="/admin/users/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('Deactivate this user?');">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="text-red-600 font-medium">Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <div class="px-5 py-12 text-center">
            <?php if ($search || $userType || $status || $role): ?>
                <p class="text-sm text-gray-500">No users match your search or filter.</p>
                <a href="/admin/users" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Clear search &amp; filters</a>
            <?php else: ?>
                <p class="text-sm text-gray-500">No users yet.</p>
                <a href="/admin/users/create" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Add your first user &rarr;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/partials/admin-pagination.php'; ?>
