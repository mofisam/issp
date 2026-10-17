<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Media;
use App\Models\Page;
use App\Services\AuditLogger;
use App\Services\FileUploader;
use App\Services\SlugGenerator;

class PageController extends Controller
{
    private const PER_PAGE = 15;
    private const STATUSES = ['draft', 'pending_review', 'published', 'archived'];

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');

        $search = trim((string) $this->input('search', ''));
        $status = trim((string) $this->input('status', ''));
        $page   = max(1, (int) $this->input('page', 1));

        $filters  = ['status' => $status ?: null];
        $total    = Page::count($filters, $search ?: null, ['title']);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $this->render('admin.pages.index', [
            'pages'    => Page::paginate($page, self::PER_PAGE, $filters, $search ?: null, ['title'], 'sort_order ASC'),
            'search'   => $search,
            'status'   => $status,
            'statuses' => self::STATUSES,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
        ], 'layouts.admin');
    }

    public function create(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $this->render('admin.pages.create', [], 'layouts.admin');
    }

    public function store(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $validator = (new Validator($_POST))
            ->required('title', 'Title')
            ->maxLength('title', 200, 'Title')
            ->required('body_content', 'Body content');

        if (!$validator->passes()) {
            $_SESSION['_old'] = $_POST;
            $_SESSION['_errors'] = $validator->errors();
            $this->redirect('/admin/pages/create');
        }

        $slug = SlugGenerator::unique((string) $this->input('title'), fn ($s) => (bool) Page::findBy('slug', $s));

        $id = Page::create([
            'title'             => $this->input('title'),
            'slug'              => $slug,
            'excerpt'           => $this->input('excerpt'),
            'body_content'      => $this->input('body_content'),
            'featured_media_id' => $this->storeFeaturedImage(),
            'status'            => $this->input('status', 'draft'),
            'created_by'        => Auth::user()['id'],
        ]);

        AuditLogger::log('created', 'pages', $id);
        \App\Core\Session::flash('success', 'Page created.');
        $this->redirect('/admin/pages');
    }

    public function edit(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $page = Page::find((int) $id);
        if (!$page) {
            $this->abort(404);
        }
        $page['featured_image_url'] = $this->mediaUrl($page['featured_media_id'] ?? null);
        $this->render('admin.pages.edit', ['page' => $page], 'layouts.admin');
    }

    /**
     * Lets a logged-in staff member view a page the way the public will see
     * it, regardless of status - so a draft can be checked before publishing.
     * Renders the same public template the live /pages/{slug} route uses.
     */
    public function preview(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $page = Page::find((int) $id);
        if (!$page) {
            $this->abort(404);
        }
        $page['featured_image_url'] = $this->mediaUrl($page['featured_media_id'] ?? null);
        $this->render('pages.page', ['page' => $page, 'title' => $page['title'] . ' (preview)'], 'layouts.main');
    }

    public function update(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $data = [
            'title'        => $this->input('title'),
            'excerpt'      => $this->input('excerpt'),
            'body_content' => $this->input('body_content'),
            'status'       => $this->input('status', 'draft'),
            'updated_by'   => Auth::user()['id'],
        ];

        $imageId = $this->storeFeaturedImage();
        if ($imageId) {
            $data['featured_media_id'] = $imageId;
        } elseif ($this->input('remove_featured_image')) {
            $data['featured_media_id'] = null;
        }

        Page::update((int) $id, $data);

        AuditLogger::log('updated', 'pages', (int) $id);
        \App\Core\Session::flash('success', 'Page updated.');
        $this->redirect('/admin/pages');
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');
        CsrfMiddleware::handle();

        Page::softDelete((int) $id);
        AuditLogger::log('deleted', 'pages', (int) $id);
        \App\Core\Session::flash('success', 'Page deleted.');
        $this->redirect('/admin/pages');
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
        $path = FileUploader::storePublic($file, 'pages/');
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
