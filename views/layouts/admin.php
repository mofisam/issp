<?php
/**
 * Admin shell: fixed, independently-scrolling sidebar with icons and
 * role-aware nav, a sticky top bar, and a responsive off-canvas sidebar
 * on mobile. Nav items whose `roles` the current user doesn't hold are
 * simply not rendered (not just hidden via CSS).
 */
$currentUser  = \App\Core\Auth::user();
$currentPath  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/admin';
$initials     = $currentUser
    ? strtoupper(mb_substr((string) $currentUser['first_name'], 0, 1) . mb_substr((string) $currentUser['last_name'], 0, 1))
    : '?';

/** Minimal stroke-based icon set (viewBox 0 0 24 24, 1.75 stroke). */
$icons = [
    'home'       => 'M3 11.5 12 4l9 7.5M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9',
    'document'   => 'M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z M14 3v5h5 M9 13h6 M9 17h6',
    'newspaper'  => 'M4 5h13a2 2 0 0 1 2 2v12a1 1 0 0 1-1 1H6a2 2 0 0 1-2-2V5Z M20 9H17a1 1 0 0 0-1 1v10 M7 9h6 M7 12.5h6 M7 16h4',
    'folder'     => 'M4 7a1 1 0 0 1 1-1h4.2l1.6 2H19a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7Z',
    'calendar'   => 'M7 3v3M17 3v3M4 9h16M5 6h14a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M8 13h3v3H8z',
    'users'      => 'M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6 M16.5 8.5A2.75 2.75 0 1 0 16.5 3 M19.5 20c0-2.7-1.8-5-4.3-5.7',
    'handshake'  => 'm2.5 12 4-4 3 2 3-3 3 2 3-2 3 3-4 4-2.5-2L13 14l-2.5-2-2.5 2L5.5 12 4 13.5',
    'mail'       => 'M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M3.5 7l8.5 6 8.5-6',
    'chat'       => 'M4 5h16a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H9l-5 4v-4H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z',
    'building'   => 'M5 21V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v16 M15 21v-8h4a1 1 0 0 1 1 1v7 M8 7h1M11 7h1M8 10.5h1M11 10.5h1M8 14h1M11 14h1 M3 21h18',
    'shield'     => 'M12 3.5 5 6v5.5c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-2.5Z M9.25 12l1.9 1.9L15 10',
    'photo'      => 'M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z M3 16l5-5 4 4 3-3 6 6 M8.5 10.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z',
    'cog'        => 'M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z M19.4 13.5a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1.04 1.56V20a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.04-1.56 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.7 1.7 0 0 0 .34-1.87A1.7 1.7 0 0 0 3.52 12.46H3.4a2 2 0 1 1 0-4h.1A1.7 1.7 0 0 0 5.06 7.42a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.7 1.7 0 0 0 1.87.34h.08A1.7 1.7 0 0 0 10.54 1.5V1.4a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1.04 1.56h.08a1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.7 1.7 0 0 0-.34 1.87v.08a1.7 1.7 0 0 0 1.56 1.04h.1a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.56 1.04Z',
    'question'   => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z M9.25 9.3a2.75 2.75 0 1 1 3.95 2.47c-.7.35-1.2 1-1.2 1.73v.3 M12 16.5h.01',
    'clock'      => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z M12 7v5l3.5 2',
];

