<?php
use App\Core\Auth;
use App\Models\Message;
use App\Models\Organization;
use App\Models\SiteSetting;

$portalUser   = Auth::user();
$unreadCount  = $portalUser ? Message::unreadCountFor((int) $portalUser['id']) : 0;
$portalOrg    = ($portalUser && !empty($portalUser['organization_id'])) ? Organization::find((int) $portalUser['organization_id']) : null;
$portalName   = $portalUser ? trim(($portalUser['first_name'] ?? '') . ' ' . ($portalUser['last_name'] ?? '')) : '';
$portalInitial = $portalName !== '' ? strtoupper(mb_substr($portalName, 0, 1)) : '?';
$currentPath  = parse_url($_SERVER['REQUEST_URI'] ?? '/portal', PHP_URL_PATH) ?: '/portal';

$helpEmail = SiteSetting::get('contact_email', 'project@issprogram.com');

$portalNav = [
    '/portal'           => ['Dashboard', fn (string $p) => $p === '/portal'],
    '/portal/documents' => ['Documents', fn (string $p) => str_starts_with($p, '/portal/documents')],
    '/portal/messages'  => ['Messages',  fn (string $p) => str_starts_with($p, '/portal/messages')],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'ISSP Stakeholder Portal') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Newsreader:ital,wght@0,400;0,500;0,600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'], serif: ['Newsreader', 'Georgia', 'serif'] },
                    colors: {
                        'issp-green':  { 50: '#EAF3EC', 100: '#CFE4D4', 500: '#2C7A4F', 600: '#1F5C3D', 700: '#173F2B', 800: '#122F21', 900: '#0D2118', DEFAULT: '#1F5C3D' },
                        'issp-orange': { 50: '#FBEEE3', 100: '#F6D9BE', 500: '#E8841F', 600: '#D9711E', 700: '#B2590F', DEFAULT: '#D9711E' },
                    },
                    boxShadow: { 'card': '0 1px 2px 0 rgba(15,23,42,.04), 0 1px 3px 0 rgba(15,23,42,.06)' },
                },
            },
        };
    </script>
</head>
<body class="font-sans text-gray-800 bg-gray-50 min-h-screen flex flex-col" style="font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;">
    <header class="bg-white border-b border-gray-100 sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between gap-6">
            <a href="/portal" class="flex items-center gap-3 flex-shrink-0">
                <img src="<?= asset('img/logo.png') ?>" alt="ISSP" class="h-9 w-auto">
                <span class="hidden sm:block text-sm font-medium text-gray-500 border-l border-gray-200 pl-3">Stakeholder Portal</span>
            </a>

            <nav class="hidden md:flex items-center gap-1 text-sm">
                <?php foreach ($portalNav as $href => [$label, $isActive]): ?>
                    <?php $active = $isActive($currentPath); ?>
                    <a href="<?= $href ?>" class="relative px-3.5 py-2 rounded-lg font-medium <?= $active ? 'text-issp-green-700 bg-issp-green-50' : 'text-gray-600 hover:text-issp-green-700 hover:bg-gray-50' ?>">
                        <?= $label ?>
                        <?php if ($href === '/portal/messages' && $unreadCount > 0): ?>
                            <span class="ml-1.5 inline-flex items-center justify-center h-[18px] min-w-[18px] px-1 rounded-full bg-issp-orange-600 text-white text-[10px] font-semibold"><?= $unreadCount > 99 ? '99+' : (int) $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="hidden md:flex items-center gap-3 flex-shrink-0">
                <div class="flex items-center gap-2.5 text-right">
                    <div class="leading-tight">
                        <div class="text-sm font-medium text-gray-900"><?= e($portalName) ?></div>
                        <?php if ($portalOrg): ?><div class="text-xs text-gray-400 max-w-[180px] truncate"><?= e($portalOrg['name']) ?></div><?php endif; ?>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-issp-green-50 text-issp-green-700 flex items-center justify-center text-sm font-semibold"><?= e($portalInitial) ?></div>
                </div>
                <a href="/logout" class="text-sm font-medium text-gray-500 hover:text-issp-orange-700 border border-gray-200 hover:border-issp-orange-100 rounded-lg px-3 py-1.5">Log out</a>
            </div>

            <button id="portal-nav-toggle" type="button" class="md:hidden p-2 -mr-2 text-gray-600" aria-label="Toggle menu" aria-expanded="false">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>

        <div id="portal-mobile-menu" class="hidden md:hidden border-t border-gray-100 px-6 py-3 space-y-1 text-sm bg-white">
            <?php foreach ($portalNav as $href => [$label, $isActive]): ?>
                <?php $active = $isActive($currentPath); ?>
                <a href="<?= $href ?>" class="flex items-center justify-between px-3 py-2.5 rounded-lg font-medium <?= $active ? 'text-issp-green-700 bg-issp-green-50' : 'text-gray-700' ?>">
                    <?= $label ?>
                    <?php if ($href === '/portal/messages' && $unreadCount > 0): ?>
                        <span class="inline-flex items-center justify-center h-[18px] min-w-[18px] px-1 rounded-full bg-issp-orange-600 text-white text-[10px] font-semibold"><?= (int) $unreadCount ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
            <div class="flex items-center justify-between px-3 pt-3 mt-2 border-t border-gray-100">
                <div class="leading-tight">
                    <div class="font-medium text-gray-900"><?= e($portalName) ?></div>
                    <?php if ($portalOrg): ?><div class="text-xs text-gray-400"><?= e($portalOrg['name']) ?></div><?php endif; ?>
                </div>
                <a href="/logout" class="font-medium text-issp-orange-700">Log out</a>
            </div>
        </div>
    </header>

    <?php require BASE_PATH . '/views/partials/flash-messages.php'; ?>

    <main class="flex-1">
        <?php $content(); ?>
    </main>

    <footer class="border-t border-gray-100 bg-white">
        <div class="max-w-6xl mx-auto px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-400">
            <span>&copy; <?= date('Y') ?> ISSP &middot; Insurance Sector Strengthening Program</span>
            <span>Need help? <a href="mailto:<?= e($helpEmail) ?>" class="text-issp-green-700 hover:underline"><?= e($helpEmail) ?></a></span>
        </div>
    </footer>

    <script>
        (function () {
            var btn = document.getElementById('portal-nav-toggle');
            var menu = document.getElementById('portal-mobile-menu');
            if (!btn || !menu) return;
            btn.addEventListener('click', function () {
                var open = menu.classList.toggle('hidden') === false;
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            document.querySelectorAll('[data-flash]').forEach(function (el) {
                setTimeout(function () { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; setTimeout(function () { el.remove(); }, 400); }, 4000);
            });
        })();
    </script>
</body>
</html>
