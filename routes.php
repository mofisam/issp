<?php
declare(strict_types=1);

/**
 * All routes in one place. \$router is created in public/index.php.
 * {param} segments are passed to the controller method in order.
 */

use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\PostController;
use App\Controllers\ResourceController;
use App\Controllers\EventController;
use App\Controllers\TeamController;
use App\Controllers\PartnerController;
use App\Controllers\ContactController;
use App\Controllers\NewsletterController;
use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\PasswordResetController;
use App\Controllers\Stakeholder\DashboardController as StakeholderDashboardController;
use App\Controllers\Stakeholder\DocumentController;
use App\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Controllers\Admin\PageController as AdminPageController;
use App\Controllers\Admin\PostController as AdminPostController;
use App\Controllers\Admin\ResourceController as AdminResourceController;
use App\Controllers\Admin\EventController as AdminEventController;
use App\Controllers\Admin\TeamController as AdminTeamController;
use App\Controllers\Admin\PartnerController as AdminPartnerController;
use App\Controllers\Admin\UserController as AdminUserController;
use App\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Controllers\Admin\ContactController as AdminContactController;
use App\Controllers\Admin\MediaController as AdminMediaController;
use App\Controllers\Admin\MessageController as AdminMessageController;
use App\Controllers\Stakeholder\MessageController as StakeholderMessageController;
use App\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\FaqController as AdminFaqController;
use App\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Controllers\Admin\RecycleBinController as AdminRecycleBinController;

// --- Public site -------------------------------------------------------
$router->get('/', [HomeController::class, 'index']);
$router->get('/about', [PageController::class, 'about']); // shortcut for the page whose slug is "about"
$router->get('/programme-pillars', [PageController::class, 'pillars']);
$router->get('/news', [PostController::class, 'index']);
$router->get('/news/{slug}', [PostController::class, 'show']);
$router->get('/resources', [ResourceController::class, 'index']);
$router->get('/resources/{id}/download', [ResourceController::class, 'download']);
$router->get('/events', [EventController::class, 'index']);
$router->get('/events/{slug}/calendar', [EventController::class, 'calendar']);
$router->get('/events/{slug}', [EventController::class, 'show']);
$router->post('/events/{id}/register', [EventController::class, 'register']);
$router->get('/team', [TeamController::class, 'index']);
$router->get('/partners', [PartnerController::class, 'index']);
$router->get('/contact', [ContactController::class, 'show']);
$router->post('/contact', [ContactController::class, 'store']);
$router->post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
$router->get('/pages/{slug}', [PageController::class, 'show']); // generic CMS pages (Programme Pillars, etc.)

// --- Auth ----------------------------------------------------------------
$router->get('/login', [LoginController::class, 'show']);
$router->post('/login', [LoginController::class, 'login']);
$router->get('/logout', [LoginController::class, 'logout']);
$router->get('/forgot-password', [PasswordResetController::class, 'showRequestForm']);
$router->post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
$router->get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm']);
$router->post('/reset-password', [PasswordResetController::class, 'reset']);

// --- Stakeholder portal ----------------------------------------------------
$router->get('/portal', [StakeholderDashboardController::class, 'index']);
$router->get('/portal/documents', [DocumentController::class, 'index']);
$router->get('/portal/documents/{id}/download', [DocumentController::class, 'download']);

// --- Admin CMS ---------------------------------------------------------
$router->get('/admin', [AdminDashboardController::class, 'index']);

$router->get('/admin/pages', [AdminPageController::class, 'index']);
$router->get('/admin/pages/create', [AdminPageController::class, 'create']);
$router->post('/admin/pages', [AdminPageController::class, 'store']);
$router->get('/admin/pages/{id}/edit', [AdminPageController::class, 'edit']);
$router->post('/admin/pages/{id}', [AdminPageController::class, 'update']);
$router->post('/admin/pages/{id}/delete', [AdminPageController::class, 'destroy']);
$router->get('/admin/pages/{id}/preview', [AdminPageController::class, 'preview']);

$router->get('/admin/posts', [AdminPostController::class, 'index']);
$router->get('/admin/posts/create', [AdminPostController::class, 'create']);
$router->post('/admin/posts', [AdminPostController::class, 'store']);
$router->get('/admin/posts/{id}/edit', [AdminPostController::class, 'edit']);
$router->post('/admin/posts/{id}', [AdminPostController::class, 'update']);
$router->post('/admin/posts/{id}/delete', [AdminPostController::class, 'destroy']);
$router->get('/admin/posts/{id}/preview', [AdminPostController::class, 'preview']);

$router->get('/admin/resources', [AdminResourceController::class, 'index']);
$router->get('/admin/resources/create', [AdminResourceController::class, 'create']);
$router->post('/admin/resources', [AdminResourceController::class, 'store']);
$router->get('/admin/resources/{id}/edit', [AdminResourceController::class, 'edit']);
$router->post('/admin/resources/{id}', [AdminResourceController::class, 'update']);
$router->post('/admin/resources/{id}/delete', [AdminResourceController::class, 'destroy']);

$router->get('/admin/events', [AdminEventController::class, 'index']);
$router->get('/admin/events/create', [AdminEventController::class, 'create']);
$router->post('/admin/events', [AdminEventController::class, 'store']);
$router->get('/admin/events/{id}/edit', [AdminEventController::class, 'edit']);
$router->post('/admin/events/{id}', [AdminEventController::class, 'update']);
$router->post('/admin/events/{id}/delete', [AdminEventController::class, 'destroy']);
$router->get('/admin/events/{id}/preview', [AdminEventController::class, 'preview']);

