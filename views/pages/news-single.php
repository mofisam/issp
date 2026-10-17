<?php if (($post['status'] ?? 'published') !== 'published'): ?>
    <div class="bg-issp-orange-50 text-issp-orange-700 text-sm text-center py-2.5 px-4">
        Preview mode &mdash; this article is <strong><?= e(str_replace('_', ' ', $post['status'])) ?></strong> and is not visible to the public yet.
    </div>
<?php endif; ?>
<style>
    .article-body { font-size: 1.0625rem; line-height: 1.8; color: #374151; }
    .article-body > * + * { margin-top: 1.25em; }
    .article-body h2 { font-family: 'Newsreader', Georgia, serif; font-size: 1.65rem; line-height: 1.25; color: #122F21; margin-top: 2em; }
    .article-body h3 { font-weight: 600; font-size: 1.2rem; color: #122F21; margin-top: 1.75em; }
    .article-body a { color: #1F5C3D; text-decoration: underline; }
    .article-body ul, .article-body ol { padding-left: 1.4em; }
    .article-body ul { list-style: disc; } .article-body ol { list-style: decimal; }
    .article-body li + li { margin-top: .4em; }
    .article-body blockquote { border-left: 3px solid #E8841F; padding-left: 1.1em; font-family: 'Newsreader', Georgia, serif; font-size: 1.25rem; color: #1f2937; }
    .article-body img { max-width: 100%; height: auto; border-radius: .75rem; }
</style>
<article class="px-6 pt-12 pb-10">
    <div class="max-w-3xl mx-auto">
        <a href="/news" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">&larr; All news</a>
        <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
            <?php if (!empty($post['category_name'])): ?><span class="font-medium text-issp-orange-600"><?= e($post['category_name']) ?></span><span aria-hidden="true">&middot;</span><?php endif; ?>
            <span><?= e(format_date($post['published_at']) ?: 'Not yet published') ?></span>
            <span aria-hidden="true">&middot;</span>
            <span><?= (int) $post['read_minutes'] ?> min read</span>
        </div>
        <h1 class="font-serif text-4xl md:text-5xl text-issp-green-800 leading-tight mt-3"><?= e($post['title']) ?></h1>
        <?php if (!empty($post['excerpt'])): ?><p class="text-lg text-gray-500 mt-4 leading-relaxed"><?= e($post['excerpt']) ?></p><?php endif; ?>
    </div>

    <?php if (!empty($post['image_url'])): ?>
        <div class="max-w-5xl mx-auto mt-10">
            <img src="<?= e($post['image_url']) ?>" alt="<?= e($post['title']) ?>" class="w-full rounded-2xl object-cover max-h-[520px]">
        </div>
    <?php endif; ?>

    <div class="max-w-3xl mx-auto mt-10">
        <div class="article-body"><?= $post['body_content'] ?></div>

        <div class="mt-12 pt-6 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <a href="/news" class="text-sm font-medium text-issp-green-700 hover:underline">&larr; Back to news</a>
            <button type="button" id="copy-link" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 border border-gray-200 hover:border-issp-green-500 rounded-lg px-3.5 py-2">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 17H7a5 5 0 0 1 0-10h2M15 7h2a5 5 0 0 1 0 10h-2M8 12h8"/></svg>
                <span id="copy-label">Copy link</span>
            </button>
        </div>
    </div>
</article>

<?php if (!empty($more)): ?>
    <section class="bg-gray-50 py-14 px-6">
        <div class="max-w-6xl mx-auto">
            <h2 class="font-serif text-2xl text-gray-900 mb-6">Keep reading</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($more as $m): ?>
                    <a href="/news/<?= e($m['slug']) ?>" class="group block">
                        <div class="aspect-[16/10] rounded-xl bg-issp-green-50 overflow-hidden">
                            <?php if (!empty($m['image_url'])): ?>
                                <img src="<?= e($m['image_url']) ?>" alt="" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <?php endif; ?>
                        </div>
                        <div class="text-xs text-gray-400 mt-3"><?= e(format_date($m['published_at'])) ?></div>
                        <h3 class="font-serif text-lg text-gray-900 mt-1 leading-snug group-hover:text-issp-green-700"><?= e($m['title']) ?></h3>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<script>
    document.getElementById('copy-link').addEventListener('click', function () {
        var label = document.getElementById('copy-label');
        (navigator.clipboard ? navigator.clipboard.writeText(location.href) : Promise.reject()).then(function () {
            label.textContent = 'Copied';
        }, function () { label.textContent = 'Press Ctrl+C to copy'; });
        setTimeout(function () { label.textContent = 'Copy link'; }, 1800);
    });
</script>
