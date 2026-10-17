<div class="mb-6">
    <a href="/admin/users" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        Users &amp; Roles
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit user</h1>
</div>

<?php
$formAction = '/admin/users/' . (int) $editUser['id'];
$isEdit     = true;
require __DIR__ . '/_form.php';
?>
