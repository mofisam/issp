<section class="max-w-6xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold text-issp-green-700 mb-10">Our Team</h1>
    <div class="grid md:grid-cols-3 gap-8">
        <?php foreach ($members as $member): ?>
            <div class="text-center">
                <div class="w-28 h-28 mx-auto rounded-full bg-gray-100 mb-3"></div>
                <div class="font-semibold"><?= e($member['full_name']) ?></div>
                <div class="text-sm text-issp-orange-600"><?= e($member['role_title']) ?></div>
                <p class="text-sm text-gray-600 mt-2"><?= e($member['bio'] ?? '') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
