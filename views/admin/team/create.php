<div class="mb-6">
    <a href="/admin/team" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        Team
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">Add team member</h1>
</div>

<?php
$formAction = '/admin/team';
$isEdit     = false;
$member     = ['full_name' => old('full_name'), 'role_title' => old('role_title'), 'bio' => old('bio'), 'linkedin_url' => old('linkedin_url'), 'email' => old('email'), 'group' => old('group') ?: 'leadership', 'sort_order' => old('sort_order', '0')];
require __DIR__ . '/_form.php';
?>
