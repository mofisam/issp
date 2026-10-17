<?php use App\Core\Auth; ?>
<header class="border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="/" class="flex items-center">
            <img src="<?= asset('img/logo.png') ?>" alt="ISSP - Insurance Sector Strengthening Program" class="h-10 w-auto">
        </a>
        <nav class="hidden md:flex items-center space-x-6 text-sm text-gray-700">
            <a href="/" class="hover:text-issp-green-700">Home</a>
            <a href="/about" class="hover:text-issp-green-700">About</a>
            <a href="/programme-pillars" class="hover:text-issp-green-700">Pillars</a>
            <a href="/news" class="hover:text-issp-green-700">News</a>
            <a href="/resources" class="hover:text-issp-green-700">Resources</a>
            <a href="/events" class="hover:text-issp-green-700">Events</a>
            <a href="/team" class="hover:text-issp-green-700">Team</a>
            <a href="/partners" class="hover:text-issp-green-700">Partners</a>
            <a href="/contact" class="hover:text-issp-green-700">Contact</a>
            <?php if (Auth::check()): ?>
                <a href="<?= Auth::isStaff() ? '/admin' : '/portal' ?>" class="font-medium text-issp-orange-600">My account</a>
            <?php else: ?>
                <a href="/login" class="font-medium text-issp-orange-600">Stakeholder login</a>
            <?php endif; ?>
        </nav>
        <button id="nav-toggle" class="md:hidden" aria-label="Toggle menu">&#9776;</button>
    </div>
    <div id="mobile-menu" class="hidden md:hidden px-6 pb-4 space-y-2 text-sm">
        <a href="/" class="block">Home</a>
        <a href="/about" class="block">About</a>
        <a href="/programme-pillars" class="block">Pillars</a>
        <a href="/news" class="block">News</a>
        <a href="/resources" class="block">Resources</a>
        <a href="/events" class="block">Events</a>
        <a href="/team" class="block">Team</a>
        <a href="/partners" class="block">Partners</a>
        <a href="/contact" class="block">Contact</a>
        <a href="/login" class="block font-medium text-issp-orange-600">Stakeholder login</a>
    </div>
</header>
