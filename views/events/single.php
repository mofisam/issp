<?php
$startTs = strtotime($event['starts_at']);
$endTs   = !empty($event['ends_at']) ? strtotime($event['ends_at']) : null;
$sameDay = $endTs && date('Ymd', $endTs) === date('Ymd', $startTs);
$timeText = date('g:ia', $startTs) . ($endTs ? ($sameDay ? ' – ' . date('g:ia', $endTs) : ' – ' . date('D j M, g:ia', $endTs)) : '');
$tzLabel = (new DateTime('now', new DateTimeZone($event['timezone'] ?: 'Africa/Lagos')))->format('T');
$cancelled = $event['status'] === 'cancelled';
$field = 'border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500';
$gcal = 'https://calendar.google.com/calendar/render?' . http_build_query([
    'action'   => 'TEMPLATE',
    'text'     => $event['title'],
    'dates'    => date('Ymd\THis', $startTs) . '/' . date('Ymd\THis', $endTs ?: $startTs),
    'ctz'      => $event['timezone'] ?: 'Africa/Lagos',
    'details'  => (string) $event['description'],
    'location' => trim(($event['location_name'] ?? '') . ' ' . ($event['location_address'] ?? '')),
]);
$old = $_SESSION['_old'] ?? [];
unset($_SESSION['_old']);
$ico = static fn (string $d): string => '<svg viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 text-issp-green-600 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="' . $d . '"/></svg>';
?>
<?php if (($event['status'] ?? 'published') === 'draft'): ?>
    <div class="bg-issp-orange-50 text-issp-orange-700 text-sm text-center py-2.5 px-4">Preview mode &mdash; this event is not visible to the public yet.</div>
<?php endif; ?>

<section class="bg-issp-green-800 text-white">
    <div class="max-w-6xl mx-auto px-6 pt-10 pb-12">
        <a href="/events" class="text-sm text-white/60 hover:text-white">&larr; All events</a>
        <div class="mt-6 flex flex-wrap gap-2">
            <?php if ($cancelled): ?><span class="rounded-full bg-red-500/20 text-red-200 text-xs font-medium px-3 py-1">Cancelled</span>
            <?php elseif ($isPast): ?><span class="rounded-full bg-white/10 text-white/80 text-xs font-medium px-3 py-1">This event has ended</span>
            <?php else: ?><span class="rounded-full bg-issp-orange-500/20 text-issp-orange-100 text-xs font-medium px-3 py-1">Upcoming</span><?php endif; ?>
            <?php if (!empty($event['is_virtual'])): ?><span class="rounded-full bg-white/10 text-white/80 text-xs font-medium px-3 py-1">Online</span><?php endif; ?>
        </div>
        <h1 class="font-serif text-4xl md:text-5xl leading-tight mt-4 max-w-3xl"><?= e($event['title']) ?></h1>
        <p class="mt-4 text-white/70"><?= e(date('l, j F Y', $startTs)) ?> &middot; <?= e($timeText) ?> <?= e($tzLabel) ?></p>
    </div>
</section>

