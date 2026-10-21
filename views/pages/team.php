<?php
$initials = static function (string $name): string {
    $parts = preg_split('/\s+/', trim(preg_replace('/^(Dr|Mr|Mrs|Ms|Prof|Engr|Chief|Alhaji|Hon)\.?\s+/i', '', $name)));
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last);
};
$in  = '<svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M6.94 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM3.2 8.75h3.5V21H3.2V8.75Zm6.2 0h3.35v1.68h.05c.47-.88 1.6-1.8 3.3-1.8 3.53 0 4.18 2.32 4.18 5.35V21h-3.5v-5.47c0-1.3-.02-2.98-1.82-2.98-1.82 0-2.1 1.42-2.1 2.88V21H9.4V8.75Z"/></svg>';
$mail = '<svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M3.5 7l8.5 6 8.5-6"/></svg>';
$avatar = static function (array $m, string $size, string $text) use ($initials): string {
    if (!empty($m['image_url'])) {
        return '<img src="' . e($m['image_url']) . '" alt="' . e($m['full_name']) . '" loading="lazy" class="' . $size . ' object-cover object-top">';
    }
    return '<div class="' . $size . ' bg-issp-green-50 text-issp-green-700 flex items-center justify-center font-serif ' . $text . '" aria-hidden="true">' . e($initials($m['full_name'])) . '</div>';
};
?>
<section class="max-w-6xl mx-auto px-6 pt-14 pb-20">
    <header class="max-w-2xl">
        <p class="text-sm font-medium text-issp-orange-600">Our team</p>
        <h1 class="font-serif text-4xl md:text-4xl text-issp-green-800 leading-tight mt-2">The people behind the programme</h1>
        <p class="text-gray-500 mt-3">Practitioners from across insurance, regulation and development, working together to deepen insurance in Nigeria.</p>
    </header>

    <?php if (count($sections) > 1): ?>
        <nav class="mt-8 flex flex-wrap gap-2" aria-label="Team groups">
            <?php foreach ($sections as $key => $sec): ?>
                <a href="#<?= e($key) ?>" class="rounded-full border border-gray-200 px-4 py-1.5 text-sm font-medium text-gray-600 hover:border-issp-green-500 hover:text-issp-green-700"><?= e($sec['label']) ?> <span class="text-gray-400"><?= count($sec['members']) ?></span></a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <?php foreach ($sections as $key => $sec): ?>
        <div id="<?= e($key) ?>" class="mt-14 scroll-mt-20">
            <div class="flex items-end justify-between gap-4 border-b border-gray-100 pb-3 mb-8">
                <h2 class="font-serif text-2xl md:text-3xl text-gray-900"><?= e($sec['label']) ?></h2>
                <p class="hidden sm:block text-sm text-gray-400"><?= e($sec['hint']) ?></p>
            </div>

            <?php if ($key === 'leadership'): ?>
                <div class="grid md:grid-cols-2 gap-6">
                    <?php foreach ($sec['members'] as $m): ?>
                        <article class="flex gap-5 rounded-2xl border border-gray-100 p-5 hover:border-issp-green-500 transition-colors">
                            <div class="flex-shrink-0 h-32 w-28 sm:h-40 sm:w-32 rounded-xl overflow-hidden"><?= $avatar($m, 'h-full w-full', 'text-4xl') ?></div>
                            <div class="min-w-0 flex flex-col">
                                <h3 class="font-serif text-xl text-gray-900 leading-snug"><?= e($m['full_name']) ?></h3>
                                <p class="text-sm font-medium text-issp-orange-600 mt-0.5"><?= e($m['role_title']) ?></p>
                                <?php if (!empty($m['bio'])): ?>
                                    <p class="text-sm text-gray-600 leading-relaxed mt-3 line-clamp-4"><?= e($m['bio']) ?></p>
                                    <button type="button" class="bio-open mt-2 w-fit text-sm font-medium text-issp-green-700 hover:underline" data-name="<?= e($m['full_name']) ?>" data-role="<?= e($m['role_title']) ?>" data-bio="<?= e($m['bio']) ?>">Read full bio</button>
                                <?php endif; ?>
                                <?php if (!empty($m['linkedin_url']) || !empty($m['email'])): ?>
                                    <div class="mt-auto pt-3 flex gap-2">
                                        <?php if (!empty($m['linkedin_url'])): ?><a href="<?= e($m['linkedin_url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($m['full_name']) ?> on LinkedIn" class="h-8 w-8 rounded-full bg-gray-100 text-gray-500 hover:bg-issp-green-600 hover:text-white flex items-center justify-center transition-colors"><?= $in ?></a><?php endif; ?>
                                        <?php if (!empty($m['email'])): ?><a href="mailto:<?= e($m['email']) ?>" aria-label="Email <?= e($m['full_name']) ?>" class="h-8 w-8 rounded-full bg-gray-100 text-gray-500 hover:bg-issp-green-600 hover:text-white flex items-center justify-center transition-colors"><?= $mail ?></a><?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-x-5 gap-y-8">
                    <?php foreach ($sec['members'] as $m): ?>
                        <article class="group">
                            <div class="aspect-square rounded-xl overflow-hidden"><?= $avatar($m, 'h-full w-full', 'text-4xl') ?></div>
                            <h3 class="font-semibold text-gray-900 mt-3 leading-snug"><?= e($m['full_name']) ?></h3>
                            <p class="text-sm text-issp-orange-600 leading-snug"><?= e($m['role_title']) ?></p>
                            <div class="mt-2 flex items-center gap-3 text-sm">
                                <?php if (!empty($m['bio'])): ?><button type="button" class="bio-open font-medium text-issp-green-700 hover:underline" data-name="<?= e($m['full_name']) ?>" data-role="<?= e($m['role_title']) ?>" data-bio="<?= e($m['bio']) ?>">Bio</button><?php endif; ?>
                                <?php if (!empty($m['linkedin_url'])): ?><a href="<?= e($m['linkedin_url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($m['full_name']) ?> on LinkedIn" class="text-gray-400 hover:text-issp-green-700"><?= $in ?></a><?php endif; ?>
                                <?php if (!empty($m['email'])): ?><a href="mailto:<?= e($m['email']) ?>" aria-label="Email <?= e($m['full_name']) ?>" class="text-gray-400 hover:text-issp-green-700"><?= $mail ?></a><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if (empty($sections)): ?>
        <div class="mt-12 rounded-2xl bg-gray-50 px-6 py-16 text-center text-gray-500">Our team profiles are coming soon.</div>
    <?php endif; ?>
