<a href="/admin/messages" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    Inbox
</a>
<h1 class="text-2xl font-bold text-gray-900 mt-2 mb-6">Sent messages</h1>

<form method="GET" action="/admin/messages/sent" class="flex flex-wrap items-end gap-3 mb-5">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by subject or message&hellip;"
               class="border border-gray-200 rounded-lg px-3.5 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
    </div>
    <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Search</button>
    <?php if ($search): ?>
        <a href="/admin/messages/sent" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
    <?php foreach ($items as $item): ?>
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-50 last:border-0">
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-gray-400 text-sm">To</span>
                    <span class="font-medium text-gray-900"><?= e($item['recipient_name']) ?></span>
                    <span class="text-gray-400">&mdash;</span>
                    <span class="text-gray-700 truncate"><?= e($item['subject']) ?></span>
                </div>
                <div class="mt-1 text-xs text-gray-400 truncate max-w-xl"><?= e(mb_strimwidth(strip_tags($item['body']), 0, 140, '…')) ?></div>
            </div>
            <div class="text-xs text-gray-400 flex-shrink-0"><?= e(format_date($item['created_at'], 'd M Y, g:ia')) ?></div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <div class="px-5 py-12 text-center">
            <?php if ($search): ?>
                <p class="text-sm text-gray-500">No messages match your search.</p>
                <a href="/admin/messages/sent" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Clear search</a>
            <?php else: ?>
                <p class="text-sm text-gray-500">You haven't sent any messages yet.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/partials/admin-pagination.php'; ?>
