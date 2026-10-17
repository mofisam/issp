<section class="max-w-4xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold text-issp-green-700 mb-10">Frequently Asked Questions</h1>
    <div class="space-y-4">
        <?php foreach ($faqs as $faq): ?>
            <details class="border border-gray-100 rounded-lg p-4">
                <summary class="font-medium cursor-pointer"><?= e($faq['question']) ?></summary>
                <p class="text-sm text-gray-600 mt-2"><?= e($faq['answer']) ?></p>
            </details>
        <?php endforeach; ?>
    </div>
</section>
