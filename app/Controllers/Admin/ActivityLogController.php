<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middleware\AdminMiddleware;
use App\Models\AuditLog;

/**
 * The full, filterable, paginated activity log. The dashboard's "Recent
 * activity" card is a teaser of the last few entries for admin+; this page
 * is the complete history and is restricted to super_admin, since it can
 * surface actions taken by every user on the system.
 */
class ActivityLogController extends Controller
{
    private const PER_PAGE = 25;

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin');

        $page       = max(1, (int) $this->input('page', 1));
        $action     = trim((string) $this->input('action', ''));
        $entityType = trim((string) $this->input('entity_type', ''));

        $total      = AuditLog::countAll($action ?: null, $entityType ?: null);
        $lastPage   = max(1, (int) ceil($total / self::PER_PAGE));
        $page       = min($page, $lastPage);

        $this->render('admin.activity.index', [
            'logs'        => AuditLog::paginated($page, self::PER_PAGE, $action ?: null, $entityType ?: null),
            'total'       => $total,
            'page'        => $page,
            'lastPage'    => $lastPage,
            'perPage'     => self::PER_PAGE,
            'action'      => $action,
            'entityType'  => $entityType,
            'actions'     => AuditLog::distinctActions(),
            'entityTypes' => AuditLog::distinctEntityTypes(),
        ], 'layouts.admin');
    }
}
