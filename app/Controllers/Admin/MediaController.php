<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Media;
use App\Services\AuditLogger;
use App\Services\FileUploader;

class MediaController extends Controller
{
    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $this->render('admin.media.index', ['items' => Media::all('created_at DESC')], 'layouts.admin');
    }

    public function store(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $uploaded = $this->file('file');
        if (!$uploaded) {
            \App\Core\Session::flash('error', 'Choose a file to upload.');
            $this->redirect('/admin/media');
        }

        $path = FileUploader::storePublic($uploaded, 'library/');
        if ($path === null) {
            \App\Core\Session::flash('error', 'Could not store that file - check the file type (PDF, DOC, DOCX, JPG, PNG, WEBP only).');
            $this->redirect('/admin/media');
        }

        $id = Media::create([
            'disk'            => 'local',
            'file_path'       => $path,
            'file_name'       => $uploaded['name'],
            'mime_type'       => mime_content_type($path),
            'file_size_bytes' => filesize($path),
            'alt_text'        => $this->input('alt_text'),
            'uploaded_by'     => Auth::user()['id'],
        ]);

        AuditLogger::log('created', 'media', $id);
        \App\Core\Session::flash('success', 'File uploaded.');
        $this->redirect('/admin/media');
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        Media::softDelete((int) $id);
        AuditLogger::log('deleted', 'media', (int) $id);
        \App\Core\Session::flash('success', 'File removed from the library.');
        $this->redirect('/admin/media');
    }
}
