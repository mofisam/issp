<?php $success = flash_get('success'); $error = flash_get('error'); ?>
<?php if ($success): ?>
    <div data-flash class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-start gap-3 rounded-lg border border-issp-green-100 bg-issp-green-50 text-issp-green-700 text-sm px-4 py-3 shadow-card">
            <svg viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.5l2 2 4.5-5M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/></svg>
            <p><?= e($success) ?></p>
        </div>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div data-flash class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-start gap-3 rounded-lg border border-red-100 bg-red-50 text-red-700 text-sm px-4 py-3 shadow-card">
            <svg viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/></svg>
            <p><?= e($error) ?></p>
        </div>
    </div>
<?php endif; ?>
