<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Validator;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Media;
use App\Models\TeamMember;
use App\Services\AuditLogger;
use App\Services\FileUploader;

class TeamController extends Controller
{
    private const PER_PAGE = 15;
    private const GROUPS = ['leadership', 'advisory_board', 'secretariat'];

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');

        $search = trim((string) $this->input('search', ''));
        $group  = trim((string) $this->input('group', ''));
        $page   = max(1, (int) $this->input('page', 1));

        $filters  = ['group' => $group ?: null];
        $total    = TeamMember::count($filters, $search ?: null, ['full_name']);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $items = TeamMember::paginate($page, self::PER_PAGE, $filters, $search ?: null, ['full_name'], 'sort_order ASC');
        foreach ($items as &$item) {
            $item['photo_url'] = $this->mediaUrl($item['photo_media_id'] ?? null);
        }
        unset($item);

        $this->render('admin.team.index', [
            'items'    => $items,
            'search'   => $search,
            'group'    => $group,
            'groups'   => self::GROUPS,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
        ], 'layouts.admin');
    }

    public function create(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $this->render('admin.team.create', [], 'layouts.admin');
    }

    public function store(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $validator = (new Validator($_POST))
            ->required('full_name', 'Full name')
            ->required('role_title', 'Role / title');

        if (!$validator->passes()) {
            $_SESSION['_old'] = $_POST;
            $_SESSION['_errors'] = $validator->errors();
            $this->redirect('/admin/team/create');
        }

        $id = TeamMember::create([
            'full_name'      => $this->input('full_name'),
            'role_title'     => $this->input('role_title'),
            'group'          => $this->input('group', 'leadership'),
            'bio'            => $this->input('bio'),
            'photo_media_id' => $this->storePhoto(),
            'linkedin_url'   => $this->input('linkedin_url'),
            'email'          => $this->input('email'),
            'sort_order'     => (int) $this->input('sort_order', 0),
            'is_published'   => $this->input('is_published') ? 1 : 0,
        ]);

        AuditLogger::log('created', 'team_members', $id);
        \App\Core\Session::flash('success', 'Team member added.');
        $this->redirect('/admin/team');
    }

    public function edit(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $member = TeamMember::find((int) $id);
        if (!$member) {
            $this->abort(404);
        }
        $member['photo_url'] = $this->mediaUrl($member['photo_media_id'] ?? null);
        $this->render('admin.team.edit', ['member' => $member], 'layouts.admin');
    }

    public function update(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $data = [
            'full_name'    => $this->input('full_name'),
            'role_title'   => $this->input('role_title'),
            'group'        => $this->input('group', 'leadership'),
            'bio'          => $this->input('bio'),
            'linkedin_url' => $this->input('linkedin_url'),
            'email'        => $this->input('email'),
            'sort_order'   => (int) $this->input('sort_order', 0),
            'is_published' => $this->input('is_published') ? 1 : 0,
        ];

        $photoId = $this->storePhoto();
        if ($photoId) {
            $data['photo_media_id'] = $photoId;
        }

        TeamMember::update((int) $id, $data);

        AuditLogger::log('updated', 'team_members', (int) $id);
        \App\Core\Session::flash('success', 'Team member updated.');
        $this->redirect('/admin/team');
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        TeamMember::softDelete((int) $id);
        AuditLogger::log('deleted', 'team_members', (int) $id);
        \App\Core\Session::flash('success', 'Team member removed.');
        $this->redirect('/admin/team');
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

    private function storePhoto(): ?int
    {
        $file = $this->file('photo');
        if (!$file) {
            return null;
        }
        $path = FileUploader::storePublic($file, 'team/');
        if (!$path) {
            return null;
        }
        return Media::create([
            'disk'            => 'local',
            'file_path'       => $path,
            'file_name'       => $file['name'],
            'mime_type'       => mime_content_type($path),
            'file_size_bytes' => filesize($path),
            'uploaded_by'     => \App\Core\Auth::user()['id'],
        ]);
    }
}