/** label, href, icon key, roles allowed to see it (empty = any logged-in staff member) */
$navGroups = [
    'Overview' => [
        ['label' => 'Dashboard', 'href' => '/admin', 'icon' => 'home', 'roles' => []],
    ],
    'Content' => [
        ['label' => 'Pages',             'href' => '/admin/pages',     'icon' => 'document',  'roles' => ['super_admin', 'admin', 'editor']],
        ['label' => 'News & Updates',    'href' => '/admin/posts',     'icon' => 'newspaper',  'roles' => ['super_admin', 'admin', 'editor']],
        ['label' => 'Downloads Library', 'href' => '/admin/resources', 'icon' => 'folder',      'roles' => ['super_admin', 'admin', 'editor']],
        ['label' => 'Events',            'href' => '/admin/events',    'icon' => 'calendar',   'roles' => ['super_admin', 'admin', 'editor']],
        ['label' => 'Team',              'href' => '/admin/team',      'icon' => 'users',      'roles' => ['super_admin', 'admin', 'editor']],
        ['label' => 'Partners',          'href' => '/admin/partners',  'icon' => 'handshake',  'roles' => ['super_admin', 'admin', 'editor']],
        ['label' => 'FAQs',              'href' => '/admin/faqs',      'icon' => 'question',   'roles' => ['super_admin', 'admin', 'editor']],
        ['label' => 'Media Library',     'href' => '/admin/media',     'icon' => 'photo',      'roles' => ['super_admin', 'admin', 'editor']],
    ],
    'Engagement' => [
        ['label' => 'Newsletter',        'href' => '/admin/newsletter', 'icon' => 'mail', 'roles' => ['super_admin', 'admin', 'editor']],
        ['label' => 'Contact Messages',  'href' => '/admin/contact',    'icon' => 'chat', 'roles' => ['super_admin', 'admin', 'editor']],
        ['label' => 'Messages',          'href' => '/admin/messages',   'icon' => 'mail', 'roles' => []],
    ],
    'Administration' => [
        ['label' => 'Organizations', 'href' => '/admin/organizations', 'icon' => 'building', 'roles' => ['super_admin', 'admin']],
        ['label' => 'Users & Roles', 'href' => '/admin/users',         'icon' => 'shield',   'roles' => ['super_admin']],
        ['label' => 'Activity Log',  'href' => '/admin/activity',      'icon' => 'clock',    'roles' => ['super_admin']],
        ['label' => 'Settings',      'href' => '/admin/settings',      'icon' => 'cog',       'roles' => ['super_admin', 'admin']],
    ],
];

$isActive = static function (string $href) use ($currentPath): bool {
    if ($href === '/admin') {
        return $currentPath === '/admin';
    }
    return $currentPath === $href || str_starts_with($currentPath, $href . '/');
};

$canSee = static function (array $item): bool {
    return empty($item['roles']) || \App\Core\Auth::hasRole(...$item['roles']);
};

$unreadMessageCount = $currentUser ? \App\Models\Message::unreadCountFor((int) $currentUser['id']) : 0;

