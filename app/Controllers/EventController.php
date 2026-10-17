<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Middleware\CsrfMiddleware;
use App\Models\Event;
use App\Models\EventRegistration;

class EventController extends Controller
{
    public function index(): void
    {
        $this->render('events.calendar', ['events' => Event::upcoming()]);
    }

    public function show(string $slug): void
    {
        $event = Event::findPublishedBySlug($slug);
        if (!$event) {
            $this->abort(404);
        }
        $this->render('events.single', ['event' => $event]);
    }

    public function register(string $id): void
    {
        CsrfMiddleware::handle();

        EventRegistration::register(
            (int) $id,
            (string) $this->input('full_name'),
            (string) $this->input('email'),
            $this->input('phone'),
            $this->input('organization_name')
        );

        \App\Core\Session::flash('success', 'You are registered. We will send a confirmation email shortly.');
        $this->redirect('/events');
    }
}
