<a href="/admin/messages" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    Inbox
</a>
<h1 class="text-2xl font-bold text-gray-900 mt-2 mb-6"><?= e($message['subject']) ?></h1>

<div class="bg-white border border-gray-100 rounded-xl shadow-card p-6 max-w-3xl">
    <dl class="grid grid-cols-2 gap-4 text-sm mb-6">
        <div>
            <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">From</dt>
            <dd class="font-medium text-gray-900"><?= e($message['sender_name']) ?></dd>
        </div>
        <div>
            <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">Received</dt>
            <dd class="font-medium text-gray-900"><?= e(format_date($message['created_at'], 'd M Y, g:ia')) ?></dd>
        </div>
    </dl>
    <div class="border-t border-gray-100 pt-4">
        <dd class="text-gray-700 whitespace-pre-line leading-relaxed"><?= e($message['body']) ?></dd>
    </div>
</div>

<form method="POST" action="/admin/messages/<?= (int) $message['id'] ?>/delete" class="max-w-3xl mt-4" onsubmit="return confirm('Delete this message?');">
    <?= \App\Core\Csrf::field() ?>
    <button type="submit" class="text-red-600 text-sm font-medium">Delete message</button>
</form>
