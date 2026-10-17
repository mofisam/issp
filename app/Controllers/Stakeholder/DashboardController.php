<?php
declare(strict_types=1);

namespace App\Controllers\Stakeholder;

use App\Core\Auth;
use App\Core\Controller;
use App\Middleware\StakeholderMiddleware;
use App\Models\Message;
use App\Models\Organization;
use App\Models\Resource;
use App\Models\ResourceDownloadLog;
use App\Services\DocumentPresenter;

class DashboardController extends Controller
{
    public function index(): void
    {
        StakeholderMiddleware::handle();

        $user      = Auth::user();
        $userId    = (int) $user['id'];
        $documents = Resource::visibleTo($user);

        $organization = !empty($user['organization_id']) ? Organization::find((int) $user['organization_id']) : null;

        $this->render('stakeholder.dashboard', [
            'user'            => $user,
            'organization'    => $organization,
            'documentCount'   => count($documents),
            'recentDocuments' => DocumentPresenter::enrich(array_slice($documents, 0, 5)),
            'unreadCount'     => Message::unreadCountFor($userId),
            'recentMessages'  => Message::inboxFor($userId, 1, 4),
            'downloadCount'   => ResourceDownloadLog::countForUser($userId),
            'title'           => 'Dashboard | ISSP Stakeholder Portal',
        ], 'layouts.stakeholder');
    }
}
