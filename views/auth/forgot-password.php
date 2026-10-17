<?php $field = 'border border-gray-200 rounded-lg px-3.5 py-2.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500'; ?>
<section class="px-6 py-14 md:py-20 bg-gray-50">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-8">
            <img src="<?= asset('img/logo.png') ?>" alt="ISSP" class="h-16 w-auto mx-auto">
            <h1 class="font-serif text-3xl text-gray-900 mt-5">Reset your password</h1>
            <p class="text-sm text-gray-500 mt-1.5">Enter your email and we'll send you a link to choose a new one.</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-card p-7 md:p-8">
            <form method="POST" action="/forgot-password" class="space-y-5">
                <?= \App\Core\Csrf::field() ?>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input id="email" type="email" name="email" required autocomplete="email" autofocus class="<?= $field ?>">
                </div>
                <button type="submit" class="w-full bg-issp-green-700 hover:bg-issp-green-600 text-white px-6 py-3 rounded-lg text-sm font-medium">Send reset link</button>
            </form>
        </div>
        <p class="text-center text-sm mt-6"><a href="/login" class="text-gray-500 hover:text-issp-green-700">&larr; Back to sign in</a></p>
    </div>
</section>
