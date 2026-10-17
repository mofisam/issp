<?php
use App\Models\SiteSetting;

$ftEmail   = SiteSetting::get('contact_email', 'project@issprogram.com');
$ftPhone   = SiteSetting::get('contact_phone', '+234 909 563 5335');
$ftAddress = SiteSetting::get('contact_address', 'Lagos, Nigeria');
$ftTagline = SiteSetting::get('site_tagline');
$ftPhoneHref = 'tel:' . preg_replace('/[^0-9+]/', '', (string) $ftPhone);
$ftSocials = [
    'linkedin_url'  => SiteSetting::get('linkedin_url', 'https://www.linkedin.com/groups/42840128/'),
    'youtube_url'   => SiteSetting::get('youtube_url', 'https://youtube.com/@issprogram01?si=x7j8hrH65OdgFPbm'),
    'facebook_url'  => SiteSetting::get('facebook_url', 'https://www.facebook.com/profile.php?id=61593538892295'),
    'instagram_url' => SiteSetting::get('instagram_url', 'https://www.instagram.com/issprogram/'),
    'twitter_url'   => SiteSetting::get('twitter_url'),
];
?>
<footer class="bg-issp-green-800 text-white mt-20">
    <div class="max-w-6xl mx-auto px-6 py-14 grid gap-10 sm:grid-cols-2 lg:grid-cols-4 text-sm">
        <div>
            <div class="mb-3">
                <img src="<?= asset('img/logo.png') ?>" alt="ISSP" class="h-10 w-auto">
            </div>
            <p class="text-white/70 leading-relaxed"><?= $ftTagline ? e($ftTagline) : 'Insurance Sector Strengthening Program &mdash; transforming Nigeria\'s insurance landscape and driving penetration from 0.5% to 10% of GDP by 2031.' ?></p>
            <div class="flex items-center gap-3 mt-5">
                <?php if ($ftSocials['linkedin_url']): ?>
                <a href="<?= e($ftSocials['linkedin_url']) ?>" target="_blank" rel="noopener" aria-label="ISSP on LinkedIn" class="h-9 w-9 rounded-full bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M6.94 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM3.2 8.75h3.5V21H3.2V8.75Zm6.2 0h3.35v1.68h.05c.47-.88 1.6-1.8 3.3-1.8 3.53 0 4.18 2.32 4.18 5.35V21h-3.5v-5.47c0-1.3-.02-2.98-1.82-2.98-1.82 0-2.1 1.42-2.1 2.88V21H9.4V8.75Z"/></svg>
                </a>
                <?php endif; ?>
                <?php if ($ftSocials['youtube_url']): ?>
                <a href="<?= e($ftSocials['youtube_url']) ?>" target="_blank" rel="noopener" aria-label="ISSP on YouTube" class="h-9 w-9 rounded-full bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M22 12c0-2.6-.25-4.18-.34-4.7a2.9 2.9 0 0 0-2.04-2.08C17.9 4.75 12 4.75 12 4.75s-5.9 0-7.62.47A2.9 2.9 0 0 0 2.34 7.3C2.25 7.82 2 9.4 2 12s.25 4.18.34 4.7a2.9 2.9 0 0 0 2.04 2.08c1.72.47 7.62.47 7.62.47s5.9 0 7.62-.47a2.9 2.9 0 0 0 2.04-2.08c.09-.52.34-2.1.34-4.7Zm-11.98 3.1v-6.2L15.5 12l-5.48 3.1Z"/></svg>
                </a>
                <?php endif; ?>
                <?php if ($ftSocials['facebook_url']): ?>
                <a href="<?= e($ftSocials['facebook_url']) ?>" target="_blank" rel="noopener" aria-label="ISSP on Facebook" class="h-9 w-9 rounded-full bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M13.5 21v-7.5h2.52l.38-2.93H13.5V8.7c0-.85.24-1.43 1.45-1.43h1.55V4.64c-.27-.04-1.2-.11-2.27-.11-2.25 0-3.79 1.37-3.79 3.89v2.17H8.1v2.93h2.34V21h3.06Z"/></svg>
                </a>
                <?php endif; ?>
                <?php if ($ftSocials['instagram_url']): ?>
                <a href="<?= e($ftSocials['instagram_url']) ?>" target="_blank" rel="noopener" aria-label="ISSP on Instagram" class="h-9 w-9 rounded-full bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="3.6"/><circle cx="17.1" cy="6.9" r="0.9" fill="currentColor" stroke="none"/></svg>
                </a>
                <?php endif; ?>
                <?php if ($ftSocials['twitter_url']): ?>
                <a href="<?= e($ftSocials['twitter_url']) ?>" target="_blank" rel="noopener" aria-label="ISSP on X" class="h-9 w-9 rounded-full bg-white/10 hover:bg-white/20 transition-colors flex items-center justify-center">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor"><path d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.3L5.3 21H2.2l7.2-8.3L2 3h6.4l4.4 5.8L17.8 3Zm-1.1 16.2h1.7L7.4 4.7H5.6l11.1 14.5Z"/></svg>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="font-medium mb-3 text-white/90">Quick links</div>
            <ul class="space-y-2 text-white/70">
                <li><a href="/about" class="hover:text-white">About the programme</a></li>
                <li><a href="/programme-pillars" class="hover:text-white">Six pillars</a></li>
                <li><a href="/news" class="hover:text-white">News &amp; updates</a></li>
                <li><a href="/events" class="hover:text-white">Events</a></li>
                <li><a href="/resources" class="hover:text-white">Resources</a></li>
                <li><a href="/partners" class="hover:text-white">Partners</a></li>
            </ul>
        </div>

        <div>
            <div class="font-medium mb-3 text-white/90">Contact</div>
            <ul class="space-y-2.5 text-white/70">
                <?php if ($ftEmail): ?>
                <li class="flex items-start gap-2.5">
                    <svg viewBox="0 0 24 24" class="h-4 w-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M3.5 7l8.5 6 8.5-6"/></svg>
                    <a href="mailto:<?= e($ftEmail) ?>" class="hover:text-white break-all"><?= e($ftEmail) ?></a>
                </li>
                <?php endif; ?>
                <?php if ($ftPhone): ?>
                <li class="flex items-start gap-2.5">
                    <svg viewBox="0 0 24 24" class="h-4 w-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 4h3.3l1.7 4.2-2.1 1.6a12 12 0 0 0 5.8 5.8l1.6-2.1 4.2 1.7v3.3a1.5 1.5 0 0 1-1.6 1.5A16 16 0 0 1 3 5.6 1.5 1.5 0 0 1 4.5 4Z"/></svg>
                    <a href="<?= e($ftPhoneHref) ?>" class="hover:text-white"><?= e($ftPhone) ?></a>
                </li>
                <?php endif; ?>
                <?php if ($ftAddress): ?>
                <li class="flex items-start gap-2.5">
                    <svg viewBox="0 0 24 24" class="h-4 w-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.1 7-11.5A7 7 0 0 0 5 9.5C5 14.9 12 21 12 21Z M12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>
                    <span><?= nl2br(e($ftAddress)) ?></span>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <div>
            <div class="font-medium mb-3 text-white/90">Stay updated</div>
            <p class="text-white/70 mb-3">Programme news and event updates, straight to your inbox.</p>
            <form id="newsletter-form" class="flex gap-2">
                <input type="email" name="email" required placeholder="Your email"
                       class="flex-1 min-w-0 rounded-lg px-3 py-2 text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-issp-orange-500">
                <button type="submit" class="bg-issp-orange-600 hover:bg-issp-orange-700 transition-colors px-4 py-2 rounded-lg text-sm font-medium flex-shrink-0">Subscribe</button>
            </form>
            <p id="newsletter-status" class="text-sm mt-2"></p>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="max-w-6xl mx-auto px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-center text-xs text-white/60">
            <span>&copy; <?= date('Y') ?> ISSP. Program Owner: EMDI Capacity Development Limited.</span>
            <span class="flex items-center gap-4">
                <a href="/contact" class="hover:text-white">Contact us</a>
                <a href="/login" class="hover:text-white">Stakeholder login</a>
            </span>
        </div>
    </div>
</footer>
