<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'ISSP - Insurance Sector Strengthening Program') ?></title>
    <meta name="description" content="<?= e($metaDescription ?? "Transforming Nigeria's insurance landscape.") ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Newsreader:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
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
    <!-- Production: swap the Tailwind CDN tag above for the compiled build below -->
    <!-- <link rel="stylesheet" href="<?= asset('css/tailwind.css') ?>"> -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="font-sans text-gray-800 bg-white" style="font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;">
    <?php require BASE_PATH . '/views/partials/nav.php'; ?>
    <?php require BASE_PATH . '/views/partials/flash-messages.php'; ?>

    <main>
        <?php $content(); ?>
    </main>

    <?php require BASE_PATH . '/views/partials/footer.php'; ?>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/newsletter.js') ?>"></script>
</body>
</html>
