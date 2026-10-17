<div class="mb-6">
    <a href="/admin/faqs" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        FAQs
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit FAQ</h1>
</div>

<form method="POST" action="/admin/faqs/<?= (int) $faq['id'] ?>" class="max-w-2xl bg-white rounded-xl border border-gray-100 shadow-card p-6 space-y-5">
    <?= \App\Core\Csrf::field() ?>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Question</label>
        <input name="question" required maxlength="300" value="<?= old('question', $faq['question']) ?>"
               class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Answer</label>
        <textarea name="answer" required rows="5"
                  class="border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500"><?= old('answer', $faq['answer']) ?></textarea>
    </div>
    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_published" value="1" <?= $faq['is_published'] ? 'checked' : '' ?> class="rounded border-gray-300 text-issp-green-600 focus:ring-issp-green-500/30">
        Visible on the public site
    </label>
    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium">Save changes</button>
        <a href="/admin/faqs" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
    </div>
</form>
