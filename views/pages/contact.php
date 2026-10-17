<section class="max-w-2xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold text-issp-green-700 mb-6">Contact Us</h1>
    <?php if (!empty($_SESSION['_errors'])): ?>
        <div class="bg-red-50 text-red-700 text-sm rounded p-4 mb-6">
            <?php foreach ($_SESSION['_errors'] as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
        </div>
        <?php unset($_SESSION['_errors']); ?>
    <?php endif; ?>
    <form id="contact-form" method="POST" action="/contact" class="space-y-4">
        <?= \App\Core\Csrf::field() ?>
        <div class="grid grid-cols-2 gap-4">
            <input name="first_name" required placeholder="First name" value="<?= old('first_name') ?>" class="border rounded px-3 py-2 w-full">
            <input name="last_name" required placeholder="Last name" value="<?= old('last_name') ?>" class="border rounded px-3 py-2 w-full">
        </div>
        <input type="email" name="email" required placeholder="Email" value="<?= old('email') ?>" class="border rounded px-3 py-2 w-full">
        <select name="audience_type" class="border rounded px-3 py-2 w-full">
            <option value="general">General enquiry</option>
            <option value="professional">Insurance professional</option>
            <option value="partner_donor">Partner / Donor</option>
            <option value="media">Media / Stakeholder</option>
            <option value="msme">MSME / Entrepreneur</option>
            <option value="regulator">Regulator / Policymaker</option>
        </select>
        <textarea name="message" required placeholder="Message" rows="5" class="border rounded px-3 py-2 w-full"></textarea>
        <button type="submit" class="bg-issp-orange-600 hover:bg-issp-orange-700 text-white px-6 py-3 rounded font-medium">Send message</button>
    </form>
</section>
