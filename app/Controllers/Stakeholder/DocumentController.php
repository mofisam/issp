<?php
declare(strict_types=1);

namespace App\Controllers\Stakeholder;

use App\Core\Auth;
use App\Core\Controller;
use App\Middleware\StakeholderMiddleware;
use App\Models\Media;
use App\Models\Resource;
use App\Models\ResourceDownloadLog;
use App\Services\DocumentPresenter;

/**
 * This is the controller that actually enforces the "stakeholder/member
 * login area" requirement from the quotation: insurers, regulators, and
 * partners can only reach the documents they are entitled to, every
 * successful download is logged, and the files themselves are stored
 * outside the public web root so there is no way to guess a URL and
 * bypass the login.
 */
class DocumentController extends Controller
{
    private const PER_PAGE = 10;

    public function index(): void
    {
        StakeholderMiddleware::handle();

        $user       = Auth::user();
        $search     = trim((string) $this->input('search', ''));
        $categoryId = (int) $this->input('category_id', 0);
        $year       = (int) $this->input('year', 0);
        $page       = max(1, (int) $this->input('page', 1));

        // Everything the user may see, enriched once so the filter dropdowns
        // only offer categories/years that actually exist for them.
        $all = DocumentPresenter::enrich(Resource::visibleTo($user));

        $categories = [];
        $years      = [];
        foreach ($all as $doc) {
            if (!empty($doc['category_id']) && !empty($doc['category_name'])) {
                $categories[(int) $doc['category_id']] = $doc['category_name'];
            }
            if (!empty($doc['publication_year'])) {
                $years[(int) $doc['publication_year']] = (int) $doc['publication_year'];
            }
        }
        asort($categories);
        rsort($years);

        $filtered = array_values(array_filter($all, static function (array $doc) use ($search, $categoryId, $year): bool {
            if ($categoryId && (int) ($doc['category_id'] ?? 0) !== $categoryId) {
                return false;
            }
            if ($year && (int) ($doc['publication_year'] ?? 0) !== $year) {
                return false;
            }
            if ($search !== '') {
                $haystack = mb_strtolower(($doc['title'] ?? '') . ' ' . ($doc['description'] ?? ''));
                return str_contains($haystack, mb_strtolower($search));
            }
            return true;
        }));

        $total    = count($filtered);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $this->render('stakeholder.documents', [
            'documents'  => array_slice($filtered, ($page - 1) * self::PER_PAGE, self::PER_PAGE),
            'categories' => $categories,
            'years'      => $years,
            'search'     => $search,
            'categoryId' => $categoryId,
            'year'       => $year,
            'page'       => $page,
            'lastPage'   => $lastPage,
            'total'      => $total,
            'hasAny'     => !empty($all),
            'title'      => 'Documents | ISSP Stakeholder Portal',
        ], 'layouts.stakeholder');
    }

    public function download(string $id): void
    {
        StakeholderMiddleware::handle();

        $user     = Auth::user();
        $resource = Resource::find((int) $id);

        if (!$resource || !Resource::userCanAccess($resource, $user)) {
            // Deliberately 403, not 404 - do not reveal whether the resource exists.
            $this->abort(403);
        }

        $media = Media::find((int) $resource['file_media_id']);
        if (!$media || !is_file($media['file_path'])) {
            $this->abort(404);
        }

        ResourceDownloadLog::record($resource['id'], $user['id']);
        Resource::incrementDownloadCount($resource['id']);

        header('Content-Type: ' . $media['mime_type']);
        header('Content-Disposition: attachment; filename="' . basename($media['file_name']) . '"');
        header('Content-Length: ' . filesize($media['file_path']));
        readfile($media['file_path']);
        exit;
    }
}
