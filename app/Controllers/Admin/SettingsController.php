<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\SiteSetting;
use App\Services\AuditLogger;

class SettingsController extends Controller
{
    /** key => [group, label] - the editable settings this form manages. */
    private const FIELDS = [
        'site_name'          => ['general', 'Site name'],
        'site_tagline'       => ['general', 'Tagline'],
        'contact_email'      => ['contact', 'Contact email'],
        'contact_phone'      => ['contact', 'Contact phone'],
        'contact_address'    => ['contact', 'Office address'],
        'facebook_url'       => ['social', 'Facebook URL'],
        'twitter_url'        => ['social', 'Twitter / X URL'],
        'linkedin_url'       => ['social', 'LinkedIn URL'],
        'instagram_url'      => ['social', 'Instagram URL'],
        'youtube_url'        => ['social', 'YouTube URL'],
        'meta_description'   => ['seo', 'Default meta description'],
    ];

    /** Shown (and used by the public site) until an admin saves their own value. */
    private const DEFAULTS = [
        'site_name'     => 'Insurance Sector Strengthening Program',
        'contact_email' => 'project@issprogram.com',
        'contact_phone' => '+234 909 563 5335',
        'contact_address' => 'Lagos, Nigeria',
        'linkedin_url'  => 'https://www.linkedin.com/groups/42840128/',
        'youtube_url'   => 'https://youtube.com/@issprogram01?si=x7j8hrH65OdgFPbm',
        'facebook_url'  => 'https://www.facebook.com/profile.php?id=61593538892295',
        'instagram_url' => 'https://www.instagram.com/issprogram/',
    ];

    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');

        $values = [];
        foreach (array_keys(self::FIELDS) as $key) {
            $values[$key] = SiteSetting::get($key, self::DEFAULTS[$key] ?? '') ?? '';
        }

        $this->render('admin.settings.index', ['fields' => self::FIELDS, 'values' => $values], 'layouts.admin');
    }

    public function store(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');
        CsrfMiddleware::handle();

        $errors = [];
        $clean  = [];
        foreach (self::FIELDS as $key => [$group, $label]) {
            $value = trim((string) $this->input($key, ''));
            if ($value !== '' && $key === 'contact_email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Contact email is not a valid email address.';
            }
            if ($value !== '' && str_ends_with($key, '_url') && !preg_match('#^https?://[^\s]+$#i', $value)) {
                $errors[] = $label . ' must start with http:// or https://';
            }
            if ($value !== '' && $key === 'contact_phone' && !preg_match('/^[0-9+()\s.\-]{6,30}$/', $value)) {
                $errors[] = 'Contact phone may only contain digits, spaces, + ( ) - and .';
            }
            $clean[$key] = $value;
        }
        if ($errors) {
            $_SESSION['_old'] = $_POST;
            \App\Core\Session::flash('error', implode(' ', $errors));
            $this->redirect('/admin/settings');
        }

        foreach (self::FIELDS as $key => [$group, $label]) {
            SiteSetting::setValue($key, $clean[$key], $group, Auth::user()['id']);
        }

        AuditLogger::log('updated', 'site_settings', 0);
        \App\Core\Session::flash('success', 'Settings saved.');
        $this->redirect('/admin/settings');
    }
}
