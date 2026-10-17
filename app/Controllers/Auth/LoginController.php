<?php
declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Auth;
use App\Core\Controller;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Models\LoginAuditLog;

class LoginController extends Controller
{
    public function show(): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::isStaff() ? '/admin' : '/portal');
        }
        $this->render('auth.login', [], 'layouts.main');
    }

    public function login(): void
    {
        CsrfMiddleware::handle();

        $email    = (string) $this->input('email');
        $password = (string) $this->input('password');

        if (RateLimitMiddleware::tooManyAttempts('login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'))) {
            \App\Core\Session::flash('error', 'Too many attempts. Please try again in a few minutes.');
            $this->redirect('/login');
        }

        if (Auth::attempt($email, $password)) {
            $this->redirect(Auth::isStaff() ? '/admin' : '/portal');
        }

        \App\Core\Session::flash('error', 'Those credentials do not match our records.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
