<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Post;

class PostController extends Controller
{
    private const PER_PAGE = 9;

    public function index(): void
    {
        $categories = Post::categoriesInUse();
        $slug       = trim((string) $this->input('category', ''));
        $active     = null;
        foreach ($categories as $c) {
            if ($c['slug'] === $slug) {
                $active = $c;
            }
        }

        $search     = mb_substr(trim((string) $this->input('q', '')), 0, 100);
        $categoryId = $active ? (int) $active['id'] : null;
        $total      = Post::publishedCount($categoryId, $search);
        $lastPage   = max(1, (int) ceil($total / self::PER_PAGE));
        $page       = min(max(1, (int) $this->input('page', 1)), $lastPage);

        $posts = Post::publishedPaginated($page, self::PER_PAGE, $categoryId, $search);
        $names = array_column($categories, 'name', 'id');
        foreach ($posts as &$post) {
            $post['image_url']     = media_url($post['featured_media_id'] ?? null);
            $post['category_name'] = $names[$post['category_id']] ?? null;
            $post['read_minutes']  = self::readMinutes($post['body_content'] ?? '');
        }
        unset($post);

        $this->render('pages.news-index', [
            'posts'          => $posts,
            'categories'     => $categories,
            'activeCategory' => $active,
            'page'           => $page,
            'lastPage'       => $lastPage,
            'total'          => $total,
            'search'         => $search,
        ]);
    }

    public function show(string $slug): void
    {
        $post = Post::findPublishedBySlug($slug);
        if (!$post) {
            $this->abort(404);
        }
        $post['image_url']    = media_url($post['featured_media_id'] ?? null);
        $post['read_minutes'] = self::readMinutes($post['body_content'] ?? '');

        $categoryName = null;
        if (!empty($post['category_id'])) {
            foreach (Post::categoriesInUse() as $c) {
                if ((int) $c['id'] === (int) $post['category_id']) {
                    $categoryName = $c['name'];
                }
            }
        }
        $post['category_name'] = $categoryName;

        $more = Post::latest(3, (int) $post['id']);
        foreach ($more as &$m) {
            $m['image_url'] = media_url($m['featured_media_id'] ?? null);
        }
        unset($m);

        $this->render('pages.news-single', ['post' => $post, 'more' => $more]);
    }

    private static function readMinutes(string $html): int
    {
        $words = str_word_count(strip_tags($html));
        return max(1, (int) ceil($words / 220));
    }
}
