<a href="/admin/contact" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    Enquiries
</a>
<h1 class="text-2xl font-bold text-gray-900 mt-2 mb-6"><?= e($submission['subject'] ?: 'No subject') ?></h1>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-gray-100 rounded-xl shadow-card p-6">
            <dl class="grid grid-cols-2 gap-4 text-sm mb-6">
                <div>
                    <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">From</dt>
                    <dd class="font-medium text-gray-900"><?= e($submission['first_name'] . ' ' . $submission['last_name']) ?></dd>
                </div>
                <div>
                    <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">Email</dt>
                    <dd class="font-medium text-gray-900"><?= e($submission['email']) ?></dd>
                </div>
                <div>
                    <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">Phone</dt>
                    <dd class="font-medium text-gray-900"><?= e($submission['phone'] ?? '—') ?></dd>
                </div>
                <div>
                    <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">Audience</dt>
                    <dd class="font-medium text-gray-900"><?= e(ucfirst(str_replace('_', ' ', $submission['audience_type']))) ?></dd>
                </div>
                <div>
                    <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">Received</dt>
                    <dd class="font-medium text-gray-900"><?= e(format_date($submission['created_at'], 'd M Y, g:ia')) ?></dd>
                </div>
            </dl>
            <div class="border-t border-gray-100 pt-4">
                <dt class="text-gray-400 text-xs uppercase tracking-wide mb-2">Message</dt>
                <dd class="text-gray-700 whitespace-pre-line leading-relaxed"><?= e($submission['message']) ?></dd>
            </div>
        </div>

        <!-- Reply by email -->
        <div class="bg-white border border-gray-100 rounded-xl shadow-card p-6">
            <h2 class="font-semibold text-gray-900 mb-1">Reply by email</h2>
            <p class="text-sm text-gray-500 mb-4">Send email directly to <strong><?= e($submission['email']) ?></strong>.</p>
            <form method="POST" action="/admin/contact/<?= (int) $submission['id'] ?>/reply" class="space-y-4">
                <?= \App\Core\Csrf::field() ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
                    <input name="subject" required maxlength="200"
                           value="<?= e('Re: ' . ($submission['subject'] ?: 'Your enquiry to ISSP')) ?>"
                           class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
                    <textarea name="message" required rows="6" placeholder="Write your reply here..."
                              class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500"></textarea>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M3.5 7l8.5 6 8.5-6"/></svg>
                    Send reply
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white border border-gray-100 rounded-xl shadow-card p-6 space-y-4 h-fit">
        <form method="POST" action="/admin/contact/<?= (int) $submission['id'] ?>/status">
            <?= \App\Core\Csrf::field() ?>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
            <select name="status" class="border border-gray-200 rounded-lg px-3 py-2.5 w-full mb-3 text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500" onchange="this.form.submit()">
                <?php foreach ($statuses as $status): ?>
                    <option value="<?= $status ?>" <?= $submission['status'] === $status ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $status)) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium w-full">Update status</button>
        </form>
        <form method="POST" action="/admin/contact/<?= (int) $submission['id'] ?>/delete" onsubmit="return confirm('Delete this enquiry?');">
            <?= \App\Core\Csrf::field() ?>
            <button type="submit" class="text-red-600 text-sm font-medium w-full text-left">Delete enquiry</button>
        </form>
    </div>
</div>
