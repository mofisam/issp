<?php $field = 'border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500'; ?>
<section class="px-6 py-14 md:py-20 bg-gray-50">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
            <img src="<?= asset('img/logo.png') ?>" alt="ISSP" class="h-16 w-auto mx-auto">
            <h1 class="font-serif text-3xl text-gray-900 mt-5">Choose a new password</h1>
            <p class="text-sm text-gray-500 mt-1.5">Use at least <?= (int) ($minLength ?? 8) ?> characters.</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-7 md:p-8">
            <form method="POST" action="/reset-password" class="space-y-5">
                <?= \App\Core\Csrf::field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">New password</label>
                    <input id="password" type="password" name="password" required minlength="<?= (int) ($minLength ?? 8) ?>" autocomplete="new-password" autofocus class="<?= $field ?>">
                </div>
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm new password</label>
                    <input id="password_confirm" type="password" name="password_confirm" required minlength="<?= (int) ($minLength ?? 8) ?>" autocomplete="new-password" class="<?= $field ?>">
                </div>
                <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-6 py-3 rounded-lg text-sm font-medium">Reset password</button>
            </form>
        </div>
    </div>
</section>
