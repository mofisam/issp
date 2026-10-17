<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ProgrammePillar;
use App\Models\ImpactMetric;
use App\Models\Faq;
use App\Models\Event;
use App\Models\Post;
use App\Models\Partner;

class HomeController extends Controller
{
    public function index(): void
    {
        $posts = Post::publishedPaginated(1, 3);
        foreach ($posts as &$post) {
            $post['image_url'] = media_url($post['featured_media_id'] ?? null);
        }
        unset($post);

        $partners = array_slice(Partner::published(), 0, 8);
        foreach ($partners as &$partner) {
            $partner['logo_url'] = media_url($partner['logo_media_id'] ?? null);
        }
        unset($partner);

        $this->render('pages.home', [
            'pillars'  => ProgrammePillar::published(),
            'metrics'  => ImpactMetric::published(),
            'faqs'     => Faq::published(),
            'events'   => array_slice(Event::upcoming(), 0, 3),
            'posts'    => $posts,
            'partners' => $partners,
        ]);
    }
}
