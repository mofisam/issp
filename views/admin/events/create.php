<div class="mb-6">
    <a href="/admin/events" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-issp-green-700">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        Events
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">New event</h1>
</div>

<?php
$formAction = '/admin/events';
$isEdit     = false;
$event      = [
    'title' => old('title'), 'description' => old('description'), 'category_id' => old('category_id'),
    'location_name' => old('location_name'), 'location_address' => old('location_address'),
    'virtual_meeting_url' => old('virtual_meeting_url'), 'status' => old('status') ?: 'draft',
];
$liveUrl    = null;
$previewUrl = null;
require __DIR__ . '/_form.php';
?>
