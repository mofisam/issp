<section class="max-w-6xl mx-auto px-6 py-16">
    <div class="flex items-center justify-between mb-10">
        <h1 class="text-3xl font-bold text-issp-green-700">Event Calendar</h1>
        <select id="event-month-filter" class="border rounded px-3 py-2 text-sm">
            <option value="">All months</option>
        </select>
    </div>
    <div class="space-y-4">
        <?php foreach ($events as $event): ?>
            <a data-event-month="<?= date('n', strtotime($event['starts_at'])) ?>" href="/events/<?= e($event['slug']) ?>"
               class="flex items-center justify-between border border-gray-100 rounded-lg p-5 hover:border-issp-green-600">
                <div>
                    <div class="font-semibold"><?= e($event['title']) ?></div>
                    <div class="text-sm text-gray-500"><?= e($event['location_name'] ?? 'Virtual') ?></div>
                </div>
                <div class="text-sm font-medium text-issp-orange-600"><?= format_date($event['starts_at']) ?></div>
            </a>
        <?php endforeach; ?>
        <?php if (empty($events)): ?>
            <p class="text-gray-500 text-sm">No upcoming events right now.</p>
        <?php endif; ?>
    </div>
    <script src="<?= asset('js/calendar.js') ?>"></script>
</section>
