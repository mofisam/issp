<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\AuditLogger;

/**
 * Super-admin recycle bin. Everything staff "delete" elsewhere is a soft
 * delete (deleted_at set); it lands here, where it can be restored or
 * removed for good. Permanent removal is a real DELETE and cannot be undone.
 */
class RecycleBinController extends Controller
{
    private const PER_PAGE = 20;

    /** type key => [table, label, SQL expression for the display title, SQL expression for a detail line] */
    private const TYPES = [
        'posts'     => ['posts', 'News', 'title', "status"],
        'pages'     => ['pages', 'Pages', 'title', "status"],
        'events'    => ['events', 'Events', 'title', "NULL"],
        'resources' => ['resources', 'Documents', 'title', "visibility"],
        'team'      => ['team_members', 'Team', 'full_name', 'email'],
        'partners'  => ['partners', 'Partners', 'name', "NULL"],
        'pillars'   => ['programme_pillars', 'Pillars', 'title', "NULL"],
        'orgs'      => ['organizations', 'Organizations', 'name', 'organization_type'],
        'users'     => ['users', 'Users', "CONCAT(first_name, ' ', last_name)", 'email'],
        'subscribers' => ['newsletter_subscribers', 'Subscribers', 'email', 'status'],
        'contact'   => ['contact_submissions', 'Enquiries', "CONCAT(first_name, ' ', last_name)", 'subject'],
    ];

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin');

        $counts = $this->counts();
        $type   = (string) $this->input('type', '');
        if (!isset(self::TYPES[$type])) {
            $type = '';
        }

        $items = [];
        $total = 0;
        $page  = 1;
        $lastPage = 1;
        if ($type !== '') {
            [$table, , $titleExpr, $detailExpr] = self::TYPES[$type];
            $total    = $counts[$type];
            $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
            $page     = min(max(1, (int) $this->input('page', 1)), $lastPage);
            $offset   = ($page - 1) * self::PER_PAGE;
            $limit    = self::PER_PAGE;
            $db  = Database::connection();
            $sql = sprintf(
                'SELECT id, %s AS title, %s AS detail, deleted_at FROM `%s` WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC LIMIT %d OFFSET %d',
                $titleExpr, $detailExpr, $table, $limit, $offset
            );
            $res   = $db->query($sql);
            $items = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }

        $this->render('admin.recycle-bin.index', [
            'types'    => array_map(fn ($t) => $t[1], self::TYPES),
            'counts'   => $counts,
            'grand'    => array_sum($counts),
            'type'     => $type,
            'items'    => $items,
            'total'    => $total,
            'page'     => $page,
            'lastPage' => $lastPage,
        ], 'layouts.admin');
    }

    public function restore(string $type, string $id): void
    {
        AdminMiddleware::requireRole('super_admin');
        CsrfMiddleware::handle();
        $table = $this->table($type);

        $db   = Database::connection();
        $stmt = $db->prepare("UPDATE `$table` SET deleted_at = NULL WHERE id = ? AND deleted_at IS NOT NULL");
        $intId = (int) $id;
        $stmt->bind_param('i', $intId);
        $stmt->execute();
        $stmt->close();

        AuditLogger::log('restored', $table, $intId);
        Session::flash('success', 'Item restored.');
        $this->redirect('/admin/recycle-bin?type=' . $type);
    }

    public function destroy(string $type, string $id): void
    {
        AdminMiddleware::requireRole('super_admin');
        CsrfMiddleware::handle();
        $table = $this->table($type);

        $result = $this->purge($table, [(int) $id]);
        if ($result['failed']) {
            Session::flash('error', 'This item is still referenced by other records (for example an activity log or related content), so it cannot be permanently removed.');
        } else {
            AuditLogger::log('purged', $table, (int) $id);
            Session::flash('success', 'Item permanently deleted.');
        }
        $this->redirect('/admin/recycle-bin?type=' . $type);
    }

    /** Permanently removes everything in one category's bin (or all categories when type is empty). */
    public function emptyBin(): void
    {
        AdminMiddleware::requireRole('super_admin');
        CsrfMiddleware::handle();

        $type  = (string) $this->input('type', '');
        $keys  = isset(self::TYPES[$type]) ? [$type] : array_keys(self::TYPES);
        $done  = 0;
        $failed = 0;
        $db = Database::connection();

        foreach ($keys as $key) {
            $table = self::TYPES[$key][0];
            $res   = $db->query("SELECT id FROM `$table` WHERE deleted_at IS NOT NULL");
            $ids   = $res ? array_map('intval', array_column($res->fetch_all(MYSQLI_ASSOC), 'id')) : [];
            $r = $this->purge($table, $ids);
            $done   += $r['deleted'];
            $failed += $r['failed'];
            if ($r['deleted']) {
                AuditLogger::log('purged', $table, 0, ['count' => $r['deleted']]);
            }
        }

        if ($failed) {
            Session::flash('error', "Permanently deleted $done item(s). $failed could not be removed because other records still reference them.");
        } else {
            Session::flash('success', $done ? "Permanently deleted $done item(s)." : 'The recycle bin was already empty.');
        }
        $this->redirect('/admin/recycle-bin' . ($type !== '' && isset(self::TYPES[$type]) ? '?type=' . $type : ''));
    }

    private function table(string $type): string
    {
        if (!isset(self::TYPES[$type])) {
            $this->abort(404);
        }
        return self::TYPES[$type][0];
    }

    /** @return array{deleted:int, failed:int} */
    private function purge(string $table, array $ids): array
    {
        $db = Database::connection();
        $deleted = $failed = 0;
        foreach ($ids as $id) {
            try {
                // Only ever removes rows that are already in the bin.
                $stmt = $db->prepare("DELETE FROM `$table` WHERE id = ? AND deleted_at IS NOT NULL");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();
                $affected > 0 ? $deleted++ : $failed++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }
        return ['deleted' => $deleted, 'failed' => $failed];
    }

    /** @return array<string,int> */
    private function counts(): array
    {
        $db = Database::connection();
        $out = [];
        foreach (self::TYPES as $key => [$table]) {
            $res = $db->query("SELECT COUNT(*) c FROM `$table` WHERE deleted_at IS NOT NULL");
            $out[$key] = $res ? (int) $res->fetch_assoc()['c'] : 0;
        }
        return $out;
    }
}
