<section class="max-w-6xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold text-issp-green-700 mb-2">Downloads Library</h1>
    <p class="text-gray-600 mb-10">Policy briefs, reports, and concept notes. Some documents require a stakeholder login.</p>
    <div class="grid md:grid-cols-2 gap-4">
        <?php foreach ($resources as $resource): ?>
            <div class="flex items-center justify-between border border-gray-100 rounded-lg p-5">
                <div>
                    <div class="font-medium"><?= e($resource['title']) ?></div>
                    <div class="text-xs text-gray-500 mt-1"><?= e($resource['visibility']) ?> &middot; <?= (int) $resource['download_count'] ?> downloads</div>
                </div>
                <a href="/resources/<?= (int) $resource['id'] ?>/download" class="text-sm font-medium text-issp-orange-600 hover:underline">Download</a>
            </div>
        <?php endforeach; ?>
        <?php if (empty($resources)): ?>
            <p class="text-gray-500 text-sm">No public documents available yet. <a href="/login" class="text-issp-orange-600">Log in</a> if you are a registered stakeholder.</p>
        <?php endif; ?>
    </div>
</section>