$pageTitle = 'Dashboard';
foreach ($navGroups as $group) {
    foreach ($group as $item) {
        if ($isActive($item['href'])) {
            $pageTitle = $item['label'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? ($pageTitle . ' · ISSP Admin')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        'issp-green':  { 50: '#EAF3EC', 100: '#CFE4D4', 500: '#2C7A4F', 600: '#1F5C3D', 700: '#173F2B', 800: '#122F21', 900: '#0D2118' },
                        'issp-orange': { 50: '#FBEEE3', 100: '#F6D9BE', 500: '#E8841F', 600: '#D9711E', 700: '#B2590F' },
                    },
                    boxShadow: { 'card': '0 1px 2px 0 rgba(15,23,42,.04), 0 1px 3px 0 rgba(15,23,42,.06)' },
                },
            },
        };
    </script>
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .sidebar-scroll::-webkit-scrollbar { width: 6px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 999px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <div id="admin-shell" class="min-h-screen lg:flex">

        <!-- Mobile overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-gray-900/50 z-30 hidden lg:hidden"></div>

        <!-- Sidebar: fixed to viewport, scrolls independently of the page -->
        <aside id="admin-sidebar"
               class="fixed inset-y-0 left-0 z-40 w-72 bg-gradient-to-b from-issp-green-800 to-issp-green-900 text-white flex flex-col -translate-x-full transition-transform duration-200 ease-out lg:translate-x-0">

            <div class="flex items-center gap-3 px-6 h-16 border-b border-white/10 flex-shrink-0">
                <img src="<?= asset('img/logo.png') ?>" alt="ISSP" class="h-9 w-auto flex-shrink-0">
                <div class="leading-tight">
                    <div class="font-semibold text-sm">ISSP Admin</div>
                    <div class="text-[11px] text-white/50">Content &amp; access console</div>
                </div>
                <button id="sidebar-close" type="button" class="ml-auto lg:hidden text-white/70 hover:text-white" aria-label="Close menu">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 py-5 space-y-6">
                <?php foreach ($navGroups as $groupLabel => $items): ?>
                    <?php $visibleItems = array_filter($items, $canSee); ?>
                    <?php if (!$visibleItems): ?><?php continue; ?><?php endif; ?>
                    <div>
                        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-white/40"><?= e($groupLabel) ?></p>
                        <div class="space-y-0.5">
                            <?php foreach ($visibleItems as $item): ?>
                                <?php $active = $isActive($item['href']); ?>
                                <a href="<?= e($item['href']) ?>"
                                   class="group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                          <?= $active ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white hover:bg-white/5' ?>">
                                    <span class="relative flex-shrink-0 h-5 w-5 <?= $active ? 'text-issp-orange-500' : 'text-white/50 group-hover:text-white/80' ?>">
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="<?= $icons[$item['icon']] ?? '' ?>"/>
                                        </svg>
                                    </span>
                                    <span class="truncate"><?= e($item['label']) ?></span>
                                    <?php if ($item['href'] === '/admin/messages' && $unreadMessageCount > 0): ?>
                                        <span class="ml-auto text-[11px] font-semibold bg-issp-orange-500 text-white rounded-full px-1.5 py-0.5 min-w-[1.25rem] text-center"><?= $unreadMessageCount > 99 ? '99+' : (int) $unreadMessageCount ?></span>
                                    <?php elseif ($active): ?>
                                        <span class="ml-auto h-1.5 w-1.5 rounded-full bg-issp-orange-500"></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </nav>

            <div class="flex-shrink-0 border-t border-white/10 p-3">
                <div class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-white/5">
                    <div class="h-9 w-9 rounded-full bg-issp-orange-500 flex items-center justify-center text-sm font-semibold flex-shrink-0"><?= e($initials) ?></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate"><?= e(trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? ''))) ?: 'Account' ?></p>
                        <p class="text-[11px] text-white/50 truncate"><?= e($currentUser['email'] ?? '') ?></p>
                    </div>
                </div>
                <a href="/logout" class="mt-1 flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-white/70 hover:text-white hover:bg-white/5">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3M16 17l5-5-5-5M21 12H9"/></svg>
                    Log out
                </a>
            </div>
        </aside>

        <!-- Main column -->
        <div class="flex-1 min-w-0 lg:ml-72">
            <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-gray-100 h-16 flex items-center gap-4 px-4 sm:px-6 lg:px-8">
                <button id="sidebar-open" type="button" class="lg:hidden text-gray-500 hover:text-gray-800" aria-label="Open menu">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0">
                    <h1 class="text-base sm:text-lg font-semibold text-gray-900 truncate"><?= e($pageTitle) ?></h1>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <a href="/admin/messages" class="relative text-gray-500 hover:text-issp-green-700" aria-label="Messages">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M3.5 7l8.5 6 8.5-6"/></svg>
                        <?php if ($unreadMessageCount > 0): ?>
                            <span class="absolute -top-1.5 -right-1.5 h-4 min-w-[1rem] px-1 rounded-full bg-issp-orange-500 text-white text-[10px] font-semibold flex items-center justify-center"><?= $unreadMessageCount > 99 ? '99+' : (int) $unreadMessageCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="/" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-issp-green-700 border border-gray-200 rounded-full px-3 py-1.5">
                        <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3h7v7M21 3 10 14M19 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h6"/></svg>
                        View site
                    </a>
                    <div class="h-8 w-8 rounded-full bg-issp-green-100 text-issp-green-700 flex items-center justify-center text-xs font-semibold"><?= e($initials) ?></div>
                </div>
            </header>

            <?php require BASE_PATH . '/views/partials/flash-messages.php'; ?>

            <main class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
                <?php $content(); ?>
            </main>
        </div>
    </div>

    <script>
        (function () {
            var sidebar = document.getElementById('admin-sidebar');
            var overlay = document.getElementById('sidebar-overlay');
            var openBtn = document.getElementById('sidebar-open');
            var closeBtn = document.getElementById('sidebar-close');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            }
            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }

            openBtn && openBtn.addEventListener('click', openSidebar);
            closeBtn && closeBtn.addEventListener('click', closeSidebar);
            overlay && overlay.addEventListener('click', closeSidebar);
        })();
    </script>
</body>
</html>
