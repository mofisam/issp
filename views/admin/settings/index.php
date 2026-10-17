<?php
/** Expects: $fields (key => [group, label]), $values (key => string). */
$old = $_SESSION['_old'] ?? [];
$restored = !empty($old);
unset($_SESSION['_old']);
foreach ($values as $k => $v) {
    if (array_key_exists($k, $old)) {
        $values[$k] = (string) $old[$k];
    }
}

$inputCls = 'peer w-full rounded-lg border border-gray-200 bg-white py-2.5 text-sm text-gray-900 placeholder:text-gray-300 focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500';

$icons = [
    'globe' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z M3 12h18 M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3Z',
    'mail'  => 'M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z M3.5 7l8.5 6 8.5-6',
    'phone' => 'M4.5 4h3.3l1.7 4.2-2.1 1.6a12 12 0 0 0 5.8 5.8l1.6-2.1 4.2 1.7v3.3a1.5 1.5 0 0 1-1.6 1.5A16 16 0 0 1 3 5.6 1.5 1.5 0 0 1 4.5 4Z',
    'pin'   => 'M12 21s7-6.1 7-11.5A7 7 0 0 0 5 9.5C5 14.9 12 21 12 21Z M12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
    'link'  => 'M9 17H7a5 5 0 0 1 0-10h2M15 7h2a5 5 0 0 1 0 10h-2M8 12h8',
    'type'  => 'M5 6V4h14v2M12 4v16M9 20h6',
    'search'=> 'M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z M16 16l5 5',
];

/** key => [type, icon, placeholder, help] */
$fieldMeta = [
    'site_name'        => ['text',  'globe', 'Insurance Sector Strengthening Program', 'Used in browser titles and link previews.'],
    'site_tagline'     => ['text',  'type',  'One line that describes the programme', 'Shown in the footer under the logo. Leave blank to use the standard programme blurb.'],
    'contact_email'    => ['email', 'mail',  'project@issprogram.com', 'Shown in the site footer and in the stakeholder portal help links.'],
    'contact_phone'    => ['tel',   'phone', '+234 000 000 0000', 'Shown in the footer and dialled when tapped on a phone.'],
    'contact_address'  => ['area',  'pin',   'Street, city, country', 'Shown in the footer. Line breaks are kept.'],
    'linkedin_url'     => ['url',   'link',  'https://www.linkedin.com/…', 'Leave blank to hide the icon.'],
    'youtube_url'      => ['url',   'link',  'https://youtube.com/@…', 'Leave blank to hide the icon.'],
    'facebook_url'     => ['url',   'link',  'https://www.facebook.com/…', 'Leave blank to hide the icon.'],
    'instagram_url'    => ['url',   'link',  'https://www.instagram.com/…', 'Leave blank to hide the icon.'],
    'twitter_url'      => ['url',   'link',  'https://x.com/…', 'Leave blank to hide the icon.'],
    'meta_description' => ['area',  'search', 'A sentence or two about the programme', 'Search engines show roughly the first 160 characters.'],
];

$sections = [
    'general' => ['General', 'Identity shown across the site.'],
    'contact' => ['Contact details', 'Appears in the public footer and portal help links.'],
    'social'  => ['Social links', 'Icons in the footer. A blank link hides its icon.'],
    'seo'     => ['Search & sharing', 'Defaults for search engines and link previews.'],
];
$groups = [];
foreach ($fields as $key => [$group, $label]) {
    $groups[$group][$key] = $label;
}
?>
<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Site settings</h1>
        <p class="text-sm text-gray-500 mt-1">Edit once &mdash; the public footer and portal pick it up straight away.</p>
    </div>
    <a href="/" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-medium text-issp-green-700 hover:underline">
        View site
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M9 7h8v8"/></svg>
    </a>
</div>

