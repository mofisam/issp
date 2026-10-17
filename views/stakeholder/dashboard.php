<?php
$typeStyles = ['PDF' => 'bg-red-50 text-red-600', 'DOC' => 'bg-blue-50 text-blue-600', 'IMG' => 'bg-issp-orange-50 text-issp-orange-700', 'FILE' => 'bg-gray-100 text-gray-500'];
?>
<!-- Welcome band -->
<section class="bg-issp-green-900 text-white">
    <div class="max-w-6xl mx-auto px-6 pt-12 pb-10 md:pt-16 md:pb-12">
        <?php if (!empty($organization['name'])): ?>
            <p class="text-sm text-white/60"><?= e($organization['name']) ?></p>
        <?php endif; ?>
        <h1 class="font-serif text-3xl md:text-5xl leading-tight mt-1">Welcome back, <?= e($user['first_name']) ?>.</h1>

        <div class="mt-9 flex flex-wrap gap-x-14 gap-y-6 border-t border-white/15 pt-7">
            <a href="/portal/documents" class="group">
                <div class="font-serif text-4xl"><?= (int) $documentCount ?></div>
                <div class="mt-1 text-sm text-white/60 group-hover:text-white">Documents available to you</div>
            </a>
            <a href="/portal/messages" class="group">
                <div class="font-serif text-4xl <?= $unreadCount > 0 ? 'text-issp-orange-500' : '' ?>"><?= (int) $unreadCount ?></div>
                <div class="mt-1 text-sm text-white/60 group-hover:text-white">Unread <?= $unreadCount === 1 ? 'message' : 'messages' ?></div>
            </a>
            <div>
                <div class="font-serif text-4xl"><?= (int) $downloadCount ?></div>
                <div class="mt-1 text-sm text-white/60">Documents you've downloaded</div>
            </div>
        </div>
    </div>
</section>

<div class="max-w-6xl mx-auto px-6 py-10 md:py-12 grid lg:grid-cols-5 gap-8">
    <!-- Recent documents -->
    <section class="lg:col-span-3 min-w-0">
        <div class="flex items-baseline justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Recent documents</h2>
            <a href="/portal/documents" class="text-sm font-medium text-issp-green-700 hover:underline">View all</a>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
            <?php foreach ($recentDocuments as $doc): ?>
                <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-50 last:border-0">
                    <div class="h-10 w-10 rounded-lg flex items-center justify-center text-[11px] font-semibold flex-shrink-0 <?= $typeStyles[$doc['file_type']] ?? $typeStyles['FILE'] ?>"><?= e($doc['file_type']) ?></div>
                    <div class="min-w-0 flex-1">
                        <div class="font-medium text-gray-900 truncate"><?= e($doc['title']) ?></div>
                        <div class="mt-0.5 text-xs text-gray-400 truncate">
                            <?= e(implode(' · ', array_filter([$doc['category_name'] ?? null, $doc['publication_year'] ?? null, $doc['file_size'] ?? null]))) ?>
                        </div>
                    </div>
                    <a href="/portal/documents/<?= (int) $doc['id'] ?>/download" class="text-sm font-medium text-issp-green-700 border border-gray-200 hover:border-issp-green-500 rounded-lg px-3 py-1.5 flex-shrink-0">Download</a>
                </div>
            <?php endforeach; ?>
            <?php if (empty($recentDocuments)): ?>
                <div class="px-5 py-12 text-center">
                    <p class="text-sm text-gray-500">No documents have been shared with your organization yet.</p>
                    <p class="text-xs text-gray-400 mt-1">New documents will appear here as soon as the ISSP team publishes them.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Messages + help -->
    <aside class="lg:col-span-2 space-y-8 min-w-0">
        <section>
            <div class="flex items-baseline justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Latest messages</h2>
                <a href="/portal/messages" class="text-sm font-medium text-issp-green-700 hover:underline">Open inbox</a>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
                <?php foreach ($recentMessages as $msg): ?>
                    <?php $unread = $msg['read_at'] === null; ?>
                    <a href="/portal/messages/<?= (int) $msg['id'] ?>" class="block px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-2 min-w-0">
                                <?php if ($unread): ?><span class="h-2 w-2 rounded-full bg-issp-orange-500 flex-shrink-0"></span><?php endif; ?>
                                <span class="text-sm truncate <?= $unread ? 'font-semibold text-gray-900' : 'font-medium text-gray-700' ?>"><?= e($msg['subject']) ?></span>
                            </span>
                            <span class="text-xs text-gray-400 flex-shrink-0"><?= e(format_date($msg['created_at'], 'd M')) ?></span>
                        </div>
                        <div class="mt-1 text-xs text-gray-400 truncate">From <?= e($msg['sender_name']) ?></div>
                    </a>
                <?php endforeach; ?>
                <?php if (empty($recentMessages)): ?>
                    <p class="px-5 py-10 text-center text-sm text-gray-500">Your inbox is empty.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="rounded-xl bg-issp-green-50 px-5 py-5">
            <h2 class="font-semibold text-gray-900">Need something?</h2>
            <p class="mt-1.5 text-sm text-gray-600 leading-relaxed">If a document you expected isn't listed, or you need access changed, the programme team can help.</p>
            <?php $helpEmail = \App\Models\SiteSetting::get('contact_email', 'project@issprogram.com'); ?>
            <a href="mailto:<?= e($helpEmail) ?>" class="mt-3 inline-block text-sm font-medium text-issp-green-700 hover:underline"><?= e($helpEmail) ?></a>
        </section>
    </aside>
</div>
