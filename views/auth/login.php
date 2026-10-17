<?php $field = 'border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500'; ?>
<section class="px-6 py-14 md:py-20 bg-gray-50">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
            <img src="<?= asset('img/logo.png') ?>" alt="ISSP" class="h-16 w-auto mx-auto">
            <h1 class="font-serif text-3xl text-gray-900 mt-5">Welcome back</h1>
            <p class="text-sm text-gray-500 mt-1.5">Sign in to the ISSP stakeholder portal.</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-7 md:p-8">
            <form method="POST" action="/login" class="space-y-5">
                <?= \App\Core\Csrf::field() ?>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input id="email" type="email" name="email" required autocomplete="username" autofocus class="<?= $field ?>">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <a href="/forgot-password" class="text-xs font-medium text-issp-green-700 hover:underline">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="current-password" class="<?= $field ?> pr-14">
                        <button type="button" id="toggle-password" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-400 hover:text-gray-600">Show</button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-6 py-3 rounded-lg text-sm font-medium">Sign in</button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            Don't have an account? Access is by invitation &mdash; contact
            <?php $helpEmail = \App\Models\SiteSetting::get('contact_email', 'project@issprogram.com'); ?><a href="mailto:<?= e($helpEmail) ?>" class="text-issp-green-700 hover:underline"><?= e($helpEmail) ?></a>.
        </p>
    </div>
    <script src="<?= asset('js/stakeholder-login.js') ?>"></script>
</section>
