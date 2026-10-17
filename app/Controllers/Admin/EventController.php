<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Event;
use App\Models\EventCategory;
use App\Services\AuditLogger;
use App\Services\SlugGenerator;

class EventController extends Controller
{
    private const PER_PAGE = 15;
    private const STATUSES = ['draft', 'published', 'cancelled', 'completed'];

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');

        $search     = trim((string) $this->input('search', ''));
        $status     = trim((string) $this->input('status', ''));
        $categoryId = trim((string) $this->input('category_id', ''));
        $page       = max(1, (int) $this->input('page', 1));

        $filters  = ['status' => $status ?: null, 'category_id' => $categoryId ?: null];
        $total    = Event::count($filters, $search ?: null, ['title']);
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page     = min($page, $lastPage);

        $this->render('admin.events.index', [
            'items'      => Event::paginate($page, self::PER_PAGE, $filters, $search ?: null, ['title'], 'starts_at DESC'),
            'categories' => EventCategory::all('name ASC'),
            'search'     => $search,
            'status'     => $status,
            'categoryId' => $categoryId,
            'statuses'   => self::STATUSES,
            'page'       => $page,
            'lastPage'   => $lastPage,
            'total'      => $total,
        ], 'layouts.admin');
    }

    public function create(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $this->render('admin.events.create', ['categories' => EventCategory::all('name ASC')], 'layouts.admin');
    }

    public function store(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $validator = (new Validator($_POST))
            ->required('title', 'Title')
            ->required('starts_at', 'Start date/time')
            ->required('ends_at', 'End date/time');

        if (!$validator->passes()) {
            $_SESSION['_old'] = $_POST;
            $_SESSION['_errors'] = $validator->errors();
            $this->redirect('/admin/events/create');
        }

        $slug = SlugGenerator::unique((string) $this->input('title'), fn ($s) => (bool) Event::findBy('slug', $s));

        $id = Event::create([
            'category_id'            => $this->input('category_id') ?: null,
            'title'                  => $this->input('title'),
            'slug'                   => $slug,
            'description'            => $this->input('description'),
            'location_name'          => $this->input('location_name'),
            'location_address'       => $this->input('location_address'),
            'is_virtual'             => $this->input('is_virtual') ? 1 : 0,
            'virtual_meeting_url'    => $this->input('virtual_meeting_url'),
            'starts_at'              => $this->input('starts_at'),
            'ends_at'                => $this->input('ends_at'),
            'registration_required'  => $this->input('registration_required') ? 1 : 0,
            'capacity'               => $this->input('capacity') ?: null,
            'status'                 => $this->input('status', 'draft'),
            'created_by'             => Auth::user()['id'],
        ]);

        AuditLogger::log('created', 'events', $id);
        \App\Core\Session::flash('success', 'Event created.');
        $this->redirect('/admin/events');
    }

    public function edit(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $event = Event::find((int) $id);
        if (!$event) {
            $this->abort(404);
        }
        $this->render('admin.events.edit', [
            'event'      => $event,
            'categories' => EventCategory::all('name ASC'),
        ], 'layouts.admin');
    }

    /**
     * Lets a logged-in staff member view an event the way the public will
     * see it, regardless of status - so a draft can be checked before
     * publishing. Renders the same template the live /events/{slug} route uses.
     */
    public function preview(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $event = Event::find((int) $id);
        if (!$event) {
            $this->abort(404);
        }
        $this->render('events.single', ['event' => $event, 'title' => $event['title'] . ' (preview)'], 'layouts.main');
    }

    public function update(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        Event::update((int) $id, [
            'category_id'            => $this->input('category_id') ?: null,
            'title'                  => $this->input('title'),
            'description'            => $this->input('description'),
            'location_name'          => $this->input('location_name'),
            'location_address'       => $this->input('location_address'),
            'is_virtual'             => $this->input('is_virtual') ? 1 : 0,
            'virtual_meeting_url'    => $this->input('virtual_meeting_url'),
            'starts_at'              => $this->input('starts_at'),
            'ends_at'                => $this->input('ends_at'),
            'registration_required'  => $this->input('registration_required') ? 1 : 0,
            'capacity'               => $this->input('capacity') ?: null,
            'status'                 => $this->input('status', 'draft'),
        ]);

        AuditLogger::log('updated', 'events', (int) $id);
        \App\Core\Session::flash('success', 'Event updated.');
        $this->redirect('/admin/events');
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        Event::softDelete((int) $id);
        AuditLogger::log('deleted', 'events', (int) $id);
        \App\Core\Session::flash('success', 'Event deleted.');
        $this->redirect('/admin/events');
    }
}