<form id="settings-form" method="POST" action="/admin/settings" class="grid xl:grid-cols-[minmax(0,1fr)_300px] gap-8 items-start pb-24">
    <?= \App\Core\Csrf::field() ?>

    <!-- Sections -->
    <div class="space-y-6 min-w-0">
        <nav class="sticky top-0 z-10 -mx-1 px-1 py-2 bg-gray-50/90 backdrop-blur flex gap-2 overflow-x-auto" aria-label="Settings sections">
            <?php foreach ($sections as $id => [$label]): ?>
                <?php if (!isset($groups[$id])) continue; ?>
                <a href="#sec-<?= $id ?>" data-nav="<?= $id ?>" class="flex-shrink-0 rounded-full border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-medium text-gray-600 hover:border-issp-green-500 hover:text-issp-green-700"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <?php foreach ($sections as $groupName => [$sectionLabel, $sectionHint]): ?>
            <?php if (!isset($groups[$groupName])) continue; ?>
            <section id="sec-<?= $groupName ?>" class="bg-white rounded-xl border border-gray-100 shadow-card scroll-mt-16">
                <header class="px-6 pt-5 pb-4 border-b border-gray-50">
                    <h2 class="font-semibold text-gray-900"><?= e($sectionLabel) ?></h2>
                    <p class="text-xs text-gray-400 mt-0.5"><?= e($sectionHint) ?></p>
                </header>
                <div class="p-6 space-y-5">
                    <?php foreach ($groups[$groupName] as $key => $label): ?>
                        <?php [$type, $icon, $placeholder, $help] = $fieldMeta[$key] ?? ['text', 'type', '', '']; ?>
                        <div>
                            <label for="f-<?= e($key) ?>" class="block text-sm font-medium text-gray-700 mb-1.5"><?= e($label) ?></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 <?= $type === 'area' ? 'top-3' : 'top-1/2 -translate-y-1/2' ?> text-gray-300 peer-focus:text-issp-green-600">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $icons[$icon] ?>"/></svg>
                                </span>
                                <?php if ($type === 'area'): ?>
                                    <textarea id="f-<?= e($key) ?>" name="<?= e($key) ?>" rows="3" placeholder="<?= e($placeholder) ?>" data-field="<?= e($key) ?>" <?= $key === 'meta_description' ? 'maxlength="300"' : '' ?> class="<?= $inputCls ?> pl-9 pr-3.5"><?= e($values[$key] ?? '') ?></textarea>
                                <?php else: ?>
                                    <input id="f-<?= e($key) ?>" type="<?= $type ?>" name="<?= e($key) ?>" value="<?= e($values[$key] ?? '') ?>" placeholder="<?= e($placeholder) ?>" data-field="<?= e($key) ?>" <?= $type === 'url' ? 'inputmode="url"' : '' ?> class="<?= $inputCls ?> pl-9 pr-3.5">
                                <?php endif; ?>
                            </div>
                            <div class="mt-1.5 flex items-start justify-between gap-3 text-xs text-gray-400">
                                <span><?= e($help) ?></span>
                                <?php if ($key === 'meta_description'): ?><span id="meta-count" class="flex-shrink-0 tabular-nums"></span><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <!-- Live preview -->
    <aside class="xl:sticky xl:top-6 space-y-4 min-w-0">
        <div>
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Footer preview</h2>
            <div class="rounded-xl bg-issp-green-800 text-white p-5 text-sm shadow-card">
                <p id="pv-tagline" class="text-white/70 text-xs leading-relaxed mb-4 line-clamp-3"></p>
                <div class="text-white/90 font-medium mb-2.5 text-xs uppercase tracking-wide">Contact</div>
                <ul class="space-y-2 text-white/70 text-[13px]">
                    <li id="pv-email-row" class="flex gap-2.5"><svg viewBox="0 0 24 24" class="h-4 w-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $icons['mail'] ?>"/></svg><span id="pv-email" class="break-all"></span></li>
                    <li id="pv-phone-row" class="flex gap-2.5"><svg viewBox="0 0 24 24" class="h-4 w-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $icons['phone'] ?>"/></svg><span id="pv-phone"></span></li>
                    <li id="pv-address-row" class="flex gap-2.5"><svg viewBox="0 0 24 24" class="h-4 w-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $icons['pin'] ?>"/></svg><span id="pv-address" class="whitespace-pre-line"></span></li>
                </ul>
                <div id="pv-socials" class="flex gap-2 mt-4"></div>
            </div>
        </div>
        <div>
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Search preview</h2>
            <div class="rounded-xl bg-white border border-gray-100 shadow-card p-4">
                <div id="pv-seo-title" class="text-[15px] text-blue-700 truncate"></div>
                <div class="text-xs text-issp-green-700 truncate"><?= e(($_SERVER['HTTP_HOST'] ?? 'issprogram.com')) ?></div>
                <p id="pv-seo-desc" class="text-xs text-gray-500 mt-1 line-clamp-3"></p>
            </div>
        </div>
    </aside>

    <!-- Save bar -->
    <div id="save-bar" class="fixed left-0 right-0 lg:left-72 bottom-0 z-20 border-t border-gray-200 bg-white/95 backdrop-blur px-4 sm:px-6 lg:px-8 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <p id="dirty-note" class="text-sm text-gray-400">All changes saved.</p>
            <div class="flex items-center gap-3">
                <button type="button" id="discard" class="hidden text-sm font-medium text-gray-500 hover:text-gray-800">Discard</button>
                <button type="submit" id="save" class="bg-issp-green-700 hover:bg-issp-green-600 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed text-white px-5 py-2 rounded-lg text-sm font-medium" disabled>Save settings</button>
            </div>
        </div>
    </div>
</form>

