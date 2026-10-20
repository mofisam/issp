<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\CsrfMiddleware;
use App\Models\Event;
use App\Models\EventRegistration;

class EventController extends Controller
{
    private const PAST_PER_PAGE = 12;

    public function index(): void
    {
        $when = $this->input('when', 'upcoming') === 'past' ? 'past' : 'upcoming';

        $upcoming     = Event::upcoming();
        $upcomingCount = count($upcoming);
        $pastCount    = Event::pastCount();

        // Month chips come from the events that exist, so there are never empty months.
        $months = [];
        foreach ($upcoming as $ev) {
            $key = date('Y-m', strtotime($ev['starts_at']));
            $months[$key] = date('M Y', strtotime($ev['starts_at']));
        }

        $month = (string) $this->input('month', '');
        if (!isset($months[$month])) {
            $month = '';
        }

        $page = 1;
        $lastPage = 1;
        if ($when === 'past') {
            $lastPage = max(1, (int) ceil($pastCount / self::PAST_PER_PAGE));
            $page     = min(max(1, (int) $this->input('page', 1)), $lastPage);
            $events   = Event::pastPaginated($page, self::PAST_PER_PAGE);
        } else {
            $events = $month === ''
                ? $upcoming
                : array_values(array_filter($upcoming, fn ($e) => date('Y-m', strtotime($e['starts_at'])) === $month));
        }

        foreach ($events as &$ev) {
            $ev['image_url'] = media_url($ev['featured_media_id'] ?? null);
        }
        unset($ev);

        $this->render('events.calendar', [
            'events'        => $events,
            'when'          => $when,
            'month'         => $month,
            'months'        => $months,
            'upcomingCount' => $upcomingCount,
            'pastCount'     => $pastCount,
            'page'          => $page,
            'lastPage'      => $lastPage,
        ]);
    }

    public function show(string $slug): void
    {
        $event = Event::findPublishedBySlug($slug);
        if (!$event) {
            $this->abort(404);
        }
        $event['image_url'] = media_url($event['featured_media_id'] ?? null);

        $taken     = EventRegistration::countFor((int) $event['id']);
        $capacity  = $event['capacity'] !== null ? (int) $event['capacity'] : null;
        $isPast    = strtotime($event['ends_at'] ?: $event['starts_at']) < time();
        $isFull    = $capacity !== null && $taken >= $capacity;

        $more = Event::nextUp(3, (int) $event['id']);
        foreach ($more as &$m) {
            $m['image_url'] = media_url($m['featured_media_id'] ?? null);
        }
        unset($m);

        $this->render('events.single', [
            'event'     => $event,
            'taken'     => $taken,
            'capacity'  => $capacity,
            'spotsLeft' => $capacity !== null ? max(0, $capacity - $taken) : null,
            'isPast'    => $isPast,
            'isFull'    => $isFull,
            'isOpen'    => $event['status'] === 'published' && !$isPast,
            'more'      => $more,
        ]);
    }

    /** .ics download so the event can be added to any calendar app. */
    public function calendar(string $slug): void
    {
        $event = Event::findPublishedBySlug($slug);
        if (!$event || $event['status'] === 'cancelled') {
            $this->abort(404);
        }
        $tz  = new \DateTimeZone($event['timezone'] ?: 'Africa/Lagos');
        $utc = new \DateTimeZone('UTC');
        $fmt = static fn (string $d): string => (new \DateTime($d, $tz))->setTimezone($utc)->format('Ymd\THis\Z');
        $esc = static fn (?string $t): string => str_replace(["\\", ";", ",", "\r\n", "\n"], ["\\\\", "\;", "\\,", "\\n", "\\n"], (string) $t);

        $where = trim(($event['location_name'] ?? '') . ' ' . ($event['location_address'] ?? ''));
        if (!empty($event['is_virtual']) && !empty($event['virtual_meeting_url'])) {
            $where = $where !== '' ? $where . ' / ' . $event['virtual_meeting_url'] : $event['virtual_meeting_url'];
        }

        $ics = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//ISSP//Events//EN\r\nBEGIN:VEVENT\r\n"
            . 'UID:event-' . (int) $event['id'] . "@issp\r\n"
            . 'DTSTAMP:' . gmdate('Ymd\THis\Z') . "\r\n"
            . 'DTSTART:' . $fmt($event['starts_at']) . "\r\n"
            . 'DTEND:' . $fmt($event['ends_at'] ?: $event['starts_at']) . "\r\n"
            . 'SUMMARY:' . $esc($event['title']) . "\r\n"
            . 'DESCRIPTION:' . $esc($event['description']) . "\r\n"
            . 'LOCATION:' . $esc($where) . "\r\n"
            . "END:VEVENT\r\nEND:VCALENDAR\r\n";

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-z0-9\-]+/i', '-', $event['slug']) . '.ics"');
        echo $ics;
        exit;
    }

    public function register(string $id): void
    {
        CsrfMiddleware::handle();

        $event = Event::find((int) $id);
        if (!$event || $event['status'] !== 'published' || empty($event['registration_required'])) {
            $this->abort(404);
        }
        $back = '/events/' . $event['slug'];

        $name  = trim((string) $this->input('full_name'));
        $email = trim((string) $this->input('email'));
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['_old'] = $_POST;
            Session::flash('error', 'Please enter your name and a valid email address.');
            $this->redirect($back . '#register');
        }
        if (strtotime($event['ends_at'] ?: $event['starts_at']) < time()) {
            Session::flash('error', 'Registration for this event has closed.');
            $this->redirect($back);
        }
        if ($event['capacity'] !== null && EventRegistration::countFor((int) $event['id']) >= (int) $event['capacity']) {
            Session::flash('error', 'Sorry, this event is now full.');
            $this->redirect($back);
        }
        if (EventRegistration::exists((int) $event['id'], $email)) {
            Session::flash('success', 'You are already registered for this event with that email.');
            $this->redirect($back);
        }

        EventRegistration::register(
            (int) $event['id'],
            $name,
            $email,
            trim((string) $this->input('phone')) ?: null,
            trim((string) $this->input('organization_name')) ?: null
        );

        Session::flash('success', 'You are registered. We look forward to seeing you!');
        $this->redirect($back);
    }
}
