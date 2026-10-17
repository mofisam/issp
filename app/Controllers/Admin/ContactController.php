<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Validator;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\ContactSubmission;
use App\Services\AuditLogger;
use App\Services\Mailer;

class ContactController extends Controller
{
    private const STATUSES = ['new', 'in_progress', 'resolved', 'spam'];
    private const PER_PAGE = 20;
    private const AUDIENCE_TYPES = ['professional', 'partner_donor', 'media', 'msme', 'regulator', 'general'];

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');

        $search   = trim((string) $this->input('search', ''));
        $status   = trim((string) $this->input('status', ''));
        $audience = trim((string) $this->input('audience', ''));
        $page     = max(1, (int) $this->input('page', 1));

        $filters  = ['status' => $status ?: null, 'audience_type' => $audience ?: null];
        $searchColumns = ['first_name', 'last_name', 'email', 'subject'];
        $total    = ContactSubmission::count($filters, $search ?: null, $searchColumns);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $this->render('admin.contact.index', [
            'items'          => ContactSubmission::paginate($page, self::PER_PAGE, $filters, $search ?: null, $searchColumns, 'created_at DESC'),
            'search'         => $search,
            'status'         => $status,
            'audience'       => $audience,
            'statuses'       => self::STATUSES,
            'audienceTypes'  => self::AUDIENCE_TYPES,
            'page'           => $page,
            'lastPage'       => $lastPage,
            'total'          => $total,
        ], 'layouts.admin');
    }

    public function show(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $submission = ContactSubmission::find((int) $id);
        if (!$submission) {
            $this->abort(404);
        }
        $this->render('admin.contact.show', ['submission' => $submission, 'statuses' => self::STATUSES], 'layouts.admin');
    }

    public function updateStatus(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $submission = ContactSubmission::find((int) $id);
        if (!$submission) {
            $this->abort(404);
        }

        $status = (string) $this->input('status', 'new');
        if (!in_array($status, self::STATUSES, true)) {
            $status = 'new';
        }

        ContactSubmission::update((int) $id, [
            'status'     => $status,
            'handled_by' => \App\Core\Auth::user()['id'],
        ]);

        AuditLogger::log('updated', 'contact_submissions', (int) $id);
        \App\Core\Session::flash('success', 'Enquiry status updated.');
        $this->redirect('/admin/contact/' . (int) $id);
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');
        CsrfMiddleware::handle();

        ContactSubmission::softDelete((int) $id);
        AuditLogger::log('deleted', 'contact_submissions', (int) $id);
        \App\Core\Session::flash('success', 'Enquiry deleted.');
        $this->redirect('/admin/contact');
    }

    /** Reply to the enquiry's sender by email, over SMTP. */
    public function reply(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $submission = ContactSubmission::find((int) $id);
        if (!$submission) {
            $this->abort(404);
        }

        $validator = (new Validator($_POST))
            ->required('subject', 'Subject')
            ->required('message', 'Message');

        if (!$validator->passes()) {
            \App\Core\Session::flash('error', 'Subject and message are both required.');
            $this->redirect('/admin/contact/' . (int) $id);
        }

        $subject  = (string) $this->input('subject');
        $bodyHtml = '<div style="font-family: Arial, sans-serif; font-size: 15px; color:#1f2937; line-height:1.6;">'
            . nl2br(e((string) $this->input('message')))
            . '</div>';

        $sent = Mailer::send($submission['email'], $submission['first_name'], $subject, $bodyHtml);

        AuditLogger::log('emailed', 'contact_submissions', (int) $id, ['subject' => $subject]);

        if ($sent) {
            // A reply is a natural signal that a fresh enquiry is being worked.
            if ($submission['status'] === 'new') {
                ContactSubmission::update((int) $id, [
                    'status'     => 'in_progress',
                    'handled_by' => \App\Core\Auth::user()['id'],
                ]);
            }
            \App\Core\Session::flash('success', 'Reply sent to ' . $submission['email'] . '.');
        } else {
            \App\Core\Session::flash('error', 'Could not send the email. Check the SMTP settings in .env (MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD).');
        }

        $this->redirect('/admin/contact/' . (int) $id);
    }
}
