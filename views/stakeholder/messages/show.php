<div class="max-w-3xl mx-auto px-6 py-10 md:py-12">
    <a href="/portal/messages" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        Inbox
    </a>
    <h1 class="font-serif text-3xl text-gray-900 mt-3 mb-6"><?= e($message['subject']) ?></h1>

    <div class="bg-white border border-gray-100 rounded-xl shadow-card p-6 md:p-8">
        <dl class="grid grid-cols-2 gap-4 text-sm pb-5 mb-5 border-b border-gray-100">
            <div>
                <dt class="text-gray-400 text-xs mb-1">From</dt>
                <dd class="font-medium text-gray-900"><?= e($message['sender_name']) ?></dd>
            </div>
            <div>
                <dt class="text-gray-400 text-xs mb-1">Received</dt>
                <dd class="font-medium text-gray-900"><?= e(format_date($message['created_at'], 'd M Y, g:ia')) ?></dd>
            </div>
        </dl>
        <div class="text-gray-700 whitespace-pre-line leading-relaxed"><?= e($message['body']) ?></div>
    </div>

    <form method="POST" action="/portal/messages/<?= (int) $message['id'] ?>/delete" class="mt-4" onsubmit="return confirm('Delete this message?');">
        <?= \App\Core\Csrf::field() ?>
        <button type="submit" class="text-sm font-medium text-gray-400 hover:text-red-600">Delete message</button>
    </form>
</div>
