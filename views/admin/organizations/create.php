<div class="mb-6">
    <a href="/admin/organizations" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        Organizations
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">Add organization</h1>
</div>

<?php
$formAction   = '/admin/organizations';
$isEdit       = false;
$organization = ['name' => old('name'), 'organization_type' => old('organization_type') ?: 'other', 'registration_number' => old('registration_number'), 'website_url' => old('website_url'), 'contact_email' => old('contact_email'), 'contact_phone' => old('contact_phone'), 'address' => old('address'), 'country' => old('country', 'Nigeria')];
require __DIR__ . '/_form.php';
?>
