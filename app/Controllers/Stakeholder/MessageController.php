<?php
declare(strict_types=1);

namespace App\Controllers\Stakeholder;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Middleware\CsrfMiddleware;
use App\Middleware\StakeholderMiddleware;
use App\Models\Message;

/** Portal-side in-app inbox - a stakeholder user's own received messages. */
class MessageController extends Controller
{
    private const PER_PAGE = 20;

    public function index(): void
    {
        StakeholderMiddleware::handle();

        $search = trim((string) $this->input('search', ''));
        $page   = max(1, (int) $this->input('page', 1));
        $userId = (int) Auth::user()['id'];

        $total    = Message::inboxCountFor($userId, $search ?: null);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $this->render('stakeholder.messages.index', [
            'items'    => Message::inboxFor($userId, $page, self::PER_PAGE, $search ?: null),
            'search'   => $search,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
            'title'    => 'Messages | ISSP Stakeholder Portal',
        ], 'layouts.stakeholder');
    }

    public function show(string $id): void
    {
        StakeholderMiddleware::handle();

        $userId  = (int) Auth::user()['id'];
        $message = Message::findForRecipient((int) $id, $userId);
        if (!$message) {
            $this->abort(404);
        }

        Message::markRead((int) $id, $userId);

        $this->render('stakeholder.messages.show', ['message' => $message, 'title' => $message['subject'] . ' | ISSP Stakeholder Portal'], 'layouts.stakeholder');
    }

    public function destroy(string $id): void
    {
        StakeholderMiddleware::handle();
        CsrfMiddleware::handle();

        $userId  = (int) Auth::user()['id'];
        $message = Message::findForRecipient((int) $id, $userId);
        if (!$message) {
            $this->abort(404);
        }

        Message::softDelete((int) $id);
        Session::flash('success', 'Message deleted.');
        $this->redirect('/portal/messages');
    }
}
