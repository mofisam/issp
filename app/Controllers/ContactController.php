<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Middleware\CsrfMiddleware;
use App\Models\ContactSubmission;
use App\Services\Mailer;

class ContactController extends Controller
{
    public function show(): void
    {
        $this->render('pages.contact');
    }

    public function store(): void
    {
        CsrfMiddleware::handle();

        $validator = new Validator($_POST);
        $validator->required('first_name', 'First name')
            ->required('last_name', 'Last name')
            ->required('email', 'Email')
            ->email('email', 'Email')
            ->required('message', 'Message');

        if (!$validator->passes()) {
            $_SESSION['_old'] = $_POST;
            $_SESSION['_errors'] = $validator->errors();
            $this->redirect('/contact');
        }

        ContactSubmission::submit($_POST);

        Mailer::send(
            (string) $this->input('email'),
            (string) $this->input('first_name'),
            'We received your message - ISSP',
            '<p>Thank you for contacting ISSP. Our team will respond shortly.</p>'
        );

        \App\Core\Session::flash('success', 'Thank you - your message has been sent.');
        $this->redirect('/contact');
    }
}
