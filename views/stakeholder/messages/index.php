<div class="max-w-6xl mx-auto px-6 py-10 md:py-12">
<div class="mb-7">
    <h1 class="font-serif text-3xl text-gray-900">Messages</h1>
    <p class="text-sm text-gray-500 mt-1.5">Messages sent to you by the ISSP team.</p>
</div>

<form method="GET" action="/portal/messages" class="flex flex-wrap items-end gap-3 mb-5">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
        <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by subject or message&hellip;"
               class="border border-gray-200 rounded-lg px-3.5 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
    </div>
    <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Search</button>
    <?php if ($search): ?>
        <a href="/portal/messages" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
    <?php foreach ($items as $item): ?>
        <?php $unread = $item['read_at'] === null; ?>
        <a href="/portal/messages/<?= (int) $item['id'] ?>" class="flex items-center justify-between gap-3 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <?php if ($unread): ?><span class="h-2 w-2 rounded-full bg-issp-orange-500 flex-shrink-0"></span><?php endif; ?>
                    <span class="<?= $unread ? 'font-semibold text-gray-900' : 'font-medium text-gray-700' ?>"><?= e($item['sender_name']) ?></span>
                    <span class="text-gray-400">&mdash;</span>
                    <span class="<?= $unread ? 'font-semibold text-gray-900' : 'text-gray-600' ?> truncate"><?= e($item['subject']) ?></span>
                </div>
                <div class="mt-1 text-xs text-gray-400 truncate max-w-xl"><?= e(mb_strimwidth(strip_tags($item['body']), 0, 140, '…')) ?></div>
            </div>
            <div class="text-xs text-gray-400 flex-shrink-0"><?= e(format_date($item['created_at'], 'd M Y, g:ia')) ?></div>
        </a>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <div class="px-5 py-12 text-center">
            <?php if ($search): ?>
                <p class="text-sm text-gray-500">No messages match your search.</p>
                <a href="/portal/messages" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Clear search</a>
            <?php else: ?>
                <p class="text-sm text-gray-500">Your inbox is empty.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require BASE_PATH . '/views/partials/admin-pagination.php'; ?>
</div>
