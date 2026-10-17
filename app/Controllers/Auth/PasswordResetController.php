<?php
declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\Mailer;

class PasswordResetController extends Controller
{
    private const MIN_PASSWORD_LENGTH = 8;

    public function showRequestForm(): void
    {
        $this->render('auth.forgot-password', ['title' => 'Reset your password | ISSP']);
    }

    public function sendResetLink(): void
    {
        CsrfMiddleware::handle();

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (RateLimitMiddleware::tooManyAttempts('reset:' . $ip)) {
            Session::flash('error', 'Too many requests. Please try again in a few minutes.');
            $this->redirect('/forgot-password');
        }

        $email = trim((string) $this->input('email'));
        $user  = $email !== '' ? User::findBy('email', $email) : null;

        // Same response whether or not the account exists, so this form can't
        // be used to find out who has an account.
        if ($user && $user['status'] === 'active' && empty($user['deleted_at'])) {
            $token = PasswordReset::issue((int) $user['id'], $_SERVER['REMOTE_ADDR'] ?? null);
            $link  = rtrim((string) env('APP_URL', ''), '/') . '/reset-password/' . $token;
            $name  = trim($user['first_name'] . ' ' . $user['last_name']);

            $body = '<div style="font-family: Arial, sans-serif; font-size: 15px; color:#1f2937; line-height:1.6;">'
                . '<p>Hello ' . e($user['first_name']) . ',</p>'
                . '<p>We received a request to reset the password for your ISSP portal account. '
                . 'Use the button below to choose a new one. The link works once and expires in 1 hour.</p>'
                . '<p><a href="' . e($link) . '" style="display:inline-block;background:#1F5C3D;color:#ffffff;padding:11px 22px;border-radius:8px;text-decoration:none;font-weight:600;">Choose a new password</a></p>'
                . '<p style="color:#6b7280;font-size:13px;">If the button does not work, copy this address into your browser:<br>' . e($link) . '</p>'
                . '<p style="color:#6b7280;font-size:13px;">If you did not ask for this, you can ignore this email &mdash; your password stays the same.</p>'
                . '</div>';

            Mailer::send($user['email'], $name, 'Reset your ISSP portal password', $body);
        }

        Session::flash('success', 'If that email belongs to an account, a reset link is on its way. It expires in 1 hour.');
        $this->redirect('/login');
    }

    public function showResetForm(string $token): void
    {
        if (!PasswordReset::findValid($token)) {
            Session::flash('error', 'That reset link is invalid or has expired. Please request a new one.');
            $this->redirect('/forgot-password');
        }

        $this->render('auth.reset-password', [
            'token' => $token,
            'title' => 'Choose a new password | ISSP',
            'minLength' => self::MIN_PASSWORD_LENGTH,
        ]);
    }

    public function reset(): void
    {
        CsrfMiddleware::handle();

        $token    = (string) $this->input('token');
        $password = (string) $this->input('password');
        $confirm  = (string) $this->input('password_confirm');

        $row = PasswordReset::findValid($token);
        if (!$row) {
            Session::flash('error', 'That reset link is invalid or has expired. Please request a new one.');
            $this->redirect('/forgot-password');
        }

        if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
            Session::flash('error', 'Your password must be at least ' . self::MIN_PASSWORD_LENGTH . ' characters.');
            $this->redirect('/reset-password/' . $token);
        }
        if ($password !== $confirm) {
            Session::flash('error', 'The two passwords do not match.');
            $this->redirect('/reset-password/' . $token);
        }

        $userId = (int) $row['user_id'];
        User::update($userId, [
            'password_hash'         => password_hash($password, PASSWORD_DEFAULT),
            'must_change_password'  => 0,
            'failed_login_attempts' => 0,
            'locked_until'          => null,
        ]);
        PasswordReset::consumeAllFor($userId);

        Session::flash('success', 'Your password has been reset. You can log in now.');
        $this->redirect('/login');
    }
}
