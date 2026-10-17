<?php
$currentUser = \App\Core\Auth::user();
$roleLabel   = $isSuperAdmin ? 'Super Admin' : ($isAdminTier ? 'Admin' : 'Editor');

$contentCards = [
    ['label' => 'Pages',     'value' => $content['pages'],     'href' => '/admin/pages',     'icon' => 'M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z M14 3v5h5 M9 13h6 M9 17h6'],
    ['label' => 'News articles', 'value' => $content['posts'], 'href' => '/admin/posts',     'icon' => 'M4 5h13a2 2 0 0 1 2 2v12a1 1 0 0 1-1 1H6a2 2 0 0 1-2-2V5Z M20 9H17a1 1 0 0 0-1 1v10 M7 9h6 M7 12.5h6 M7 16h4'],
    ['label' => 'Events',     'value' => $content['events'],    'href' => '/admin/events',    'icon' => 'M7 3v3M17 3v3M4 9h16M5 6h14a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M8 13h3v3H8z'],
    ['label' => 'Documents',  'value' => $content['resources'], 'href' => '/admin/resources', 'icon' => 'M4 7a1 1 0 0 1 1-1h4.2l1.6 2H19a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7Z'],
    ['label' => 'Team',       'value' => $content['team'],      'href' => '/admin/team',      'icon' => 'M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6 M16.5 8.5A2.75 2.75 0 1 0 16.5 3 M19.5 20c0-2.7-1.8-5-4.3-5.7'],
    ['label' => 'Partners',   'value' => $content['partners'],  'href' => '/admin/partners',  'icon' => 'm2.5 12 4-4 3 2 3-3 3 2 3-2 3 3-4 4-2.5-2L13 14l-2.5-2-2.5 2L5.5 12 4 13.5'],
];

$attentionItems = array_filter([
    $needsAttention['pendingPosts'] > 0 ? ['count' => $needsAttention['pendingPosts'], 'label' => 'article' . ($needsAttention['pendingPosts'] === 1 ? '' : 's') . ' awaiting review', 'href' => '/admin/posts'] : null,
    $needsAttention['pendingPages'] > 0 ? ['count' => $needsAttention['pendingPages'], 'label' => 'page' . ($needsAttention['pendingPages'] === 1 ? '' : 's') . ' awaiting review', 'href' => '/admin/pages'] : null,
    $needsAttention['draftPosts'] > 0 ? ['count' => $needsAttention['draftPosts'], 'label' => 'article' . ($needsAttention['draftPosts'] === 1 ? '' : 's') . ' still in draft', 'href' => '/admin/posts'] : null,
    $needsAttention['newContactMessages'] > 0 ? ['count' => $needsAttention['newContactMessages'], 'label' => 'new contact ' . ($needsAttention['newContactMessages'] === 1 ? 'message' : 'messages'), 'href' => '/admin/contact'] : null,
]);

$activityVerbs = ['created' => 'added', 'updated' => 'updated', 'deleted' => 'removed'];

$postStatusMeta = [
    'draft'          => ['label' => 'Draft',          'color' => 'bg-gray-300'],
    'pending_review' => ['label' => 'Pending review',  'color' => 'bg-issp-orange-500'],
    'published'      => ['label' => 'Published',       'color' => 'bg-issp-green-600'],
    'archived'       => ['label' => 'Archived',        'color' => 'bg-gray-400'],
];
$postStatusTotal = max(1, array_sum($postStatusCounts));

$eventMixTotal = max(1, $eventMix['upcoming'] + $eventMix['past']);

if (!empty($activityTrend)) {
    $trendMax = max(1, max(array_column($activityTrend, 'total')));
}
?>
<div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Welcome back<?= isset($currentUser['first_name']) ? ', ' . e($currentUser['first_name']) : '' ?></h1>
        <p class="text-sm text-gray-500 mt-1">Here's a snapshot of what's happening on the ISSP site.</p>
    </div>
    <span class="text-xs font-medium px-3 py-1.5 rounded-full bg-issp-green-50 text-issp-green-700"><?= e($roleLabel) ?></span>
</div>

<!-- Content overview -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
    <?php foreach ($contentCards as $card): ?>
        <a href="<?= e($card['href']) ?>" class="bg-white rounded-xl border border-gray-100 shadow-card p-4 hover:border-issp-green-300 transition-colors">
            <div class="h-9 w-9 rounded-lg bg-issp-green-50 text-issp-green-700 flex items-center justify-center mb-3">
                <svg viewBox="0 0 24 24" class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $card['icon'] ?>"/></svg>
            </div>
            <div class="text-xl font-bold text-gray-900"><?= (int) $card['value'] ?></div>
            <div class="text-xs text-gray-500 mt-0.5"><?= e($card['label']) ?></div>
        </a>
    <?php endforeach; ?>
</div>

