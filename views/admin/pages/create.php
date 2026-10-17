<div class="mb-6">
    <a href="/admin/pages" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        Pages
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">New page</h1>
</div>

<?php
$formAction = '/admin/pages';
$isEdit     = false;
$page       = ['title' => old('title'), 'excerpt' => old('excerpt'), 'body_content' => old('body_content'), 'status' => old('status') ?: 'draft'];
$liveUrl    = null;
$previewUrl = null;
require __DIR__ . '/_form.php';
?>
