<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\NewsletterSubscriber;

class NewsletterController extends Controller
{
    public function subscribe(): void
    {
        $validator = (new Validator($_POST))->required('email', 'Email')->email('email', 'Email');

        if (!$validator->passes()) {
            $this->json(['success' => false, 'message' => 'Please enter a valid email address.'], 422);
        }

        $result = NewsletterSubscriber::subscribe(
            (string) $this->input('email'),
            $this->input('full_name'),
            'footer_form'
        );

        $this->json([
            'success' => true,
            'message' => $result['created']
                ? 'Thanks for subscribing - check your inbox to confirm.'
                : 'You are already subscribed.',
        ]);
    }
}
