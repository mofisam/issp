<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Validator;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\EventCategory;
use App\Models\PostCategory;
use App\Models\ResourceCategory;
use App\Services\AuditLogger;
use App\Services\SlugGenerator;

/**
 * One controller manages the category lists for Posts, Events, and
 * Resources - they're all the same shape (name + slug), so a single
 * {type}-driven controller avoids three near-identical copies.
 * Valid $type values are the keys of self::MAP; anything else 404s.
 */
class CategoryController extends Controller
{
    private const MAP = [
        'posts' => [
            'model'      => PostCategory::class,
            'label'      => 'Article categories',
            'backLabel'  => 'News & Updates',
            'backUrl'    => '/admin/posts',
            'entityType' => 'post_categories',
        ],
        'events' => [
            'model'      => EventCategory::class,
            'label'      => 'Event categories',
            'backLabel'  => 'Events',
            'backUrl'    => '/admin/events',
            'entityType' => 'event_categories',
        ],
        'resources' => [
            'model'      => ResourceCategory::class,
            'label'      => 'Document categories',
            'backLabel'  => 'Downloads Library',
            'backUrl'    => '/admin/resources',
            'entityType' => 'resource_categories',
        ],
    ];

    public function index(string $type): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $config = $this->config($type);

        /** @var class-string<\App\Core\Model> $model */
        $model = $config['model'];
        $this->render('admin.categories.index', [
            'type'   => $type,
            'config' => $config,
            'items'  => $model::all('name ASC'),
        ], 'layouts.admin');
    }

    public function store(string $type): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();
        $config = $this->config($type);
        /** @var class-string<\App\Core\Model> $model */
        $model = $config['model'];

        $validator = (new Validator($_POST))->required('name', 'Name');
        if (!$validator->passes()) {
            \App\Core\Session::flash('error', 'Category name is required.');
            $this->redirect('/admin/categories/' . $type);
        }

        $name = (string) $this->input('name');
        $slug = SlugGenerator::unique($name, fn ($s) => (bool) $model::findBy('slug', $s));

        $data = ['name' => $name, 'slug' => $slug];
        if ($type === 'posts' || $type === 'resources') {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $id = $model::create($data);
        AuditLogger::log('created', $config['entityType'], $id);
        \App\Core\Session::flash('success', 'Category added.');
        $this->redirect('/admin/categories/' . $type);
    }

    public function edit(string $type, string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $config = $this->config($type);
        /** @var class-string<\App\Core\Model> $model */
        $model = $config['model'];

        $category = $model::find((int) $id);
        if (!$category) {
            $this->abort(404);
        }

        $this->render('admin.categories.edit', [
            'type'     => $type,
            'config'   => $config,
            'category' => $category,
        ], 'layouts.admin');
    }

    public function update(string $type, string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();
        $config = $this->config($type);
        /** @var class-string<\App\Core\Model> $model */
        $model = $config['model'];

        $validator = (new Validator($_POST))->required('name', 'Name');
        if (!$validator->passes()) {
            \App\Core\Session::flash('error', 'Category name is required.');
            $this->redirect('/admin/categories/' . $type . '/' . $id . '/edit');
        }

        $model::update((int) $id, ['name' => $this->input('name')]);
        AuditLogger::log('updated', $config['entityType'], (int) $id);
        \App\Core\Session::flash('success', 'Category updated.');
        $this->redirect('/admin/categories/' . $type);
    }

    public function destroy(string $type, string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');
        CsrfMiddleware::handle();
        $config = $this->config($type);
        /** @var class-string<\App\Core\Model> $model */
        $model = $config['model'];

        // Hard delete: these tables have no deleted_at column, and the
        // foreign keys on posts/events/resources are ON DELETE SET NULL,
        // so items using this category simply become uncategorised.
        $model::delete((int) $id);
        AuditLogger::log('deleted', $config['entityType'], (int) $id);
        \App\Core\Session::flash('success', 'Category deleted.');
        $this->redirect('/admin/categories/' . $type);
    }

    /** @return array{model:string,label:string,backLabel:string,backUrl:string,entityType:string} */
    private function config(string $type): array
    {
        if (!isset(self::MAP[$type])) {
            $this->abort(404);
        }
        return self::MAP[$type];
    }
}
