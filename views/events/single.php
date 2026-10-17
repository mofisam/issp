<?php if (($event['status'] ?? 'published') !== 'published'): ?>
    <div class="bg-issp-orange-50 text-issp-orange-700 text-sm text-center py-2.5 px-4">
        Preview mode &mdash; this event is <strong><?= e($event['status']) ?></strong> and is not visible to the public yet.
    </div>
<?php endif; ?>
<article class="max-w-3xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold text-issp-green-700 mb-2"><?= e($event['title']) ?></h1>
    <p class="text-sm text-gray-500 mb-8"><?= format_date($event['starts_at'], 'd M Y, g:ia') ?> &middot; <?= e($event['location_name'] ?? 'Virtual') ?></p>
    <div class="text-gray-700 leading-relaxed mb-10"><?= nl2br(e($event['description'] ?? '')) ?></div>

    <?php if ($event['registration_required']): ?>
    <form method="POST" action="/events/<?= (int) $event['id'] ?>/register" class="space-y-4 max-w-md border border-gray-100 rounded-lg p-6">
        <?= \App\Core\Csrf::field() ?>
        <h2 class="font-semibold">Register for this event</h2>
        <input name="full_name" required placeholder="Full name" class="border rounded px-3 py-2 w-full">
        <input type="email" name="email" required placeholder="Email" class="border rounded px-3 py-2 w-full">
        <input name="organization_name" placeholder="Organization (optional)" class="border rounded px-3 py-2 w-full">
        <button type="submit" class="bg-issp-orange-600 hover:bg-issp-orange-700 text-white px-6 py-2 rounded font-medium">Register</button>
    </form>
    <?php endif; ?>
</article>
