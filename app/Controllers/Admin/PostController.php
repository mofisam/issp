<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\AuditLogger;
use App\Services\FileUploader;
use App\Services\SlugGenerator;

class PostController extends Controller
{
    private const PER_PAGE = 15;
    private const STATUSES = ['draft', 'pending_review', 'published', 'archived'];

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');

        $search     = trim((string) $this->input('search', ''));
        $status     = trim((string) $this->input('status', ''));
        $categoryId = trim((string) $this->input('category_id', ''));
        $page       = max(1, (int) $this->input('page', 1));

        $filters  = ['status' => $status ?: null, 'category_id' => $categoryId ?: null];
        $total    = Post::count($filters, $search ?: null, ['title']);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $this->render('admin.posts.index', [
            'items'      => Post::paginate($page, self::PER_PAGE, $filters, $search ?: null, ['title']),
            'categories' => PostCategory::all('name ASC'),
            'search'     => $search,
            'status'     => $status,
            'categoryId' => $categoryId,
            'statuses'   => self::STATUSES,
            'page'       => $page,
            'lastPage'   => $lastPage,
            'total'      => $total,
        ], 'layouts.admin');
    }

    public function create(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $this->render('admin.posts.create', ['categories' => PostCategory::all('name ASC')], 'layouts.admin');
    }

    public function store(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $validator = (new Validator($_POST))
            ->required('title', 'Title')
            ->maxLength('title', 220, 'Title')
            ->required('body_content', 'Body content');

        if (!$validator->passes()) {
            $_SESSION['_old'] = $_POST;
            $_SESSION['_errors'] = $validator->errors();
            $this->redirect('/admin/posts/create');
        }

        $slug = SlugGenerator::unique((string) $this->input('title'), fn ($s) => (bool) Post::findBy('slug', $s));
        $status = (string) $this->input('status', 'draft');

        $id = Post::create([
            'category_id'       => $this->input('category_id') ?: null,
            'author_id'         => Auth::user()['id'],
            'title'             => $this->input('title'),
            'slug'              => $slug,
            'excerpt'           => $this->input('excerpt'),
            'body_content'      => $this->input('body_content'),
            'featured_media_id' => $this->storeFeaturedImage(),
            'status'            => $status,
            'published_at'      => $status === 'published' ? date('Y-m-d H:i:s') : null,
        ]);

        AuditLogger::log('created', 'posts', $id);
        \App\Core\Session::flash('success', 'Article created.');
        $this->redirect('/admin/posts');
    }

    public function edit(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $post = Post::find((int) $id);
        if (!$post) {
            $this->abort(404);
        }
        $post['featured_image_url'] = $this->mediaUrl($post['featured_media_id'] ?? null);
        $this->render('admin.posts.edit', [
            'post'       => $post,
            'categories' => PostCategory::all('name ASC'),
        ], 'layouts.admin');
    }

    /**
     * Lets a logged-in staff member view an article the way the public will
     * see it, regardless of status - so a draft can be checked before
     * publishing. Renders the same template the live /news/{slug} route uses.
     */
    public function preview(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $post = Post::find((int) $id);
        if (!$post) {
            $this->abort(404);
        }
        $post['image_url'] = $this->mediaUrl($post['featured_media_id'] ?? null);
        $this->render('pages.news-single', ['post' => $post, 'title' => $post['title'] . ' (preview)'], 'layouts.main');
    }

    public function update(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $existing = Post::find((int) $id);
        if (!$existing) {
            $this->abort(404);
        }

        $status = (string) $this->input('status', 'draft');

        $data = [
            'category_id'  => $this->input('category_id') ?: null,
            'title'        => $this->input('title'),
            'excerpt'      => $this->input('excerpt'),
            'body_content' => $this->input('body_content'),
            'status'       => $status,
            'published_at' => $status === 'published' ? ($existing['published_at'] ?? date('Y-m-d H:i:s')) : $existing['published_at'],
        ];

        $imageId = $this->storeFeaturedImage();
        if ($imageId) {
            $data['featured_media_id'] = $imageId;
        } elseif ($this->input('remove_featured_image')) {
            $data['featured_media_id'] = null;
        }

        Post::update((int) $id, $data);

        AuditLogger::log('updated', 'posts', (int) $id);
        \App\Core\Session::flash('success', 'Article updated.');
        $this->redirect('/admin/posts');
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');
        CsrfMiddleware::handle();

        Post::softDelete((int) $id);
        AuditLogger::log('deleted', 'posts', (int) $id);
        \App\Core\Session::flash('success', 'Article deleted.');
        $this->redirect('/admin/posts');
    }

    /**
     * Resolves a media row's file_path (an absolute server path under public/) to a servable URL.
     * Loosely typed because mysqli can hand back a numeric id as either an
     * int or a numeric string depending on the driver/column type.
     */
    private function mediaUrl(int|string|null $mediaId): ?string
    {
        if (!$mediaId) {
            return null;
        }
        $media = Media::find((int) $mediaId);
        if (!$media) {
            return null;
        }
        return '/' . ltrim(str_replace(BASE_PATH . '/public/', '', $media['file_path']), '/');
    }

    private function storeFeaturedImage(): ?int
    {
        $file = $this->file('featured_image');
        if (!$file) {
            return null;
        }
        $path = FileUploader::storePublic($file, 'posts/');
        if (!$path) {
            return null;
        }
        return Media::create([
            'disk'            => 'local',
            'file_path'       => $path,
            'file_name'       => $file['name'],
            'mime_type'       => mime_content_type($path),
            'file_size_bytes' => filesize($path),
            'uploaded_by'     => Auth::user()['id'],
        ]);
    }
}