<div class="grid lg:grid-cols-3 gap-6 mt-6">
    <!-- Needs attention -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Needs attention</h2>
        <?php if (empty($attentionItems)): ?>
            <p class="text-sm text-gray-400">Nothing waiting on you right now.</p>
        <?php else: ?>
            <ul class="space-y-2.5">
                <?php foreach ($attentionItems as $item): ?>
                    <li>
                        <a href="<?= e($item['href']) ?>" class="flex items-center gap-3 text-sm group">
                            <span class="h-6 w-6 rounded-full bg-issp-orange-50 text-issp-orange-700 flex items-center justify-center text-xs font-semibold flex-shrink-0"><?= (int) $item['count'] ?></span>
                            <span class="text-gray-700 group-hover:text-issp-green-700"><?= e($item['label']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Upcoming events -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-900">Upcoming events</h2>
            <a href="/admin/events" class="text-xs font-medium text-issp-green-700">View all</a>
        </div>
        <?php if (empty($upcomingEvents)): ?>
            <p class="text-sm text-gray-400">No upcoming events scheduled.</p>
        <?php else: ?>
            <ul class="space-y-3">
                <?php foreach ($upcomingEvents as $event): ?>
                    <li>
                        <a href="/admin/events/<?= (int) $event['id'] ?>/edit" class="flex items-center gap-3 group">
                            <div class="flex-shrink-0 w-10 text-center bg-issp-green-50 rounded-lg py-1">
                                <div class="text-[9px] font-semibold text-issp-green-700 uppercase"><?= e(date('M', strtotime($event['starts_at']))) ?></div>
                                <div class="text-sm font-bold text-issp-green-800 leading-none"><?= e(date('d', strtotime($event['starts_at']))) ?></div>
                            </div>
                            <span class="text-sm text-gray-700 group-hover:text-issp-green-700 truncate"><?= e($event['title']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Admin-tier: recent activity -->
    <?php if ($isAdminTier): ?>
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Recent activity</h2>
                <?php if ($isSuperAdmin): ?>
                    <a href="/admin/activity" class="text-xs font-medium text-issp-green-700">View all</a>
                <?php endif; ?>
            </div>
            <?php if (empty($recentActivity)): ?>
                <p class="text-sm text-gray-400">No activity recorded yet.</p>
            <?php else: ?>
                <ul class="space-y-3">
                    <?php foreach ($recentActivity as $log): ?>
                        <li class="text-sm">
                            <span class="text-gray-700">
                                <span class="font-medium"><?= e(trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?: 'Someone') ?></span>
                                <?= e($activityVerbs[$log['action']] ?? $log['action']) ?>
                                <span class="text-gray-500"><?= e(str_replace('_', ' ', $log['entity_type'])) ?></span>
                            </span>
                            <div class="text-xs text-gray-400"><?= e(format_date($log['created_at'], 'd M Y, g:ia')) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="bg-issp-green-50 rounded-xl border border-issp-green-100 p-6 flex flex-col justify-center">
            <p class="text-sm text-issp-green-800">Need access to Organizations, Users, or Settings? Ask a site admin.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Data insights -->
<div class="grid lg:grid-cols-3 gap-6 mt-6">
    <!-- Content pipeline -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Content pipeline</h2>
        <?php if (array_sum($postStatusCounts) === 0): ?>
            <p class="text-sm text-gray-400">No articles yet.</p>
        <?php else: ?>
            <div class="flex h-3 rounded-full overflow-hidden mb-4">
                <?php foreach ($postStatusCounts as $key => $count): ?>
                    <?php if ($count > 0): ?>
                        <div class="<?= $postStatusMeta[$key]['color'] ?>" style="width: <?= round($count / $postStatusTotal * 100, 2) ?>%"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <ul class="space-y-2">
                <?php foreach ($postStatusCounts as $key => $count): ?>
                    <li class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2 text-gray-600">
                            <span class="h-2.5 w-2.5 rounded-full <?= $postStatusMeta[$key]['color'] ?>"></span>
                            <?= e($postStatusMeta[$key]['label']) ?>
                        </span>
                        <span class="font-medium text-gray-900"><?= (int) $count ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Event mix -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6">
        <h2 class="font-semibold text-gray-900 mb-4">Event calendar</h2>
        <?php if ($eventMix['upcoming'] + $eventMix['past'] === 0): ?>
            <p class="text-sm text-gray-400">No events yet.</p>
        <?php else: ?>
            <div class="flex h-3 rounded-full overflow-hidden mb-4">
                <div class="bg-issp-green-600" style="width: <?= round($eventMix['upcoming'] / $eventMixTotal * 100, 2) ?>%"></div>
                <div class="bg-gray-300" style="width: <?= round($eventMix['past'] / $eventMixTotal * 100, 2) ?>%"></div>
            </div>
            <ul class="space-y-2">
                <li class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-gray-600"><span class="h-2.5 w-2.5 rounded-full bg-issp-green-600"></span> Upcoming</span>
                    <span class="font-medium text-gray-900"><?= (int) $eventMix['upcoming'] ?></span>
                </li>
                <li class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2 text-gray-600"><span class="h-2.5 w-2.5 rounded-full bg-gray-300"></span> Past</span>
                    <span class="font-medium text-gray-900"><?= (int) $eventMix['past'] ?></span>
                </li>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Admin-tier: 7-day activity trend -->
    <?php if ($isAdminTier): ?>
        <div class="bg-white rounded-xl border border-gray-100 shadow-card p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Activity, last 7 days</h2>
            <?php if (empty($activityTrend) || array_sum(array_column($activityTrend, 'total')) === 0): ?>
                <p class="text-sm text-gray-400">No activity recorded this week.</p>
            <?php else: ?>
                <div class="flex items-end justify-between gap-2 h-20">
                    <?php foreach ($activityTrend as $point): ?>
                        <div class="flex-1 flex flex-col items-center justify-end gap-1.5 h-full">
                            <div class="w-full max-w-[22px] rounded-t bg-issp-green-500/80" style="height: <?= $point['total'] > 0 ? max(4, round($point['total'] / $trendMax * 100)) : 2 ?>%" title="<?= (int) $point['total'] ?> actions"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="flex items-center justify-between gap-2 mt-2">
                    <?php foreach ($activityTrend as $point): ?>
                        <div class="flex-1 text-center text-[10px] text-gray-400"><?= e(date('D', strtotime($point['date']))) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Admin-tier: site-wide figures -->
<?php if ($isAdminTier): ?>
    <div class="grid grid-cols-2 <?= $isSuperAdmin ? 'sm:grid-cols-3' : 'sm:grid-cols-2' ?> gap-4 mt-6">
        <a href="/admin/newsletter" class="bg-white rounded-xl border border-gray-100 shadow-card p-5 flex items-center gap-4 hover:border-issp-green-300 transition-colors">
            <div class="h-10 w-10 rounded-lg bg-issp-green-50 text-issp-green-700 flex items-center justify-center flex-shrink-0">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M3.5 7l8.5 6 8.5-6"/></svg>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900"><?= (int) $engagement['newsletterSubscribers'] ?></div>
                <div class="text-xs text-gray-500">Newsletter subscribers</div>
            </div>
        </a>
        <a href="/admin/organizations" class="bg-white rounded-xl border border-gray-100 shadow-card p-5 flex items-center gap-4 hover:border-issp-green-300 transition-colors">
            <div class="h-10 w-10 rounded-lg bg-issp-green-50 text-issp-green-700 flex items-center justify-center flex-shrink-0">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 21V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v16 M15 21v-8h4a1 1 0 0 1 1 1v7"/></svg>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900"><?= (int) $engagement['organizations'] ?></div>
                <div class="text-xs text-gray-500">Organizations</div>
            </div>
        </a>
        <?php if ($isSuperAdmin): ?>
            <a href="/admin/users" class="bg-white rounded-xl border border-gray-100 shadow-card p-5 flex items-center gap-4 hover:border-issp-green-300 transition-colors">
                <div class="h-10 w-10 rounded-lg bg-issp-green-50 text-issp-green-700 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z M19.4 13.5a1.7 1.7 0 0 0 .34 1.87"/></svg>
                </div>
                <div>
                    <div class="text-xl font-bold text-gray-900"><?= (int) $userCount ?></div>
                    <div class="text-xs text-gray-500">User accounts</div>
                </div>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Quick actions -->
<div class="mt-6 bg-white rounded-xl border border-gray-100 shadow-card p-6">
    <h2 class="font-semibold text-gray-900 mb-4">Quick actions</h2>
    <div class="flex flex-wrap gap-3">
        <a href="/admin/posts/create" class="inline-flex items-center gap-2 text-sm font-medium bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2 rounded-lg">+ New article</a>
        <a href="/admin/events/create" class="inline-flex items-center gap-2 text-sm font-medium bg-white border border-gray-200 hover:border-issp-green-300 text-gray-700 px-4 py-2 rounded-lg">+ New event</a>
        <a href="/admin/resources/create" class="inline-flex items-center gap-2 text-sm font-medium bg-white border border-gray-200 hover:border-issp-green-300 text-gray-700 px-4 py-2 rounded-lg">+ Upload document</a>
        <a href="/admin/contact" class="inline-flex items-center gap-2 text-sm font-medium bg-white border border-gray-200 hover:border-issp-green-300 text-gray-700 px-4 py-2 rounded-lg">View enquiries</a>
        <?php if ($isAdminTier): ?>
            <a href="/admin/settings" class="inline-flex items-center gap-2 text-sm font-medium bg-white border border-gray-200 hover:border-issp-green-300 text-gray-700 px-4 py-2 rounded-lg">Site settings</a>
        <?php endif; ?>
        <?php if ($isSuperAdmin): ?>
            <a href="/admin/users/create" class="inline-flex items-center gap-2 text-sm font-medium bg-white border border-gray-200 hover:border-issp-green-300 text-gray-700 px-4 py-2 rounded-lg">+ Add user</a>
        <?php endif; ?>
    </div>
</div>
