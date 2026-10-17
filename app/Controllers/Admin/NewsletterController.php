<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\NewsletterSubscriber;
use App\Services\AuditLogger;
use App\Services\Mailer;

class NewsletterController extends Controller
{
    private const PER_PAGE = 20;
    private const STATUSES = ['subscribed', 'unsubscribed'];

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');

        $search = trim((string) $this->input('search', ''));
        $status = trim((string) $this->input('status', ''));
        $page   = max(1, (int) $this->input('page', 1));

        $filters  = ['status' => $status ?: null];
        $searchColumns = ['email', 'full_name'];
        $total    = NewsletterSubscriber::count($filters, $search ?: null, $searchColumns);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $this->render('admin.newsletter.index', [
            'items'    => NewsletterSubscriber::paginate($page, self::PER_PAGE, $filters, $search ?: null, $searchColumns, 'created_at DESC'),
            'search'   => $search,
            'status'   => $status,
            'statuses' => self::STATUSES,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
        ], 'layouts.admin');
    }

    /** Toggle a subscriber between subscribed/unsubscribed from the admin list. */
    public function toggleStatus(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $subscriber = NewsletterSubscriber::find((int) $id);
        if (!$subscriber) {
            $this->abort(404);
        }

        $newStatus = $subscriber['status'] === 'unsubscribed' ? 'subscribed' : 'unsubscribed';
        $data = ['status' => $newStatus];
        if ($newStatus === 'unsubscribed') {
            $data['unsubscribed_at'] = date('Y-m-d H:i:s');
        } else {
            $data['confirmed_at'] = date('Y-m-d H:i:s');
        }
        NewsletterSubscriber::update((int) $id, $data);

        AuditLogger::log('updated', 'newsletter_subscribers', (int) $id);
        Session::flash('success', 'Subscriber status updated.');
        $this->redirect('/admin/newsletter');
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        NewsletterSubscriber::softDelete((int) $id);
        AuditLogger::log('deleted', 'newsletter_subscribers', (int) $id);
        Session::flash('success', 'Subscriber removed.');
        $this->redirect('/admin/newsletter');
    }

    /** Compose screen for a broadcast email to every subscribed subscriber. */
    public function compose(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');

        $recipientCount = count(NewsletterSubscriber::whereAll('status', 'subscribed'));
        $this->render('admin.newsletter.compose', ['recipientCount' => $recipientCount], 'layouts.admin');
    }

    /** Sends the composed email to every currently-subscribed subscriber over SMTP. */
    public function send(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');
        CsrfMiddleware::handle();

        $validator = (new Validator($_POST))
            ->required('subject', 'Subject')
            ->required('message', 'Message');

        if (!$validator->passes()) {
            $_SESSION['_old'] = $_POST;
            Session::flash('error', 'Subject and message are both required.');
            $this->redirect('/admin/newsletter/compose');
        }

        $subscribers = NewsletterSubscriber::whereAll('status', 'subscribed');
        if (empty($subscribers)) {
            Session::flash('error', 'There are no subscribed recipients to email.');
            $this->redirect('/admin/newsletter/compose');
        }

        $recipients = array_map(
            static fn (array $s) => ['email' => $s['email'], 'name' => $s['full_name'] ?? ''],
            $subscribers
        );

        $subject = (string) $this->input('subject');
        $bodyHtml = '<div style="font-family: Arial, sans-serif; font-size: 15px; color:#1f2937; line-height:1.6;">'
            . nl2br(e((string) $this->input('message')))
            . '</div>';

        set_time_limit(0); // a few hundred subscribers over one SMTP connection can take a while
        $result = Mailer::sendBulk($recipients, $subject, $bodyHtml);

        AuditLogger::log('emailed', 'newsletter_subscribers', 0, [
            'subject' => $subject,
            'sent'    => $result['sent'],
            'failed'  => count($result['failed']),
        ]);

        if ($result['sent'] > 0) {
            $message = "Email sent to {$result['sent']} subscriber" . ($result['sent'] === 1 ? '' : 's') . '.';
            if (!empty($result['failed'])) {
                $message .= ' ' . count($result['failed']) . ' could not be delivered.';
            }
            Session::flash('success', $message);
        } else {
            Session::flash('error', 'Could not send to any subscribers. Check the SMTP settings in .env (MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD).');
        }

        $this->redirect('/admin/newsletter');
    }
}
