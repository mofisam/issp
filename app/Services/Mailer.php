<?php
declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Thin wrapper around PHPMailer (installed via `composer require phpmailer/phpmailer`).
 * Used for: newsletter confirmations, contact-form acknowledgements, password resets.
 */
class Mailer
{
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        if (!class_exists(PHPMailer::class)) {
            error_log("Mailer: PHPMailer not installed - run 'composer install'. Email to {$toEmail} not sent.");
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', 'localhost');
            $mail->Port       = (int) env('MAIL_PORT', 587);
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME', '');
            $mail->Password   = env('MAIL_PASSWORD', '');
            $mail->setFrom((string) env('MAIL_FROM_ADDRESS', 'project@issprogram.com'), (string) env('MAIL_FROM_NAME', 'ISSP'));
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send the same subject/body to many recipients over a single SMTP
     * connection (PHPMailer's keep-alive), instead of reconnecting once
     * per email via send(). Used for newsletter broadcasts.
     *
     * @param array<int, array{email: string, name: string}> $recipients
     * @return array{sent: int, failed: array<int, string>}
     */
    public static function sendBulk(array $recipients, string $subject, string $htmlBody): array
    {
        $result = ['sent' => 0, 'failed' => []];

        if (!class_exists(PHPMailer::class)) {
            error_log('Mailer: PHPMailer not installed - run "composer install". Bulk email not sent.');
            $result['failed'] = array_column($recipients, 'email');
            return $result;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host          = env('MAIL_HOST', 'localhost');
            $mail->Port          = (int) env('MAIL_PORT', 587);
            $mail->SMTPAuth      = true;
            $mail->Username      = env('MAIL_USERNAME', '');
            $mail->Password      = env('MAIL_PASSWORD', '');
            $mail->SMTPKeepAlive = true;
            $mail->setFrom((string) env('MAIL_FROM_ADDRESS', 'project@issprogram.com'), (string) env('MAIL_FROM_NAME', 'ISSP'));
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;

            foreach ($recipients as $recipient) {
                $mail->clearAddresses();
                try {
                    $mail->addAddress($recipient['email'], $recipient['name'] ?? '');
                    $mail->send();
                    $result['sent']++;
                } catch (PHPMailerException $e) {
                    error_log('Mailer bulk error for ' . $recipient['email'] . ': ' . $e->getMessage());
                    $result['failed'][] = $recipient['email'];
                }
            }

            $mail->smtpClose();
        } catch (PHPMailerException $e) {
            error_log('Mailer bulk setup error: ' . $e->getMessage());
            $result['failed'] = array_column($recipients, 'email');
        }

        return $result;
    }
}
