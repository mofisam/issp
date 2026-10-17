<?php use App\Core\Auth; ?>
<div class="flex items-start justify-between gap-4 flex-wrap mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Newsletter subscribers</h1>
        <p class="text-sm text-gray-500 mt-1">Everyone who has signed up for updates via the website footer or event forms.</p>
    </div>
    <?php if (Auth::hasRole('super_admin', 'admin')): ?>
        <a href="/admin/newsletter/compose" class="inline-flex items-center gap-2 bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M3.5 7l8.5 6 8.5-6"/></svg>
            Email subscribers
        </a>
    <?php endif; ?>
</div>

<form method="GET" action="/admin/newsletter" class="flex flex-wrap items-end gap-3 mb-5">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name or email&hellip;"
               class="border border-gray-200 rounded-lg px-3.5 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
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
    <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    <?php if ($search || $status): ?>
        <a href="/admin/newsletter" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<div class="bg-white rounded-lg border border-gray-100 divide-y">
    <?php foreach ($items as $item): ?>
        <div class="flex items-center justify-between px-5 py-3">
            <div>
                <div class="font-medium"><?= e($item['full_name'] ?: $item['email']) ?></div>
                <div class="text-xs text-gray-500">
                    <?= e($item['email']) ?> &middot; <?= e(ucfirst($item['status'])) ?>
                    <?php if (!empty($item['source'])): ?> &middot; via <?= e($item['source']) ?><?php endif; ?>
                </div>
            </div>
            <div class="space-x-3 text-sm">
                <form method="POST" action="/admin/newsletter/<?= (int) $item['id'] ?>/toggle-status" class="inline">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="text-issp-green-700 font-medium">
                        <?= $item['status'] === 'unsubscribed' ? 'Re-subscribe' : 'Unsubscribe' ?>
                    </button>
                </form>
                <form method="POST" action="/admin/newsletter/<?= (int) $item['id'] ?>/delete" class="inline" onsubmit="return confirm('Remove this subscriber?');">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" class="text-red-600 font-medium">Delete</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <p class="px-5 py-6 text-sm text-gray-500">
            <?php if ($search || $status): ?>
                No subscribers match your search or filter. <a href="/admin/newsletter" class="font-medium text-issp-green-700">Clear search &amp; filters</a>
            <?php else: ?>
                No subscribers yet.
            <?php endif; ?>
        </p>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/partials/admin-pagination.php'; ?>
