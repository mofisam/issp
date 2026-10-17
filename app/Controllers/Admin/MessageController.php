<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Message;
use App\Models\User;
use App\Services\AuditLogger;

/**
 * Staff-side in-app inbox. Any staff member (super_admin/admin/editor) can
 * read their own inbox and reply; only super_admin/admin can compose a new
 * message to any user (staff or stakeholder), mirroring the role split
 * already used for the newsletter and contact-reply features.
 */
class MessageController extends Controller
{
    private const PER_PAGE = 20;

    public function index(): void
    {
        AdminMiddleware::handle();

        $search = trim((string) $this->input('search', ''));
        $page   = max(1, (int) $this->input('page', 1));
        $userId = (int) Auth::user()['id'];

        $total    = Message::inboxCountFor($userId, $search ?: null);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $this->render('admin.messages.index', [
            'items'    => Message::inboxFor($userId, $page, self::PER_PAGE, $search ?: null),
            'search'   => $search,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
        ], 'layouts.admin');
    }

    public function sent(): void
    {
        AdminMiddleware::handle();

        $search = trim((string) $this->input('search', ''));
        $page   = max(1, (int) $this->input('page', 1));
        $userId = (int) Auth::user()['id'];

        $total    = Message::sentCountBy($userId, $search ?: null);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $this->render('admin.messages.sent', [
            'items'    => Message::sentBy($userId, $page, self::PER_PAGE, $search ?: null),
            'search'   => $search,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
        ], 'layouts.admin');
    }

    public function show(string $id): void
    {
        AdminMiddleware::handle();

        $userId  = (int) Auth::user()['id'];
        $message = Message::findForRecipient((int) $id, $userId);
        if (!$message) {
            $this->abort(404);
        }

        Message::markRead((int) $id, $userId);

        $this->render('admin.messages.show', ['message' => $message], 'layouts.admin');
    }

    public function compose(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');

        $this->render('admin.messages.compose', [
            'recipients' => User::whereAll('status', 'active', 'first_name ASC'),
        ], 'layouts.admin');
    }

    public function store(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');
        CsrfMiddleware::handle();

        $validator = (new Validator($_POST))
            ->required('subject', 'Subject')
            ->required('body', 'Message');

        $recipientIds = array_filter(array_map('intval', (array) ($_POST['recipient_id'] ?? [])));

        if (!$validator->passes() || empty($recipientIds)) {
            $_SESSION['_old'] = $_POST;
            $_SESSION['_errors'] = $validator->errors();
            if (empty($recipientIds)) {
                Session::flash('error', 'Choose at least one recipient.');
            }
            $this->redirect('/admin/messages/compose');
        }

        $senderId = (int) Auth::user()['id'];
        $subject  = (string) $this->input('subject');
        $ids = Message::sendToMany($senderId, $recipientIds, $subject, (string) $this->input('body'));

        AuditLogger::log('created', 'messages', $ids[0] ?? 0, ['subject' => $subject, 'recipients' => count($recipientIds)]);

        $count = count($recipientIds);
        Session::flash('success', 'Message sent to ' . $count . ' recipient' . ($count === 1 ? '' : 's') . '.');
        $this->redirect('/admin/messages');
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::handle();
        CsrfMiddleware::handle();

        $userId  = (int) Auth::user()['id'];
        $message = Message::findForRecipient((int) $id, $userId);
        if (!$message) {
            $this->abort(404);
        }

        Message::softDelete((int) $id);
        Session::flash('success', 'Message deleted.');
        $this->redirect('/admin/messages');
    }
}
