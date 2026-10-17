<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Media;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Services\AuditLogger;
use App\Services\FileUploader;
use App\Services\SlugGenerator;

class ResourceController extends Controller
{
    private const PER_PAGE = 15;
    private const VISIBILITIES = ['public', 'stakeholder', 'private'];

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');

        $search     = trim((string) $this->input('search', ''));
        $visibility = trim((string) $this->input('visibility', ''));
        $categoryId = trim((string) $this->input('category_id', ''));
        $page       = max(1, (int) $this->input('page', 1));

        $filters  = ['visibility' => $visibility ?: null, 'category_id' => $categoryId ?: null];
        $total    = Resource::count($filters, $search ?: null, ['title']);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $this->render('admin.resources.index', [
            'items'        => Resource::paginate($page, self::PER_PAGE, $filters, $search ?: null, ['title']),
            'categories'   => ResourceCategory::all('name ASC'),
            'search'       => $search,
            'visibility'   => $visibility,
            'categoryId'   => $categoryId,
            'visibilities' => self::VISIBILITIES,
            'page'         => $page,
            'lastPage'     => $lastPage,
            'total'        => $total,
        ], 'layouts.admin');
    }

    /** Data the access picker in the form needs. */
    private function accessPickerData(array $grants = ['organizations' => [], 'roles' => []]): array
    {
        $db = \App\Core\Database::connection();
        $memberCounts = [];
        $res = $db->query("SELECT organization_id, COUNT(*) c FROM users WHERE user_type = 'stakeholder' AND deleted_at IS NULL AND organization_id IS NOT NULL GROUP BY organization_id");
        foreach ($res ? $res->fetch_all(MYSQLI_ASSOC) : [] as $r) {
            $memberCounts[(int) $r['organization_id']] = (int) $r['c'];
        }
        $roles = array_values(array_filter(
            Role::all('display_name ASC'),
            fn ($r) => str_starts_with($r['name'], 'stakeholder_')
        ));
        return [
            'organizations' => Organization::all('name ASC'),
            'memberCounts'  => $memberCounts,
            'stakeholderRoles' => $roles,
            'grants'        => $grants,
        ];
    }

    public function create(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $this->render('admin.resources.create', [
            'categories' => ResourceCategory::all('name ASC'),
        ] + $this->accessPickerData(), 'layouts.admin');
    }

    public function store(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $validator = (new Validator($_POST))
            ->required('title', 'Title')
            ->maxLength('title', 220, 'Title');

        $uploaded = $this->file('file');

        if (!$validator->passes() || !$uploaded) {
            $_SESSION['_old'] = $_POST;
            $_SESSION['_errors'] = $validator->passes() ? ['A document file is required.'] : $validator->errors();
            $this->redirect('/admin/resources/create');
        }

        $mediaId = $this->storeUploadedFile($uploaded, (string) $this->input('visibility', 'public'));
        if (!$mediaId) {
            $_SESSION['_old'] = $_POST;
            $_SESSION['_errors'] = ['Could not store that file - check the file type (PDF, DOC, DOCX, JPG, PNG, WEBP only).'];
            $this->redirect('/admin/resources/create');
        }

        $slug = SlugGenerator::unique((string) $this->input('title'), fn ($s) => (bool) Resource::findBy('slug', $s));

        $id = Resource::create([
            'category_id'      => $this->input('category_id') ?: null,
            'title'            => $this->input('title'),
            'slug'             => $slug,
            'description'      => $this->input('description'),
            'file_media_id'    => $mediaId,
            'publication_year' => $this->input('publication_year') ?: null,
            'visibility'       => $this->input('visibility', 'public'),
            'uploaded_by'      => Auth::user()['id'],
        ]);

        $this->saveGrants((int) $id, (string) $this->input('visibility', 'public'));

        AuditLogger::log('created', 'resources', $id);
        \App\Core\Session::flash('success', 'Document uploaded.');
        $this->redirect('/admin/resources');
    }

    public function edit(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $resource = Resource::find((int) $id);
        if (!$resource) {
            $this->abort(404);
        }
        $media = !empty($resource['file_media_id']) ? Media::find((int) $resource['file_media_id']) : null;
        $resource['file_name'] = $media['file_name'] ?? null;

        $this->render('admin.resources.edit', [
            'resource'   => $resource,
            'categories' => ResourceCategory::all('name ASC'),
        ] + $this->accessPickerData(Resource::grantsFor((int) $id)), 'layouts.admin');
    }

    public function update(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $data = [
            'category_id'      => $this->input('category_id') ?: null,
            'title'            => $this->input('title'),
            'description'      => $this->input('description'),
            'publication_year' => $this->input('publication_year') ?: null,
            'visibility'       => $this->input('visibility', 'public'),
        ];

        $uploaded = $this->file('file');
        if ($uploaded) {
            $mediaId = $this->storeUploadedFile($uploaded, (string) $this->input('visibility', 'public'));
            if ($mediaId) {
                $data['file_media_id'] = $mediaId;
            }
        }

        Resource::update((int) $id, $data);
        $this->saveGrants((int) $id, (string) $data['visibility']);

        AuditLogger::log('updated', 'resources', (int) $id);
        \App\Core\Session::flash('success', 'Document updated.');
        $this->redirect('/admin/resources');
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        Resource::softDelete((int) $id);
        AuditLogger::log('deleted', 'resources', (int) $id);
        \App\Core\Session::flash('success', 'Document deleted.');
        $this->redirect('/admin/resources');
    }

    /** Only stakeholder-tier documents carry grants; other tiers clear them. */
    private function saveGrants(int $resourceId, string $visibility): void
    {
        $orgs = $roles = [];
        if ($visibility === 'stakeholder' && $this->input('access_mode', 'all') === 'selected') {
            $orgs  = (array) ($_POST['grant_organizations'] ?? []);
            $roles = (array) ($_POST['grant_roles'] ?? []);
        }
        Resource::syncGrants($resourceId, $orgs, $roles);
    }

    private function storeUploadedFile(array $file, string $visibility): ?int
    {
        $path = $visibility === 'public'
            ? FileUploader::storePublic($file, 'resources/')
            : FileUploader::storePrivate($file);

        if ($path === null) {
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
