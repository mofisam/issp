<h1 class="text-2xl font-bold mb-6">Contact enquiries</h1>
<p class="text-sm text-gray-500 mb-4">Messages submitted through the website's contact form.</p>

<form method="GET" action="/admin/contact" class="flex flex-wrap items-end gap-3 mb-5">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name, email or subject&hellip;"
               class="border border-gray-200 rounded-lg px-3.5 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Status</label>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            <option value="">All</option>
            <?php foreach ($statuses as $value): ?>
                <option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $value))) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Audience</label>
        <select name="audience" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            <option value="">All</option>
            <?php foreach ($audienceTypes as $value): ?>
                <option value="<?= e($value) ?>" <?= $audience === $value ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $value))) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    <?php if ($search || $status || $audience): ?>
        <a href="/admin/contact" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<div class="bg-white rounded-lg border border-gray-100 divide-y">
    <?php foreach ($items as $item): ?>
        <?php
            $badge = match ($item['status']) {
                'new' => 'bg-issp-orange-100 text-issp-orange-700',
                'in_progress' => 'bg-blue-100 text-blue-700',
                'resolved' => 'bg-green-100 text-green-700',
                'spam' => 'bg-gray-200 text-gray-600',
                default => 'bg-gray-100 text-gray-600',
            };
        ?>
        <a href="/admin/contact/<?= (int) $item['id'] ?>" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50">
            <div>
                <div class="font-medium"><?= e($item['first_name'] . ' ' . $item['last_name']) ?> &mdash; <?= e($item['subject'] ?: 'No subject') ?></div>
                <div class="text-xs text-gray-500"><?= e($item['email']) ?> &middot; <?= e(ucfirst(str_replace('_', ' ', $item['audience_type']))) ?> &middot; <?= e($item['created_at']) ?></div>
            </div>
            <span class="text-xs px-2 py-1 rounded font-medium <?= $badge ?>"><?= e(ucfirst(str_replace('_', ' ', $item['status']))) ?></span>
        </a>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <p class="px-5 py-6 text-sm text-gray-500">
            <?php if ($search || $status || $audience): ?>
                No enquiries match your search or filter. <a href="/admin/contact" class="font-medium text-issp-green-700">Clear search &amp; filters</a>
            <?php else: ?>
                No enquiries yet.
            <?php endif; ?>
        </p>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/partials/admin-pagination.php'; ?>
