<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Middleware\AdminMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Models\Faq;
use App\Services\AuditLogger;

class FaqController extends Controller
{
    public function index(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $this->render('admin.faqs.index', [
            'items' => Faq::all('sort_order ASC, id ASC'),
        ], 'layouts.admin');
    }

    public function create(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $this->render('admin.faqs.create', [], 'layouts.admin');
    }

    public function store(): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $validator = (new Validator($_POST))
            ->required('question', 'Question')
            ->required('answer', 'Answer');

        if (!$validator->passes()) {
            $_SESSION['_old'] = $_POST;
            Session::flash('error', 'Question and answer are both required.');
            $this->redirect('/admin/faqs/create');
        }

        $id = Faq::create([
            'question'     => $this->input('question'),
            'answer'       => $this->input('answer'),
            'sort_order'   => $this->nextSortOrder(),
            'is_published' => $this->input('is_published') ? 1 : 0,
        ]);

        AuditLogger::log('created', 'faqs', $id);
        Session::flash('success', 'FAQ added.');
        $this->redirect('/admin/faqs');
    }

    public function edit(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        $faq = Faq::find((int) $id);
        if (!$faq) {
            $this->abort(404);
        }
        $this->render('admin.faqs.edit', ['faq' => $faq], 'layouts.admin');
    }

    public function update(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $faq = Faq::find((int) $id);
        if (!$faq) {
            $this->abort(404);
        }

        $validator = (new Validator($_POST))
            ->required('question', 'Question')
            ->required('answer', 'Answer');

        if (!$validator->passes()) {
            Session::flash('error', 'Question and answer are both required.');
            $this->redirect('/admin/faqs/' . $id . '/edit');
        }

        Faq::update((int) $id, [
            'question'     => $this->input('question'),
            'answer'       => $this->input('answer'),
            'is_published' => $this->input('is_published') ? 1 : 0,
        ]);

        AuditLogger::log('updated', 'faqs', (int) $id);
        Session::flash('success', 'FAQ updated.');
        $this->redirect('/admin/faqs');
    }

    public function destroy(string $id): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin');
        CsrfMiddleware::handle();

        Faq::delete((int) $id);
        AuditLogger::log('deleted', 'faqs', (int) $id);
        Session::flash('success', 'FAQ deleted.');
        $this->redirect('/admin/faqs');
    }

    /** Swap sort_order with the item immediately above it in the list. */
    public function moveUp(string $id): void
    {
        $this->swapWithNeighbour($id, -1);
    }

    /** Swap sort_order with the item immediately below it in the list. */
    public function moveDown(string $id): void
    {
        $this->swapWithNeighbour($id, 1);
    }

    private function swapWithNeighbour(string $id, int $direction): void
    {
        AdminMiddleware::requireRole('super_admin', 'admin', 'editor');
        CsrfMiddleware::handle();

        $faq = Faq::find((int) $id);
        if (!$faq) {
            $this->abort(404);
        }

        $ordered = Faq::all('sort_order ASC, id ASC');
        $index   = null;
        foreach ($ordered as $i => $row) {
            if ((int) $row['id'] === (int) $id) {
                $index = $i;
                break;
            }
        }

        $neighbourIndex = $index === null ? null : $index + $direction;
        if ($index === null || !isset($ordered[$neighbourIndex])) {
            $this->redirect('/admin/faqs');
        }

        $neighbour = $ordered[$neighbourIndex];
        Faq::update((int) $faq['id'], ['sort_order' => $neighbour['sort_order']]);
        Faq::update((int) $neighbour['id'], ['sort_order' => $faq['sort_order']]);

        AuditLogger::log('updated', 'faqs', (int) $id, ['reordered' => $direction < 0 ? 'up' : 'down']);
        $this->redirect('/admin/faqs');
    }

    private function nextSortOrder(): int
    {
        $all = Faq::all('sort_order DESC');
        return $all ? ((int) $all[0]['sort_order'] + 1) : 0;
    }
}
