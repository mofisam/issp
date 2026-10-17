<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Validator;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Organization;
use App\Services\AuditLogger;

class OrganizationController extends Controller
{
    private const PER_PAGE = 15;
    private const TYPES = ['insurer', 'regulator', 'partner', 'donor', 'government', 'other'];

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');

        $search = trim((string) $this->input('search', ''));
        $type   = trim((string) $this->input('type', ''));
        $active = trim((string) $this->input('active', ''));
        $page   = max(1, (int) $this->input('page', 1));

        $filters  = ['organization_type' => $type ?: null, 'is_active' => $active !== '' ? $active : null];
        $total    = Organization::count($filters, $search ?: null, ['name']);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $this->render('admin.organizations.index', [
            'items'    => Organization::paginate($page, self::PER_PAGE, $filters, $search ?: null, ['name'], 'name ASC'),
            'search'   => $search,
            'type'     => $type,
            'active'   => $active,
            'types'    => self::TYPES,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
        ], 'layouts.admin');
    }

    public function create(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');
        $this->render('admin.organizations.create', [], 'layouts.admin');
    }

    public function store(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');
        CsrfMiddleware::handle();

        $validator = (new Validator($_POST))->required('name', 'Name');

        if (!$validator->passes()) {
            $_SESSION['_old'] = $_POST;
            $_SESSION['_errors'] = $validator->errors();
            $this->redirect('/admin/organizations/create');
        }

        $id = Organization::create([
            'name'                => $this->input('name'),
            'organization_type'   => $this->input('organization_type', 'other'),
            'registration_number' => $this->input('registration_number'),
            'website_url'         => $this->input('website_url'),
            'contact_email'       => $this->input('contact_email'),
            'contact_phone'       => $this->input('contact_phone'),
            'address'             => $this->input('address'),
            'country'             => $this->input('country', 'Nigeria'),
            'is_active'           => $this->input('is_active') ? 1 : 0,
        ]);

        AuditLogger::log('created', 'organizations', $id);
        \App\Core\Session::flash('success', 'Organization added.');
        $this->redirect('/admin/organizations');
    }

    public function edit(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');
        $org = Organization::find((int) $id);
        if (!$org) {
            $this->abort(404);
        }
        $this->render('admin.organizations.edit', ['organization' => $org], 'layouts.admin');
    }

    public function update(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');
        CsrfMiddleware::handle();

        Organization::update((int) $id, [
            'name'                => $this->input('name'),
            'organization_type'   => $this->input('organization_type', 'other'),
            'registration_number' => $this->input('registration_number'),
            'website_url'         => $this->input('website_url'),
            'contact_email'       => $this->input('contact_email'),
            'contact_phone'       => $this->input('contact_phone'),
            'address'             => $this->input('address'),
            'country'             => $this->input('country', 'Nigeria'),
            'is_active'           => $this->input('is_active') ? 1 : 0,
        ]);

        AuditLogger::log('updated', 'organizations', (int) $id);
        \App\Core\Session::flash('success', 'Organization updated.');
        $this->redirect('/admin/organizations');
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::requireRole('super_admin');
        CsrfMiddleware::handle();

        Organization::softDelete((int) $id);
        AuditLogger::log('deleted', 'organizations', (int) $id);
        \App\Core\Session::flash('success', 'Organization removed.');
        $this->redirect('/admin/organizations');
    }
}
