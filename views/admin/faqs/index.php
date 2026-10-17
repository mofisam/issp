<?php use App\Core\Auth; ?>
<div class="flex items-start justify-between gap-4 flex-wrap mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">FAQs</h1>
        <p class="text-sm text-gray-500 mt-1">Shown on the homepage, in the order listed below.</p>
    </div>
    <a href="/admin/faqs/create" class="inline-flex items-center gap-2 bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14 M5 12h14"/></svg>
        Add FAQ
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
    <?php foreach ($items as $i => $item): ?>
        <div class="flex items-start gap-4 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
            <div class="flex flex-col gap-1 pt-0.5 flex-shrink-0">
                <form method="POST" action="/admin/faqs/<?= (int) $item['id'] ?>/move-up">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" <?= $i === 0 ? 'disabled' : '' ?> class="h-6 w-6 flex items-center justify-center rounded border border-gray-200 text-gray-500 hover:border-issp-green-300 disabled:opacity-30 disabled:hover:border-gray-200">
                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5 M5 12l7-7 7 7"/></svg>
                    </button>
                </form>
                <form method="POST" action="/admin/faqs/<?= (int) $item['id'] ?>/move-down">
                    <?= \App\Core\Csrf::field() ?>
                    <button type="submit" <?= $i === count($items) - 1 ? 'disabled' : '' ?> class="h-6 w-6 flex items-center justify-center rounded border border-gray-200 text-gray-500 hover:border-issp-green-300 disabled:opacity-30 disabled:hover:border-gray-200">
                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14 M5 12l7 7 7-7"/></svg>
                    </button>
                </form>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <h2 class="font-medium text-gray-900"><?= e($item['question']) ?></h2>
                    <?php if (!$item['is_published']): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-500">Hidden</span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-gray-500 mt-1 line-clamp-2"><?= e($item['answer']) ?></p>
            </div>

            <div class="flex items-center gap-4 text-sm flex-shrink-0 pt-0.5">
                <a href="/admin/faqs/<?= (int) $item['id'] ?>/edit" class="text-issp-green-700 font-medium">Edit</a>
                <?php if (Auth::hasRole('super_admin', 'admin')): ?>
                    <form method="POST" action="/admin/faqs/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('Delete this FAQ?');">
                        <?= \App\Core\Csrf::field() ?>
                        <button type="submit" class="text-red-600 font-medium">Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <p class="px-5 py-10 text-center text-sm text-gray-500">No FAQs yet &mdash; add the first one.</p>
    <?php endif; ?>
</div>
