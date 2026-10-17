<?php
$icon = '<svg viewBox="0 0 24 24" class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h13a2 2 0 0 1 2 2v12a1 1 0 0 1-1 1H6a2 2 0 0 1-2-2V5Z M20 9H17a1 1 0 0 0-1 1v10 M7 9h6 M7 12.5h6 M7 16h4"/></svg>';
$activeSlug = $activeCategory['slug'] ?? '';
$isFirstPage = $page === 1;
$lead  = ($isFirstPage && $posts) ? $posts[0] : null;
$rest  = $lead ? array_slice($posts, 1) : $posts;
$pageUrl = static function (int $p) use ($activeSlug): string {
    $q = [];
    if ($activeSlug !== '') { $q['category'] = $activeSlug; }
    if ($p > 1) { $q['page'] = $p; }
    return '/news' . ($q ? '?' . http_build_query($q) : '');
};
$meta = static function (array $post): string {
    return implode(' · ', array_filter([
        $post['category_name'] ?? null,
        format_date($post['published_at']),
        ($post['read_minutes'] ?? 0) . ' min read',
    ]));
};
?>
<section class="max-w-6xl mx-auto px-6 pt-14 pb-16">
    <header class="max-w-2xl">
        <p class="text-sm font-medium text-issp-orange-600">News &amp; updates</p>
        <h1 class="font-serif text-4xl md:text-5xl text-issp-green-800 leading-tight mt-2">Stories from the programme</h1>
        <p class="text-gray-500 mt-3">Announcements, progress reports and insights from across Nigeria's insurance sector.</p>
    </header>

    <?php if ($categories): ?>
        <nav class="mt-8 flex flex-wrap gap-2" aria-label="Filter by category">
            <a href="/news" class="rounded-full border px-4 py-1.5 text-sm font-medium <?= $activeSlug === '' ? 'bg-issp-green-700 border-issp-green-700 text-white' : 'border-gray-200 text-gray-600 hover:border-issp-green-500 hover:text-issp-green-700' ?>">All</a>
            <?php foreach ($categories as $c): ?>
                <a href="/news?category=<?= e($c['slug']) ?>" class="rounded-full border px-4 py-1.5 text-sm font-medium <?= $activeSlug === $c['slug'] ? 'bg-issp-green-700 border-issp-green-700 text-white' : 'border-gray-200 text-gray-600 hover:border-issp-green-500 hover:text-issp-green-700' ?>"><?= e($c['name']) ?></a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <?php if ($lead): ?>
        <a href="/news/<?= e($lead['slug']) ?>" class="group mt-10 grid md:grid-cols-5 gap-0 md:gap-8 rounded-2xl overflow-hidden border border-gray-100 hover:border-issp-green-500 transition-colors">
            <div class="md:col-span-3 aspect-[16/10] md:aspect-auto md:min-h-[320px] bg-issp-green-50 overflow-hidden">
                <?php if (!empty($lead['image_url'])): ?>
                    <img src="<?= e($lead['image_url']) ?>" alt="<?= e($lead['title']) ?>" class="h-full w-full object-cover group-hover:scale-[1.03] transition-transform duration-500">
                <?php else: ?>
                    <div class="h-full w-full flex items-center justify-center text-issp-green-100"><?= str_replace('h-10 w-10', 'h-16 w-16', $icon) ?></div>
                <?php endif; ?>
            </div>
            <div class="md:col-span-2 p-6 md:py-8 md:pl-0 md:pr-8 flex flex-col justify-center">
                <div class="text-xs font-medium text-issp-orange-600 uppercase tracking-wide">Latest</div>
                <h2 class="font-serif text-2xl md:text-3xl text-gray-900 mt-2 leading-snug group-hover:text-issp-green-700"><?= e($lead['title']) ?></h2>
                <?php if (!empty($lead['excerpt'])): ?><p class="text-gray-600 mt-3 line-clamp-4"><?= e($lead['excerpt']) ?></p><?php endif; ?>
                <div class="text-xs text-gray-400 mt-4"><?= e($meta($lead)) ?></div>
                <span class="mt-5 text-sm font-medium text-issp-green-700">Read article &rarr;</span>
            </div>
        </a>
    <?php endif; ?>

    <?php if ($rest): ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 mt-10">
            <?php foreach ($rest as $post): ?>
                <a href="/news/<?= e($post['slug']) ?>" class="group flex flex-col">
                    <div class="aspect-[16/10] rounded-xl bg-issp-green-50 overflow-hidden">
                        <?php if (!empty($post['image_url'])): ?>
                            <img src="<?= e($post['image_url']) ?>" alt="<?= e($post['title']) ?>" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <?php else: ?>
                            <div class="h-full w-full flex items-center justify-center text-issp-green-100"><?= $icon ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-gray-400 mt-4"><?= e($meta($post)) ?></div>
                    <h2 class="font-serif text-xl text-gray-900 mt-1.5 leading-snug group-hover:text-issp-green-700"><?= e($post['title']) ?></h2>
                    <?php if (!empty($post['excerpt'])): ?><p class="text-sm text-gray-600 mt-2 line-clamp-3"><?= e($post['excerpt']) ?></p><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($posts)): ?>
        <div class="mt-12 rounded-2xl bg-gray-50 px-6 py-16 text-center">
            <p class="text-gray-600"><?= $activeCategory ? 'No articles in this category yet.' : 'No articles published yet.' ?></p>
            <?php if ($activeCategory): ?><a href="/news" class="inline-block mt-3 text-sm font-medium text-issp-green-700">View all articles</a><?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($lastPage > 1): ?>
        <nav class="mt-14 flex items-center justify-between border-t border-gray-100 pt-6 text-sm" aria-label="Pagination">
            <span class="text-gray-400">Page <?= (int) $page ?> of <?= (int) $lastPage ?> &middot; <?= (int) $total ?> articles</span>
            <div class="flex items-center gap-2">
                <?php if ($page > 1): ?><a href="<?= e($pageUrl($page - 1)) ?>" rel="prev" class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:border-issp-green-500">&larr; Newer</a><?php endif; ?>
                <?php if ($page < $lastPage): ?><a href="<?= e($pageUrl($page + 1)) ?>" rel="next" class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:border-issp-green-500">Older &rarr;</a><?php endif; ?>
            </div>
        </nav>
    <?php endif; ?>
</section>
