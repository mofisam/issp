<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Page;
use App\Models\ProgrammePillar;

/**
 * Reference implementation - the pattern every other public controller
 * (Post, Resource, Event, ...) follows: look up by slug, 404 if missing or
 * unpublished, render the matching view.
 */
class PageController extends Controller
{
    /**
     * A dedicated, hand-built template (same pattern as the homepage)
     * rather than the generic pages.page template, so the programme's
     * flagship page gets full design control. The underlying `pages` row
     * still supplies the title/meta tags for SEO and is still editable
     * from Admin > Pages for that purpose.
     */
    public function about(): void
    {
        $page = Page::findPublishedBySlug('about');
        if (!$page) {
            $this->abort(404);
        }
        $this->render('pages.about', [
            'page'            => $page,
            'pillars'         => ProgrammePillar::published(),
            'title'           => ($page['meta_title'] ?: $page['title']) . ' | ISSP',
            'metaDescription' => $page['meta_description'] ?: $page['excerpt'],
        ]);
    }

    /** The six-pillar framework in full - not tied to a `pages` row, so it always renders. */
    public function pillars(): void
    {
        $this->render('pages.programme-pillars', [
            'pillars'         => ProgrammePillar::published(),
            'title'           => 'Six Pillars of Transformation | ISSP',
            'metaDescription' => "The ISSP's six-pillar framework for strengthening Nigeria's insurance sector.",
        ]);
    }

    public function show(string $slug): void
    {
        $page = Page::findPublishedBySlug($slug);
        if (!$page) {
            $this->abort(404);
        }
        $page['featured_image_url'] = media_url($page['featured_media_id'] ?? null);
        $this->render('pages.page', ['page' => $page]);
    }
}