</section>

<dialog id="bio-dialog" class="rounded-2xl p-0 w-[min(92vw,34rem)] backdrop:bg-gray-900/50">
    <div class="p-7">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 id="bio-name" class="font-serif text-2xl text-gray-900"></h2>
                <p id="bio-role" class="text-sm font-medium text-issp-orange-600 mt-0.5"></p>
            </div>
            <button type="button" id="bio-close" aria-label="Close" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
        </div>
        <p id="bio-text" class="mt-4 text-gray-600 leading-relaxed whitespace-pre-line max-h-[55vh] overflow-y-auto"></p>
    </div>
</dialog>
<script>
    (function () {
        var dlg = document.getElementById('bio-dialog');
        if (!dlg || !dlg.showModal) { return; }
        document.querySelectorAll('.bio-open').forEach(function (b) {
            b.addEventListener('click', function () {
                document.getElementById('bio-name').textContent = b.dataset.name;
                document.getElementById('bio-role').textContent = b.dataset.role;
                document.getElementById('bio-text').textContent = b.dataset.bio;
                dlg.showModal();
            });
        });
        document.getElementById('bio-close').addEventListener('click', function () { dlg.close(); });
        dlg.addEventListener('click', function (e) { if (e.target === dlg) { dlg.close(); } });
    })();
</script>
