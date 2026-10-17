<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Validator;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;

class UserController extends Controller
{
    private const PER_PAGE = 15;
    private const USER_TYPES = ['staff', 'stakeholder'];
    private const STATUSES = ['active', 'inactive', 'suspended'];

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin');

        $search   = trim((string) $this->input('search', ''));
        $userType = trim((string) $this->input('user_type', ''));
        $status   = trim((string) $this->input('status', ''));
        $role     = trim((string) $this->input('role', ''));
        $page     = max(1, (int) $this->input('page', 1));

        $filters = ['user_type' => $userType ?: null, 'status' => $status ?: null];
        if ($role !== '') {
            $filters['id'] = User::idsWithRole($role) ?: [0];
        }

        $searchColumns = ['first_name', 'last_name', 'email'];
        $total    = User::count($filters, $search ?: null, $searchColumns);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $items = User::paginate($page, self::PER_PAGE, $filters, $search ?: null, $searchColumns);
        foreach ($items as &$item) {
            $item['role_names'] = User::roleNamesFor((int) $item['id']);
        }
        unset($item);

        $this->render('admin.users.index', [
            'items'     => $items,
            'search'    => $search,
            'userType'  => $userType,
            'status'    => $status,
            'role'      => $role,
            'userTypes' => self::USER_TYPES,
            'statuses'  => self::STATUSES,
            'roles'     => Role::all('name ASC'),
            'page'      => $page,
            'lastPage'  => $lastPage,
            'total'     => $total,
        ], 'layouts.admin');
    }

    public function create(): void
    {
        AdminMiddleware::requireRole('super_admin');
        $this->render('admin.users.create', [
            'roles'         => Role::all('name ASC'),
            'organizations' => Organization::all('name ASC'),
        ], 'layouts.admin');
    }

    public function store(): void
    {
        AdminMiddleware::requireRole('super_admin');
        CsrfMiddleware::handle();

        $validator = (new Validator($_POST))
            ->required('first_name', 'First name')
            ->required('last_name', 'Last name')
            ->required('email', 'Email')
            ->email('email', 'Email');

        $password = (string) $this->input('password', '');
        if (strlen($password) < 8) {
            $_SESSION['_errors']['password'] = 'Password must be at least 8 characters.';
        }
        if (!$validator->passes() || !empty($_SESSION['_errors']['password'])) {
            if ($validator->errors()) {
                $_SESSION['_errors'] = array_merge($_SESSION['_errors'] ?? [], $validator->errors());
            }
            $_SESSION['_old'] = $_POST;
            $this->redirect('/admin/users/create');
        }

        if (User::findBy('email', (string) $this->input('email'))) {
            $_SESSION['_old'] = $_POST;
            $_SESSION['_errors'] = ['That email is already in use.'];
            $this->redirect('/admin/users/create');
        }

        $userType = (string) $this->input('user_type', 'staff');

        $userId = User::create([
            'organization_id'    => $userType === 'stakeholder' ? ($this->input('organization_id') ?: null) : null,
            'user_type'          => $userType,
            'first_name'         => $this->input('first_name'),
            'last_name'          => $this->input('last_name'),
            'email'              => $this->input('email'),
            'job_title'          => $this->input('job_title'),
            'password_hash'      => password_hash($password, PASSWORD_DEFAULT),
            'status'             => $this->input('status', 'active'),
            'email_verified_at'  => date('Y-m-d H:i:s'),
        ]);

        $roleIds = array_map('intval', (array) ($_POST['roles'] ?? []));
        User::syncRoles($userId, $roleIds);

        AuditLogger::log('created', 'users', $userId);
        \App\Core\Session::flash('success', 'User created.');
        $this->redirect('/admin/users');
    }

    public function edit(string $id): void
    {
        AdminMiddleware::requireRole('super_admin');
        $user = User::find((int) $id);
        if (!$user) {
            $this->abort(404);
        }
        $this->render('admin.users.edit', [
            'editUser'      => $user,
            'roles'         => Role::all('name ASC'),
            'organizations' => Organization::all('name ASC'),
            'assignedRoles' => User::roleIdsFor((int) $id),
        ], 'layouts.admin');
    }

    public function update(string $id): void
    {
        AdminMiddleware::requireRole('super_admin');
        CsrfMiddleware::handle();

        $userType = (string) $this->input('user_type', 'staff');

        $data = [
            'organization_id' => $userType === 'stakeholder' ? ($this->input('organization_id') ?: null) : null,
            'user_type'       => $userType,
            'first_name'      => $this->input('first_name'),
            'last_name'       => $this->input('last_name'),
            'email'           => $this->input('email'),
            'job_title'       => $this->input('job_title'),
            'status'          => $this->input('status', 'active'),
        ];

        $password = (string) $this->input('password', '');
        if ($password !== '') {
            if (strlen($password) < 8) {
                \App\Core\Session::flash('error', 'Password must be at least 8 characters - it was not changed.');
            } else {
                $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        User::update((int) $id, $data);

        $roleIds = array_map('intval', (array) ($_POST['roles'] ?? []));
        User::syncRoles((int) $id, $roleIds);

        AuditLogger::log('updated', 'users', (int) $id);
        \App\Core\Session::flash('success', 'User updated.');
        $this->redirect('/admin/users');
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::requireRole('super_admin');
        CsrfMiddleware::handle();

        if ((int) $id === (int) \App\Core\Auth::user()['id']) {
            \App\Core\Session::flash('error', 'You cannot delete your own account while logged in.');
            $this->redirect('/admin/users');
        }

        User::softDelete((int) $id);
        AuditLogger::log('deleted', 'users', (int) $id);
        \App\Core\Session::flash('success', 'User deactivated.');
        $this->redirect('/admin/users');
    }
}