$router->get('/admin/team', [AdminTeamController::class, 'index']);
$router->get('/admin/team/create', [AdminTeamController::class, 'create']);
$router->post('/admin/team', [AdminTeamController::class, 'store']);
$router->get('/admin/team/{id}/edit', [AdminTeamController::class, 'edit']);
$router->post('/admin/team/{id}', [AdminTeamController::class, 'update']);
$router->post('/admin/team/{id}/delete', [AdminTeamController::class, 'destroy']);

$router->get('/admin/partners', [AdminPartnerController::class, 'index']);
$router->get('/admin/partners/create', [AdminPartnerController::class, 'create']);
$router->post('/admin/partners', [AdminPartnerController::class, 'store']);
$router->get('/admin/partners/{id}/edit', [AdminPartnerController::class, 'edit']);
$router->post('/admin/partners/{id}', [AdminPartnerController::class, 'update']);
$router->post('/admin/partners/{id}/delete', [AdminPartnerController::class, 'destroy']);

$router->get('/admin/users', [AdminUserController::class, 'index']);
$router->get('/admin/users/create', [AdminUserController::class, 'create']);
$router->post('/admin/users', [AdminUserController::class, 'store']);
$router->get('/admin/users/{id}/edit', [AdminUserController::class, 'edit']);
$router->post('/admin/users/{id}', [AdminUserController::class, 'update']);
$router->post('/admin/users/{id}/delete', [AdminUserController::class, 'destroy']);

$router->get('/admin/organizations', [AdminOrganizationController::class, 'index']);
$router->get('/admin/organizations/create', [AdminOrganizationController::class, 'create']);
$router->post('/admin/organizations', [AdminOrganizationController::class, 'store']);
$router->get('/admin/organizations/{id}/edit', [AdminOrganizationController::class, 'edit']);
$router->post('/admin/organizations/{id}', [AdminOrganizationController::class, 'update']);
$router->post('/admin/organizations/{id}/delete', [AdminOrganizationController::class, 'destroy']);

$router->get('/admin/newsletter', [AdminNewsletterController::class, 'index']);
$router->get('/admin/newsletter/compose', [AdminNewsletterController::class, 'compose']);
$router->post('/admin/newsletter/send', [AdminNewsletterController::class, 'send']);
$router->post('/admin/newsletter/{id}/toggle-status', [AdminNewsletterController::class, 'toggleStatus']);
$router->post('/admin/newsletter/{id}/delete', [AdminNewsletterController::class, 'destroy']);

$router->get('/admin/contact', [AdminContactController::class, 'index']);
$router->get('/admin/contact/{id}', [AdminContactController::class, 'show']);
$router->post('/admin/contact/{id}/status', [AdminContactController::class, 'updateStatus']);
$router->post('/admin/contact/{id}/reply', [AdminContactController::class, 'reply']);
$router->post('/admin/contact/{id}/delete', [AdminContactController::class, 'destroy']);

$router->get('/admin/messages', [AdminMessageController::class, 'index']);
$router->get('/admin/messages/sent', [AdminMessageController::class, 'sent']);
$router->get('/admin/messages/compose', [AdminMessageController::class, 'compose']);
$router->post('/admin/messages', [AdminMessageController::class, 'store']);
$router->get('/admin/messages/{id}', [AdminMessageController::class, 'show']);
$router->post('/admin/messages/{id}/delete', [AdminMessageController::class, 'destroy']);

$router->get('/portal/messages', [StakeholderMessageController::class, 'index']);
$router->get('/portal/messages/{id}', [StakeholderMessageController::class, 'show']);
$router->post('/portal/messages/{id}/delete', [StakeholderMessageController::class, 'destroy']);

$router->get('/admin/media', [AdminMediaController::class, 'index']);
$router->post('/admin/media', [AdminMediaController::class, 'store']);
$router->post('/admin/media/{id}/delete', [AdminMediaController::class, 'destroy']);

$router->get('/admin/settings', [AdminSettingsController::class, 'index']);
$router->post('/admin/settings', [AdminSettingsController::class, 'store']);

$router->get('/admin/categories/{type}', [AdminCategoryController::class, 'index']);
$router->post('/admin/categories/{type}', [AdminCategoryController::class, 'store']);
$router->get('/admin/categories/{type}/{id}/edit', [AdminCategoryController::class, 'edit']);
$router->post('/admin/categories/{type}/{id}', [AdminCategoryController::class, 'update']);
$router->post('/admin/categories/{type}/{id}/delete', [AdminCategoryController::class, 'destroy']);

$router->get('/admin/faqs', [AdminFaqController::class, 'index']);
$router->get('/admin/faqs/create', [AdminFaqController::class, 'create']);
$router->post('/admin/faqs', [AdminFaqController::class, 'store']);
$router->get('/admin/faqs/{id}/edit', [AdminFaqController::class, 'edit']);
$router->post('/admin/faqs/{id}', [AdminFaqController::class, 'update']);
$router->post('/admin/faqs/{id}/delete', [AdminFaqController::class, 'destroy']);
$router->post('/admin/faqs/{id}/move-up', [AdminFaqController::class, 'moveUp']);
$router->post('/admin/faqs/{id}/move-down', [AdminFaqController::class, 'moveDown']);

$router->get('/admin/activity', [AdminActivityLogController::class, 'index']);

$router->get('/admin/recycle-bin', [AdminRecycleBinController::class, 'index']);
$router->post('/admin/recycle-bin/empty', [AdminRecycleBinController::class, 'emptyBin']);
$router->post('/admin/recycle-bin/{type}/{id}/restore', [AdminRecycleBinController::class, 'restore']);
$router->post('/admin/recycle-bin/{type}/{id}/delete', [AdminRecycleBinController::class, 'destroy']);
