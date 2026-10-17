<?php
/**
 * Shared Prev/Next pager for admin list pages. Expects $page and $lastPage
 * in scope. Preserves the current search/filter query string across page
 * links by carrying forward $_GET, so this partial works the same on every
 * list page regardless of what it's filtered by.
 */
if (($lastPage ?? 1) > 1):
    $basePath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
    $buildPageUrl = static function (int $targetPage) use ($basePath): string {
        $query = $_GET;
        $query['page'] = $targetPage;
        return $basePath . '?' . http_build_query($query);
    };
?>
<div class="flex items-center justify-between mt-5 text-sm">
    <span class="text-gray-400">Page <?= (int) $page ?> of <?= (int) $lastPage ?><?php if (isset($total)): ?> &middot; <?= number_format($total) ?> total<?php endif; ?></span>
    <div class="flex items-center gap-2">
        <?php if ($page > 1): ?>
            <a href="<?= e($buildPageUrl($page - 1)) ?>" class="px-3.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:border-issp-green-300">Previous</a>
        <?php endif; ?>
        <?php if ($page < $lastPage): ?>
            <a href="<?= e($buildPageUrl($page + 1)) ?>" class="px-3.5 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:border-issp-green-300">Next</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