<div class="max-w-6xl mx-auto px-6 py-12 grid lg:grid-cols-3 gap-10 items-start">
    <article class="lg:col-span-2 min-w-0">
        <?php if (!empty($event['image_url'])): ?>
            <img src="<?= e($event['image_url']) ?>" alt="" class="w-full rounded-2xl object-cover max-h-[420px] mb-8">
        <?php endif; ?>
        <h2 class="font-serif text-2xl text-gray-900 mb-3">About this event</h2>
        <div class="text-gray-700 leading-relaxed text-[1.0625rem]"><?= !empty($event['description']) ? nl2br(e($event['description'])) : '<span class="text-gray-400">Details will be shared soon.</span>' ?></div>

        <div class="mt-10 pt-6 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <a href="/events" class="text-sm font-medium text-issp-green-700 hover:underline">&larr; Back to events</a>
            <button type="button" id="copy-link" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 border border-gray-200 hover:border-issp-green-500 rounded-lg px-3.5 py-2"><span id="copy-label">Copy link</span></button>
        </div>
    </article>

    <aside class="space-y-5 lg:sticky lg:top-6">
        <div class="rounded-2xl border border-gray-100 shadow-card p-6 space-y-4 bg-white">
            <div class="flex gap-3"><?= $ico('M8 3v3M16 3v3M4 8h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z') ?>
                <div><div class="font-medium text-gray-900"><?= e(date('l, j F Y', $startTs)) ?></div><div class="text-sm text-gray-500"><?= e($timeText) ?> <?= e($tzLabel) ?></div></div></div>
            <div class="flex gap-3"><?= $ico('M12 21s7-6.1 7-11.5A7 7 0 0 0 5 9.5C5 14.9 12 21 12 21Z M12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z') ?>
                <div>
                    <div class="font-medium text-gray-900"><?= e($event['location_name'] ?: (!empty($event['is_virtual']) ? 'Online event' : 'Venue to be announced')) ?></div>
                    <?php if (!empty($event['location_address'])): ?><div class="text-sm text-gray-500"><?= e($event['location_address']) ?></div><?php endif; ?>
                    <?php if (!empty($event['is_virtual']) && !empty($event['virtual_meeting_url']) && $isOpen): ?>
                        <a href="<?= e($event['virtual_meeting_url']) ?>" target="_blank" rel="noopener" class="text-sm font-medium text-issp-green-700 hover:underline">Join online &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($capacity !== null && $isOpen): ?>
                <div class="flex gap-3"><?= $ico('M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1M10 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM20 19v-1a3 3 0 0 0-2-2.8M16 5.2a3 3 0 0 1 0 5.6') ?>
                    <div class="text-sm"><span class="font-medium text-gray-900"><?= $isFull ? 'Fully booked' : (int) $spotsLeft . ' of ' . (int) $capacity . ' spots left' ?></span></div></div>
            <?php endif; ?>
            <?php if (!$cancelled): ?>
                <div class="flex gap-2 pt-1 text-sm">
                    <a href="<?= e($gcal) ?>" target="_blank" rel="noopener" class="flex-1 text-center rounded-lg border border-gray-200 hover:border-issp-green-500 py-2 font-medium text-gray-700">Google Calendar</a>
                    <a href="/events/<?= e($event['slug']) ?>/calendar" class="flex-1 text-center rounded-lg border border-gray-200 hover:border-issp-green-500 py-2 font-medium text-gray-700">Download .ics</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($cancelled): ?>
            <div class="rounded-2xl bg-red-50 text-red-700 text-sm p-5">This event has been cancelled. Check <a href="/events" class="underline font-medium">upcoming events</a> for alternatives.</div>
        <?php elseif ($isPast): ?>
            <div class="rounded-2xl bg-gray-50 text-gray-600 text-sm p-5">This event has taken place and registration is closed.</div>
        <?php elseif (!empty($event['registration_required'])): ?>
            <?php if (!empty($event['registration_url'])): ?>
                <a href="<?= e($event['registration_url']) ?>" target="_blank" rel="noopener" class="block text-center rounded-xl bg-issp-orange-600 hover:bg-issp-orange-700 text-white font-medium px-6 py-3.5">Register for this event &rarr;</a>
            <?php elseif ($isFull): ?>
                <div class="rounded-2xl bg-issp-orange-50 text-issp-orange-700 text-sm p-5">This event is fully booked. Watch <a href="/events" class="underline font-medium">upcoming events</a> for the next one.</div>
            <?php else: ?>
                <form id="register" method="POST" action="/events/<?= (int) $event['id'] ?>/register" class="rounded-2xl border border-gray-100 shadow-card bg-white p-6 space-y-3.5 scroll-mt-6">
                    <?= \App\Core\Csrf::field() ?>
                    <div>
                        <h2 class="font-serif text-xl text-gray-900">Reserve your place</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Free to attend. We'll email you the details.</p>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-500 mb-1" for="r-name">Full name</label><input id="r-name" name="full_name" required value="<?= e($old['full_name'] ?? '') ?>" class="<?= $field ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-500 mb-1" for="r-email">Email</label><input id="r-email" type="email" name="email" required value="<?= e($old['email'] ?? '') ?>" class="<?= $field ?>"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-500 mb-1" for="r-phone">Phone <span class="text-gray-300">(optional)</span></label><input id="r-phone" name="phone" value="<?= e($old['phone'] ?? '') ?>" class="<?= $field ?>"></div>
                        <div><label class="block text-xs font-medium text-gray-500 mb-1" for="r-org">Organization <span class="text-gray-300">(optional)</span></label><input id="r-org" name="organization_name" value="<?= e($old['organization_name'] ?? '') ?>" class="<?= $field ?>"></div>
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-issp-orange-600 hover:bg-issp-orange-700 text-white font-medium px-6 py-3">Register</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </aside>
</div>

<?php if (!empty($more)): ?>
    <section class="bg-gray-50 py-14 px-6">
        <div class="max-w-6xl mx-auto">
            <h2 class="font-serif text-2xl text-gray-900 mb-6">More upcoming events</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <?php foreach ($more as $m): $mt = strtotime($m['starts_at']); ?>
                    <a href="/events/<?= e($m['slug']) ?>" class="group flex gap-4 rounded-xl bg-white border border-gray-100 p-4 hover:border-issp-green-500">
                        <div class="w-14 flex-shrink-0 text-center rounded-lg bg-issp-green-50 text-issp-green-700 py-2"><div class="text-[11px] font-semibold uppercase"><?= e(date('M', $mt)) ?></div><div class="font-serif text-2xl leading-none"><?= e(date('j', $mt)) ?></div></div>
                        <div class="min-w-0"><div class="font-medium text-gray-900 group-hover:text-issp-green-700"><?= e($m['title']) ?></div><div class="text-xs text-gray-500 mt-1 truncate"><?= e(date('g:ia', $mt)) ?> &middot; <?= e($m['location_name'] ?: (!empty($m['is_virtual']) ? 'Online' : 'TBA')) ?></div></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<script>
    document.getElementById('copy-link').addEventListener('click', function () {
        var l = document.getElementById('copy-label');
        (navigator.clipboard ? navigator.clipboard.writeText(location.href) : Promise.reject()).then(function () { l.textContent = 'Copied'; }, function () { l.textContent = 'Press Ctrl+C'; });
        setTimeout(function () { l.textContent = 'Copy link'; }, 1800);
    });
</script>
