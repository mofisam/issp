<?php if (($page['status'] ?? 'published') !== 'published'): ?>
    <div class="bg-issp-orange-50 text-issp-orange-700 text-sm text-center py-2.5 px-4">
        Preview mode &mdash; this page is <strong><?= e(str_replace('_', ' ', $page['status'])) ?></strong> and is not visible to the public yet.
    </div>
<?php endif; ?>
<article class="max-w-4xl mx-auto px-6 py-16 prose-issp">
    <h1 class="text-3xl font-bold text-issp-green-700 mb-6"><?= e($page['title']) ?></h1>
    <?php if (!empty($page['featured_image_url'])): ?>
        <img src="<?= e($page['featured_image_url']) ?>" alt="<?= e($page['title']) ?>" class="w-full rounded-xl mb-8 object-cover max-h-[420px]">
    <?php endif; ?>
    <div class="text-gray-700 leading-relaxed">
        <?= $page['body_content'] ?>
    </div>
</article>