<script>
(function () {
    var form = document.getElementById('settings-form');
    var els = Array.prototype.slice.call(form.querySelectorAll('[data-field]'));
    var forceDirty = <?= $restored ? 'true' : 'false' ?>;
    var initial = {};
    els.forEach(function (el) { initial[el.name] = el.value; });
    var save = document.getElementById('save'), note = document.getElementById('dirty-note'), discard = document.getElementById('discard');
    var socials = [
        ['linkedin_url', 'LinkedIn', 'M6.94 5a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM3.2 8.75h3.5V21H3.2V8.75Zm6.2 0h3.35v1.68h.05c.47-.88 1.6-1.8 3.3-1.8 3.53 0 4.18 2.32 4.18 5.35V21h-3.5v-5.47c0-1.3-.02-2.98-1.82-2.98-1.82 0-2.1 1.42-2.1 2.88V21H9.4V8.75Z'],
        ['youtube_url', 'YouTube', 'M22 12c0-2.6-.25-4.18-.34-4.7a2.9 2.9 0 0 0-2.04-2.08C17.9 4.75 12 4.75 12 4.75s-5.9 0-7.62.47A2.9 2.9 0 0 0 2.34 7.3C2.25 7.82 2 9.4 2 12s.25 4.18.34 4.7a2.9 2.9 0 0 0 2.04 2.08c1.72.47 7.62.47 7.62.47s5.9 0 7.62-.47a2.9 2.9 0 0 0 2.04-2.08c.09-.52.34-2.1.34-4.7Zm-11.98 3.1v-6.2L15.5 12l-5.48 3.1Z'],
        ['facebook_url', 'Facebook', 'M13.5 21v-7.5h2.52l.38-2.93H13.5V8.7c0-.85.24-1.43 1.45-1.43h1.55V4.64c-.27-.04-1.2-.11-2.27-.11-2.25 0-3.79 1.37-3.79 3.89v2.17H8.1v2.93h2.34V21h3.06Z'],
        ['instagram_url', 'Instagram', 'M7.5 3.5h9a4 4 0 0 1 4 4v9a4 4 0 0 1-4 4h-9a4 4 0 0 1-4-4v-9a4 4 0 0 1 4-4Z M12 15.6a3.6 3.6 0 1 0 0-7.2 3.6 3.6 0 0 0 0 7.2Z'],
        ['twitter_url', 'X', 'M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.3L5.3 21H2.2l7.2-8.3L2 3h6.4l4.4 5.8L17.8 3Zm-1.1 16.2h1.7L7.4 4.7H5.6l11.1 14.5Z']
    ];
    function val(name) { return (form.elements[name] ? form.elements[name].value : '').trim(); }
    function row(id, text) { var r = document.getElementById(id + '-row'); r.classList.toggle('hidden', !text); document.getElementById(id).textContent = text; }

    function render() {
        document.getElementById('pv-tagline').textContent = val('site_tagline') || 'Insurance Sector Strengthening Program — transforming Nigeria’s insurance landscape…';
        row('pv-email', val('contact_email'));
        row('pv-phone', val('contact_phone'));
        row('pv-address', val('contact_address'));
        var box = document.getElementById('pv-socials');
        box.innerHTML = '';
        socials.forEach(function (s) {
            if (!val(s[0])) return;
            var d = document.createElement('span');
            d.className = 'h-8 w-8 rounded-full bg-white/10 flex items-center justify-center';
            d.title = s[1];
            d.innerHTML = '<svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="currentColor" stroke="none"><path d="' + s[2] + '"/></svg>';
            box.appendChild(d);
        });
        if (!box.children.length) box.innerHTML = '<span class="text-xs text-white/40">No social icons</span>';
        document.getElementById('pv-seo-title').textContent = val('site_name') || 'Site name';
        document.getElementById('pv-seo-desc').textContent = val('meta_description') || 'No description set.';
        var m = form.elements.meta_description, c = document.getElementById('meta-count');
        if (m && c) { c.textContent = m.value.length + ' / 160'; c.className = 'flex-shrink-0 tabular-nums ' + (m.value.length > 160 ? 'text-issp-orange-700' : 'text-gray-400'); }

        var dirty = forceDirty || els.some(function (el) { return el.value !== initial[el.name]; });
        save.disabled = !dirty;
        discard.classList.toggle('hidden', !dirty);
        note.textContent = dirty ? 'You have unsaved changes.' : 'All changes saved.';
        note.className = 'text-sm ' + (dirty ? 'text-issp-orange-700 font-medium' : 'text-gray-400');
    }
    els.forEach(function (el) { el.addEventListener('input', render); });
    discard.addEventListener('click', function () { els.forEach(function (el) { el.value = initial[el.name]; }); render(); });
    window.addEventListener('beforeunload', function (e) { if (!save.disabled && !form.dataset.submitting) { e.preventDefault(); e.returnValue = ''; } });
    form.addEventListener('submit', function () { form.dataset.submitting = '1'; });
    render();

    // Highlight the section in view
    var navLinks = document.querySelectorAll('[data-nav]');
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (!en.isIntersecting) return;
                navLinks.forEach(function (a) {
                    var on = a.getAttribute('data-nav') === en.target.id.replace('sec-', '');
                    a.classList.toggle('border-issp-green-500', on); a.classList.toggle('text-issp-green-700', on); a.classList.toggle('bg-issp-green-50', on);
                });
            });
        }, { rootMargin: '-20% 0px -65% 0px' });
        document.querySelectorAll('section[id^="sec-"]').forEach(function (s) { io.observe(s); });
    }
})();
</script>
