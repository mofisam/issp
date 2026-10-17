<?php
$typeStyles = ['PDF' => 'bg-red-50 text-red-600', 'DOC' => 'bg-blue-50 text-blue-600', 'IMG' => 'bg-issp-orange-50 text-issp-orange-700', 'FILE' => 'bg-gray-100 text-gray-500'];
$filtering  = $search !== '' || $categoryId || $year;
$field      = 'border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-issp-green-500/30 focus:border-issp-green-500';
?>
<div class="max-w-6xl mx-auto px-6 py-10 md:py-12">
    <div class="mb-7">
        <h1 class="font-serif text-3xl text-gray-900">Documents</h1>
        <p class="mt-1.5 text-sm text-gray-500">Reports, briefs and resources shared with your organization.</p>
    </div>

    <?php if ($hasAny): ?>
        <form method="GET" action="/portal/documents" class="flex flex-wrap items-end gap-3 mb-5">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1.5" for="doc-search">Search</label>
                <input id="doc-search" type="text" name="search" value="<?= e($search) ?>" placeholder="Search by title or description&hellip;" class="<?= $field ?> w-full">
            </div>
            <?php if ($categories): ?>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5" for="doc-category">Category</label>
                    <select id="doc-category" name="category_id" class="<?= $field ?>">
                        <option value="">All categories</option>
                        <?php foreach ($categories as $id => $name): ?>
                            <option value="<?= (int) $id ?>" <?= $categoryId === (int) $id ? 'selected' : '' ?>><?= e($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php if ($years): ?>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5" for="doc-year">Year</label>
                    <select id="doc-year" name="year" class="<?= $field ?>">
                        <option value="">All years</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= (int) $y ?>" <?= $year === (int) $y ? 'selected' : '' ?>><?= (int) $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <button type="submit" class="bg-issp-green-700 hover:bg-issp-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
            <?php if ($filtering): ?>
                <a href="/portal/documents" class="text-sm text-gray-500 hover:text-gray-700 py-2">Clear</a>
            <?php endif; ?>
        </form>
    <?php endif; ?>

    <div class="bg-white rounded-xl border border-gray-100 shadow-card overflow-hidden">
        <?php foreach ($documents as $doc): ?>
            <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
                <div class="h-11 w-11 rounded-lg flex items-center justify-center text-[11px] font-semibold flex-shrink-0 <?= $typeStyles[$doc['file_type']] ?? $typeStyles['FILE'] ?>"><?= e($doc['file_type']) ?></div>
                <div class="min-w-0 flex-1">
                    <div class="font-medium text-gray-900"><?= e($doc['title']) ?></div>
                    <?php if (!empty($doc['description'])): ?>
                        <p class="mt-0.5 text-sm text-gray-500 line-clamp-2"><?= e($doc['description']) ?></p>
                    <?php endif; ?>
                    <div class="mt-1 text-xs text-gray-400">
                        <?= e(implode(' · ', array_filter([$doc['category_name'] ?? null, $doc['publication_year'] ?? null, $doc['file_size'] ?? null]))) ?>
                    </div>
                </div>
                <a href="/portal/documents/<?= (int) $doc['id'] ?>/download" class="inline-flex items-center gap-1.5 text-sm font-medium text-issp-green-700 border border-gray-200 hover:border-issp-green-500 rounded-lg px-3.5 py-2 flex-shrink-0">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11m0 0-4-4m4 4 4-4M5 20h14"/></svg>
                    Download
                </a>
            </div>
        <?php endforeach; ?>
        <?php if (empty($documents)): ?>
            <div class="px-5 py-14 text-center">
                <?php if ($filtering): ?>
                    <p class="text-sm text-gray-500">No documents match your search or filters.</p>
                    <a href="/portal/documents" class="inline-block mt-3 text-sm font-medium text-issp-green-700">Clear search &amp; filters</a>
                <?php else: ?>
                    <p class="text-sm text-gray-500">No documents have been shared with your organization yet.</p>
                    <p class="text-xs text-gray-400 mt-1">New documents will appear here as soon as the ISSP team publishes them.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php require BASE_PATH . '/views/partials/admin-pagination.php'; ?>
</div>
