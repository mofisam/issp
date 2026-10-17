<?php
declare(strict_types=1);

/**
 * ONE-TIME ADMIN SETUP PAGE.
 *
 * Place this file at: public/setup.php
 * Visit it once at:   https://yourdomain.com/setup.php?key=YOUR_SETUP_KEY
 *
 * It creates the first super_admin account, then locks itself automatically
 * (it refuses to run again once any staff account exists in the database).
 *
 * BEFORE USING:
 *   1. Add a line to your .env file:
 *        SETUP_KEY=pick-a-long-random-string-here
 *      (anything long and hard to guess - this is a temporary secret, not a password)
 *
 * AFTER USING:
 *   Delete this file from the server immediately. The database check means it
 *   won't create a second admin even if left in place, but a setup page with
 *   no file on disk at all is safer than one that merely refuses to run.
 */

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require BASE_PATH . '/app/Config/config.php';
require BASE_PATH . '/app/Core/Helpers.php';

use App\Core\Database;
use App\Core\Session;
use App\Models\Role;
use App\Models\User;

Session::start();

$db = Database::connection();

// --- Gate 1: has an admin already been created? -----------------------
$staffCount = (int) ($db->query("SELECT COUNT(*) AS c FROM users WHERE user_type = 'staff'")->fetch_assoc()['c'] ?? 0);
$alreadySetUp = $staffCount > 0;

// --- Gate 2: does the visitor know the setup key from .env? ------------
$setupKey   = (string) env('SETUP_KEY', '');
$providedKey = (string) ($_GET['key'] ?? $_POST['key'] ?? '');
$keyMissing  = $setupKey === '';
$keyValid    = !$keyMissing && hash_equals($setupKey, $providedKey);

$errors  = [];
$success = false;

if (!$alreadySetUp && $keyValid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $lastName  = trim((string) ($_POST['last_name'] ?? ''));
    $email     = trim((string) ($_POST['email'] ?? ''));
    $password  = (string) ($_POST['password'] ?? '');
    $confirm   = (string) ($_POST['password_confirmation'] ?? '');

    if ($firstName === '') $errors[] = 'First name is required.';
    if ($lastName === '')  $errors[] = 'Last name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Password confirmation does not match.';
    if (!$errors && User::findBy('email', $email)) $errors[] = 'That email is already in use.';

    if (!$errors) {
        $userId = User::create([
            'user_type'         => 'staff',
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'email'             => $email,
            'password_hash'     => password_hash($password, PASSWORD_DEFAULT),
            'status'            => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);

        $superAdminRole = Role::findBy('name', 'super_admin');
        if ($superAdminRole) {
            $stmt = $db->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)');
            $stmt->bind_param('ii', $userId, $superAdminRole['id']);
            $stmt->execute();
            $stmt->close();
        }

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISSP - Admin Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { "issp-green": { DEFAULT:"#1F5C3D",600:"#1F5C3D",700:"#173F2B" }, "issp-orange": { DEFAULT:"#D9711E",600:"#D9711E",700:"#B2590F" } } } } }</script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
<div class="max-w-md w-full">

    <?php if ($alreadySetUp): ?>
        <div class="bg-white border border-gray-100 rounded-lg p-8 text-center">
            <h1 class="text-xl font-bold text-issp-green-700 mb-2">Setup already completed</h1>
            <p class="text-sm text-gray-600 mb-6">An administrator account already exists. This page no longer creates new accounts.</p>
            <p class="text-sm text-gray-500 mb-6">Please delete <code class="bg-gray-100 px-1 rounded">public/setup.php</code> from the server now.</p>
            <a href="/login" class="inline-block bg-issp-green-700 hover:bg-issp-green-600 text-white px-5 py-2.5 rounded font-medium text-sm">Go to login</a>
        </div>

    <?php elseif ($keyMissing): ?>
        <div class="bg-white border border-gray-100 rounded-lg p-8 text-center">
            <h1 class="text-xl font-bold text-red-600 mb-2">Setup key not configured</h1>
            <p class="text-sm text-gray-600">Add <code class="bg-gray-100 px-1 rounded">SETUP_KEY=some-long-random-string</code> to your <code class="bg-gray-100 px-1 rounded">.env</code> file, then reload this page with <code class="bg-gray-100 px-1 rounded">?key=that-value</code> in the URL.</p>
        </div>

    <?php elseif (!$keyValid): ?>
        <div class="bg-white border border-gray-100 rounded-lg p-8 text-center">
            <h1 class="text-xl font-bold text-red-600 mb-2">Invalid or missing key</h1>
            <p class="text-sm text-gray-600">Append <code class="bg-gray-100 px-1 rounded">?key=YOUR_SETUP_KEY</code> (the value you set in <code class="bg-gray-100 px-1 rounded">.env</code>) to this page's URL.</p>
        </div>

    <?php elseif ($success): ?>
        <div class="bg-white border border-gray-100 rounded-lg p-8 text-center">
            <h1 class="text-xl font-bold text-issp-green-700 mb-2">Admin account created</h1>
            <p class="text-sm text-gray-600 mb-6">You can now log in. <strong>Delete <code class="bg-gray-100 px-1 rounded">public/setup.php</code> from the server right now</strong> - leaving it in place is an unnecessary risk even though it will refuse to run again.</p>
            <a href="/login" class="inline-block bg-issp-green-700 hover:bg-issp-green-600 text-white px-5 py-2.5 rounded font-medium text-sm">Go to login</a>
        </div>

    <?php else: ?>
        <div class="bg-white border border-gray-100 rounded-lg p-8">
            <h1 class="text-xl font-bold text-issp-green-700 mb-1">Create the admin account</h1>
            <p class="text-sm text-gray-500 mb-6">This page only works once. Remember to delete it afterward.</p>

            <?php if ($errors): ?>
                <div class="bg-red-50 text-red-700 text-sm rounded p-3 mb-4">
                    <?php foreach ($errors as $err): ?><p><?= htmlspecialchars($err) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="?key=<?= urlencode($providedKey) ?>" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <input name="first_name" required placeholder="First name"
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" class="border rounded px-3 py-2 w-full text-sm">
                    <input name="last_name" required placeholder="Last name"
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" class="border rounded px-3 py-2 w-full text-sm">
                </div>
                <input type="email" name="email" required placeholder="Email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="border rounded px-3 py-2 w-full text-sm">
                <input type="password" name="password" required minlength="8" placeholder="Password (min. 8 characters)"
                       class="border rounded px-3 py-2 w-full text-sm">
                <input type="password" name="password_confirmation" required minlength="8" placeholder="Confirm password"
                       class="border rounded px-3 py-2 w-full text-sm">
                <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-6 py-2.5 rounded font-medium text-sm">Create admin account</button>
            </form>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
