<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Middleware\AdminMiddleware;
use App\Models\AuditLog;
use App\Models\ContactSubmission;
use App\Models\Event;
use App\Models\NewsletterSubscriber;
use App\Models\Organization;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Resource;
use App\Models\TeamMember;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(): void
    {
        AdminMiddleware::handle();

        $isAdminTier = Auth::hasRole('super_admin', 'admin');
        $isSuperAdmin = Auth::hasRole('super_admin');

        // Content stats: every staff role (editor and up) works with these daily.
        $content = [
            'pages'     => count(Page::all()),
            'posts'     => count(Post::all()),
            'events'    => count(Event::all()),
            'resources' => count(Resource::all()),
            'team'      => count(TeamMember::all()),
            'partners'  => count(Partner::all()),
        ];

        // Items waiting on someone - the same figure an editor would want
        // to triage first thing.
        $needsAttention = [
            'draftPosts'       => count(Post::whereAll('status', 'draft')),
            'pendingPosts'     => count(Post::whereAll('status', 'pending_review')),
            'pendingPages'     => count(Page::whereAll('status', 'pending_review')),
            'newContactMessages' => count(ContactSubmission::whereAll('status', 'new')),
        ];

        // Capped at 5 everywhere they're listed, so the dashboard stays a
        // snapshot rather than a second inbox.
        $allUpcomingEvents = Event::upcoming();
        $upcomingEvents    = array_slice($allUpcomingEvents, 0, 5);

        // Data insight: where content sits in the pipeline, and how the
        // event calendar splits - useful to every staff role, not just
        // admins, since it's what an editor triages against daily.
        $postStatusCounts = [
            'draft'          => count(Post::whereAll('status', 'draft')),
            'pending_review' => $needsAttention['pendingPosts'],
            'published'      => count(Post::whereAll('status', 'published')),
            'archived'       => count(Post::whereAll('status', 'archived')),
        ];
        $eventMix = [
            'upcoming' => count($allUpcomingEvents),
            'past'     => max(0, $content['events'] - count($allUpcomingEvents)),
        ];

        $data = [
            'isAdminTier'       => $isAdminTier,
            'isSuperAdmin'      => $isSuperAdmin,
            'content'           => $content,
            'needsAttention'    => $needsAttention,
            'upcomingEvents'    => $upcomingEvents,
            'postStatusCounts'  => $postStatusCounts,
            'eventMix'          => $eventMix,
        ];

        // Admin/super_admin only: site-wide figures and the activity feed -
        // an editor doesn't manage subscribers, enquiries or other accounts,
        // so showing these to them would just be noise (and in the users'
        // case, data they can't act on anyway).
        if ($isAdminTier) {
            $data['engagement'] = [
                'newsletterSubscribers' => count(NewsletterSubscriber::all()),
                'organizations'         => count(Organization::all()),
            ];
            $data['recentActivity'] = AuditLog::recent(5);
            $data['activityTrend']  = AuditLog::countsByDay(7);
        }

        if ($isSuperAdmin) {
            $data['userCount'] = count(User::all());
        }

        $this->render('admin.dashboard', $data, 'layouts.admin');
    }
}
