<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Resource;
use App\Models\ResourceDownloadLog;

class ResourceController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        $resources = $user ? Resource::visibleTo($user) : Resource::publicOnly();
        $this->render('pages.resources', ['resources' => $resources]);
    }

    /** Public download endpoint - only ever serves visibility=public files. Gated files go through Stakeholder\DocumentController. */
    public function download(string $id): void
    {
        $resource = Resource::find((int) $id);
        if (!$resource || !Resource::userCanAccess($resource, Auth::user())) {
            $this->abort(403);
        }

        ResourceDownloadLog::record($resource['id'], Auth::user()['id'] ?? null);
        Resource::incrementDownloadCount($resource['id']);

        // In a full build, resolve $resource['file_media_id'] via the Media model
        // to get the real file path, then stream it with readfile() and the
        // correct Content-Type/Content-Disposition headers.
        $this->redirect('/resources');
    }
}
