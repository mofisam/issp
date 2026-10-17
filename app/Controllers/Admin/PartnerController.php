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
use App\Models\Partner;
use App\Services\AuditLogger;
use App\Services\FileUploader;

class PartnerController extends Controller
{
    private const PER_PAGE = 15;
    private const TYPES = ['implementing_partner', 'donor', 'institutional_host', 'advisory', 'other'];

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');

        $search = trim((string) $this->input('search', ''));
        $type   = trim((string) $this->input('type', ''));
        $page   = max(1, (int) $this->input('page', 1));

        $filters  = ['partner_type' => $type ?: null];
        $total    = Partner::count($filters, $search ?: null, ['name']);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $items = Partner::paginate($page, self::PER_PAGE, $filters, $search ?: null, ['name'], 'sort_order ASC');
        foreach ($items as &$item) {
            $item['logo_url'] = $this->logoUrl($item['logo_media_id'] ?? null);
        }
        unset($item);

        $this->render('admin.partners.index', [
            'items'    => $items,
            'search'   => $search,
            'type'     => $type,
            'types'    => self::TYPES,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
        ], 'layouts.admin');
    }

    public function create(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $this->render('admin.partners.create', [
            'organizations' => Organization::all('name ASC'),
        ], 'layouts.admin');
    }

    public function store(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $validator = (new Validator($_POST))->required('name', 'Name');

        if (!$validator->passes()) {
            $_SESSION['_old'] = $_POST;
            $_SESSION['_errors'] = $validator->errors();
            $this->redirect('/admin/partners/create');
        }

        $id = Partner::create([
            'organization_id' => $this->input('organization_id') ?: null,
            'name'            => $this->input('name'),
            'partner_type'    => $this->input('partner_type', 'other'),
            'logo_media_id'   => $this->storeLogo(),
            'website_url'     => $this->input('website_url'),
            'description'     => $this->input('description'),
            'sort_order'      => (int) $this->input('sort_order', 0),
            'is_published'    => $this->input('is_published') ? 1 : 0,
        ]);

        AuditLogger::log('created', 'partners', $id);
        \App\Core\Session::flash('success', 'Partner added.');
        $this->redirect('/admin/partners');
    }

    public function edit(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $partner = Partner::find((int) $id);
        if (!$partner) {
            $this->abort(404);
        }
        $partner['logo_url'] = $this->logoUrl($partner['logo_media_id'] ?? null);
        $this->render('admin.partners.edit', [
            'partner'       => $partner,
            'organizations' => Organization::all('name ASC'),
        ], 'layouts.admin');
    }

    public function update(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $data = [
            'organization_id' => $this->input('organization_id') ?: null,
            'name'            => $this->input('name'),
            'partner_type'    => $this->input('partner_type', 'other'),
            'website_url'     => $this->input('website_url'),
            'description'     => $this->input('description'),
            'sort_order'      => (int) $this->input('sort_order', 0),
            'is_published'    => $this->input('is_published') ? 1 : 0,
        ];

        $logoId = $this->storeLogo();
        if ($logoId) {
            $data['logo_media_id'] = $logoId;
        }

        Partner::update((int) $id, $data);

        AuditLogger::log('updated', 'partners', (int) $id);
        \App\Core\Session::flash('success', 'Partner updated.');
        $this->redirect('/admin/partners');
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        Partner::softDelete((int) $id);
        AuditLogger::log('deleted', 'partners', (int) $id);
        \App\Core\Session::flash('success', 'Partner removed.');
        $this->redirect('/admin/partners');
    }

    /**
     * Resolves a media row's file_path (an absolute server path under public/) to a servable URL.
     * $mediaId is typed loosely because mysqli can hand back a numeric id as
     * either an int or a numeric string depending on the driver/column type.
     */
    private function logoUrl(int|string|null $mediaId): ?string
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

    private function storeLogo(): ?int
    {
        $file = $this->file('logo');
        if (!$file) {
            return null;
        }
        $path = FileUploader::storePublic($file, 'partners/');
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
