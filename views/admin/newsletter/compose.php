<div class="mb-6">
    <a href="/admin/newsletter" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        Newsletter subscribers
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">Email subscribers</h1>
    <p class="text-sm text-gray-500 mt-1">Sent by SMTP to everyone currently subscribed &mdash; configured via <code class="font-mono text-xs">.env</code>.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <form method="POST" action="/admin/newsletter/send" class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-5">
        <?= \App\Core\Csrf::field() ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
            <input name="subject" required maxlength="200" value="<?= old('subject') ?>" placeholder="e.g. ISSP Quarterly Update - October 2026"
                   class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
            <textarea name="message" required rows="12" placeholder="Write your update here. Line breaks are preserved."
                      class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500"><?= old('message') ?></textarea>
            <p class="mt-1.5 text-xs text-gray-400">Sent as plain text wrapped in a simple HTML layout &mdash; no formatting toolbar needed.</p>
        </div>
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" <?= $recipientCount === 0 ? 'disabled' : '' ?>
                    class="inline-flex items-center gap-2 bg-issp-green-700 hover:bg-issp-green-600 disabled:opacity-40 disabled:cursor-not-allowed text-white px-5 py-2.5 rounded-lg text-sm font-medium"
                    onclick="return confirm('Send this email to <?= (int) $recipientCount ?> subscriber<?= $recipientCount === 1 ? '' : 's' ?>?');">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M3.5 7l8.5 6 8.5-6"/></svg>
                Send to <?= (int) $recipientCount ?> subscriber<?= $recipientCount === 1 ? '' : 's' ?>
            </button>
            <a href="/admin/newsletter" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6 h-fit">
        <h2 class="text-sm font-semibold text-gray-900 mb-3">Before you send</h2>
        <ul class="space-y-2.5 text-sm text-gray-600">
            <li class="flex gap-2">
                <svg viewBox="0 0 24 24" class="h-4 w-4 mt-0.5 flex-shrink-0 text-issp-green-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.5l2 2 4.5-5M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/></svg>
                Goes to <strong><?= (int) $recipientCount ?></strong> currently subscribed <?= $recipientCount === 1 ? 'address' : 'addresses' ?> &mdash; unsubscribed contacts are skipped automatically.
            </li>
            <li class="flex gap-2">
                <svg viewBox="0 0 24 24" class="h-4 w-4 mt-0.5 flex-shrink-0 text-issp-green-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.5l2 2 4.5-5M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/></svg>
                Delivered over the SMTP server configured in <code class="font-mono text-xs">MAIL_HOST</code> / <code class="font-mono text-xs">MAIL_USERNAME</code>.
            </li>
            <li class="flex gap-2">
                <svg viewBox="0 0 24 24" class="h-4 w-4 mt-0.5 flex-shrink-0 text-issp-green-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.5l2 2 4.5-5M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/></svg>
                This can't be undone once sent &mdash; there's no draft or schedule queue.
            </li>
        </ul>
        <?php if ($recipientCount === 0): ?>
            <p class="mt-4 text-xs text-issp-orange-700 bg-issp-orange-50 rounded-lg px-3 py-2">No subscribed recipients yet, so sending is disabled.</p>
        <?php endif; ?>
    </div>
</div>
