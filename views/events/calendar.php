<?php
$isUpcoming = $when === 'upcoming';
$tabUrl = static fn (string $w): string => '/events' . ($w === 'past' ? '?when=past' : '');
$timeRange = static function (array $e): string {
    $s = strtotime($e['starts_at']);
    $en = !empty($e['ends_at']) ? strtotime($e['ends_at']) : null;
    $t = date('g:ia', $s);
    if ($en && date('Ymd', $en) === date('Ymd', $s)) { $t .= ' – ' . date('g:ia', $en); }
    return $t;
};
$where = static fn (array $e): string => !empty($e['is_virtual'])
    ? ($e['location_name'] ? $e['location_name'] . ' · Online' : 'Online event')
    : ($e['location_name'] ?: 'Venue to be announced');
$pin  = '<svg viewBox="0 0 24 24" class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.1 7-11.5A7 7 0 0 0 5 9.5C5 14.9 12 21 12 21Z M12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>';
$clock = '<svg viewBox="0 0 24 24" class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z M12 7v5l3.5 2"/></svg>';

$lead = ($isUpcoming && $month === '' && $events) ? $events[0] : null;
$rest = $lead ? array_slice($events, 1) : $events;

// Group by month for the upcoming list; past events are listed flat, newest first.
$groups = [];
foreach ($rest as $ev) {
    $groups[date('F Y', strtotime($ev['starts_at']))][] = $ev;
}
?>
<section class="max-w-6xl mx-auto px-6 pt-14 pb-16">
    <header class="max-w-2xl">
        <p class="text-sm font-medium text-issp-orange-600">Events</p>
        <h1 class="font-serif text-4xl md:text-5xl text-issp-green-800 leading-tight mt-2">Meet the programme in person</h1>
        <p class="text-gray-500 mt-3">Forums, workshops and webinars bringing the insurance sector together.</p>
    </header>

    <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-b border-gray-100">
        <nav class="flex gap-6 text-sm font-medium" aria-label="Event timeline">
            <a href="<?= $tabUrl('upcoming') ?>" class="pb-3 -mb-px border-b-2 <?= $isUpcoming ? 'border-issp-green-600 text-issp-green-700' : 'border-transparent text-gray-500 hover:text-gray-800' ?>">Upcoming <span class="ml-1 text-xs text-gray-400"><?= (int) $upcomingCount ?></span></a>
            <a href="<?= $tabUrl('past') ?>" class="pb-3 -mb-px border-b-2 <?= !$isUpcoming ? 'border-issp-green-600 text-issp-green-700' : 'border-transparent text-gray-500 hover:text-gray-800' ?>">Past events <span class="ml-1 text-xs text-gray-400"><?= (int) $pastCount ?></span></a>
        </nav>
    </div>

    <?php if ($isUpcoming && count($months) > 1): ?>
        <div class="mt-5 flex flex-wrap gap-2" aria-label="Filter by month">
            <a href="/events" class="rounded-full border px-3.5 py-1.5 text-sm font-medium <?= $month === '' ? 'bg-issp-green-700 border-issp-green-700 text-white' : 'border-gray-200 text-gray-600 hover:border-issp-green-500' ?>">All months</a>
            <?php foreach ($months as $key => $label): ?>
                <a href="/events?month=<?= e($key) ?>" class="rounded-full border px-3.5 py-1.5 text-sm font-medium <?= $month === $key ? 'bg-issp-green-700 border-issp-green-700 text-white' : 'border-gray-200 text-gray-600 hover:border-issp-green-500' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($lead): ?>
        <a href="/events/<?= e($lead['slug']) ?>" class="group mt-8 grid md:grid-cols-5 rounded-2xl overflow-hidden bg-issp-green-800 text-white">
            <div class="md:col-span-3 p-7 md:p-10 flex flex-col justify-center">
                <span class="inline-flex w-fit items-center gap-2 text-xs font-semibold uppercase tracking-wide text-issp-orange-500">
                    <span class="h-2 w-2 rounded-full bg-issp-orange-500"></span> Next event
                </span>
                <h2 class="font-serif text-3xl md:text-4xl leading-tight mt-3 group-hover:underline decoration-white/30 underline-offset-4"><?= e($lead['title']) ?></h2>
                <?php if (!empty($lead['description'])): ?><p class="text-white/70 mt-3 line-clamp-3"><?= e($lead['description']) ?></p><?php endif; ?>
                <div class="mt-5 space-y-1.5 text-sm text-white/80">
                    <div class="flex items-center gap-2"><?= $clock ?><?= e(date('l, j F Y', strtotime($lead['starts_at']))) ?> &middot; <?= e($timeRange($lead)) ?></div>
                    <div class="flex items-center gap-2"><?= $pin ?><?= e($where($lead)) ?></div>
                </div>
                <span class="mt-6 inline-flex w-fit items-center rounded-lg bg-issp-orange-600 group-hover:bg-issp-orange-700 px-5 py-2.5 text-sm font-medium">
                    <?= !empty($lead['registration_required']) ? 'Register now' : 'View details' ?> &rarr;
                </span>
            </div>
            <div class="md:col-span-2 bg-issp-green-900 relative min-h-[180px]">
                <?php if (!empty($lead['image_url'])): ?>
                    <img src="<?= e($lead['image_url']) ?>" alt="" class="absolute inset-0 h-full w-full object-cover opacity-90">
                <?php else: ?>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <div class="font-serif text-8xl leading-none"><?= e(date('j', strtotime($lead['starts_at']))) ?></div>
                            <div class="mt-2 text-sm uppercase tracking-[0.2em] text-white/60"><?= e(date('F Y', strtotime($lead['starts_at']))) ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </a>
    <?php endif; ?>

    <?php foreach ($groups as $label => $list): ?>
        <div class="mt-10">
            <?php if ($isUpcoming): ?><h2 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3"><?= e($label) ?></h2><?php endif; ?>
            <div class="rounded-2xl border border-gray-100 divide-y divide-gray-100 overflow-hidden">
                <?php foreach ($list as $ev): ?>
                    <?php $ts = strtotime($ev['starts_at']); ?>
                    <a href="/events/<?= e($ev['slug']) ?>" class="group flex items-center gap-5 px-5 py-5 hover:bg-gray-50/70 transition-colors">
                        <div class="flex-shrink-0 w-16 text-center rounded-xl <?= $isUpcoming ? 'bg-issp-green-50 text-issp-green-700' : 'bg-gray-100 text-gray-500' ?> py-2">
                            <div class="text-[11px] font-semibold uppercase tracking-wide"><?= e(date('M', $ts)) ?></div>
                            <div class="font-serif text-3xl leading-none"><?= e(date('j', $ts)) ?></div>
                            <?php if (!$isUpcoming): ?><div class="text-[10px] mt-0.5"><?= e(date('Y', $ts)) ?></div><?php endif; ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-gray-900 group-hover:text-issp-green-700"><?= e($ev['title']) ?></h3>
                                <?php if (!empty($ev['is_virtual'])): ?><span class="rounded-full bg-blue-50 text-blue-700 text-[11px] font-medium px-2 py-0.5">Online</span><?php endif; ?>
                                <?php if ($isUpcoming && !empty($ev['registration_required'])): ?><span class="rounded-full bg-issp-orange-50 text-issp-orange-700 text-[11px] font-medium px-2 py-0.5">Registration open</span><?php endif; ?>
                                <?php if (($ev['status'] ?? '') === 'cancelled'): ?><span class="rounded-full bg-red-50 text-red-700 text-[11px] font-medium px-2 py-0.5">Cancelled</span><?php endif; ?>
                            </div>
                            <div class="mt-1.5 flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-500">
                                <span class="flex items-center gap-1.5"><?= $clock ?><?= e($timeRange($ev)) ?></span>
                                <span class="flex items-center gap-1.5 min-w-0"><?= $pin ?><span class="truncate"><?= e($where($ev)) ?></span></span>
                            </div>
                        </div>
                        <span class="hidden sm:block text-gray-300 group-hover:text-issp-green-600 text-xl" aria-hidden="true">&rarr;</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($events)): ?>
        <div class="mt-10 rounded-2xl bg-gray-50 px-6 py-16 text-center">
            <p class="font-medium text-gray-800"><?= $isUpcoming ? ($month !== '' ? 'No events this month.' : 'No upcoming events right now.') : 'No past events yet.' ?></p>
            <p class="text-sm text-gray-500 mt-1">Subscribe in the footer to hear first when the next one is announced.</p>
            <?php if ($month !== ''): ?><a href="/events" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Show all upcoming events</a><?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!$isUpcoming && $lastPage > 1): ?>
        <nav class="mt-10 flex items-center justify-between text-sm" aria-label="Pagination">
            <span class="text-gray-400">Page <?= (int) $page ?> of <?= (int) $lastPage ?></span>
            <div class="flex gap-2">
                <?php if ($page > 1): ?><a href="/events?when=past&page=<?= $page - 1 ?>" class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:border-issp-green-500">&larr; Newer</a><?php endif; ?>
                <?php if ($page < $lastPage): ?><a href="/events?when=past&page=<?= $page + 1 ?>" class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:border-issp-green-500">Older &rarr;</a><?php endif; ?>
            </div>
        </nav>
    <?php endif; ?>
</section>
