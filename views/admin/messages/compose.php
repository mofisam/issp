<?php $old = $_SESSION['_old'] ?? []; $errors = $_SESSION['_errors'] ?? []; unset($_SESSION['_old'], $_SESSION['_errors']); ?>
<a href="/admin/messages" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    Inbox
</a>
<h1 class="text-2xl font-bold text-gray-900 mt-2 mb-6">New message</h1>

<form method="POST" action="/admin/messages" class="bg-white border border-gray-100 rounded-xl shadow-card p-6 max-w-2xl space-y-5">
    <?= \App\Core\Csrf::field() ?>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Recipients</label>
        <select name="recipient_id[]" multiple size="8" required
                class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
            <?php foreach ($recipients as $recipient): ?>
                <?php $selected = in_array((string) $recipient['id'], (array) ($old['recipient_id'] ?? []), true); ?>
                <option value="<?= (int) $recipient['id'] ?>" <?= $selected ? 'selected' : '' ?>>
                    <?= e($recipient['first_name'] . ' ' . $recipient['last_name']) ?> &mdash; <?= e($recipient['email']) ?> (<?= e(ucfirst($recipient['user_type'])) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <p class="text-xs text-gray-400 mt-1.5">Hold Ctrl/Cmd to select multiple recipients.</p>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
        <input name="subject" required maxlength="190" value="<?= e($old['subject'] ?? '') ?>"
               class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
        <?php if (!empty($errors['subject'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['subject']) ?></p><?php endif; ?>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
        <textarea name="body" required rows="8" placeholder="Write your message here&hellip;"
                  class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500"><?= e($old['body'] ?? '') ?></textarea>
        <?php if (!empty($errors['body'])): ?><p class="text-xs text-red-600 mt-1"><?= e($errors['body']) ?></p><?php endif; ?>
    </div>

    <button type="submit" class="inline-flex items-center gap-2 bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M3.5 7l8.5 6 8.5-6"/></svg>
        Send message
    </button>
</form>
