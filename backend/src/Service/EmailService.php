<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ?string $frontendUrl = null
    ) {
    }
    
    private function getFrontendUrl(): string
    {
        return $this->frontendUrl ?? $_ENV['FRONTEND_URL'] ?? 'http://localhost:3000';
    }

    public function sendPasswordResetEmail(string $email, string $resetToken): void
    {
        $resetUrl = $this->getFrontendUrl() . '/auth/reset-password?token=' . $resetToken;

        $emailMessage = (new Email())
            ->from('noreply@challengepicker.com')
            ->to($email)
            ->subject('Password Reset Request')
            ->html($this->getPasswordResetEmailTemplate($resetUrl));

        $this->mailer->send($emailMessage);
    }

    public function sendEmailVerificationEmail(string $email, string $verificationToken): void
    {
        $verificationUrl = $this->getFrontendUrl() . '/auth/verify-email?token=' . $verificationToken;

        $emailMessage = (new Email())
            ->from('noreply@challengepicker.com')
            ->to($email)
            ->subject('Verify Your Email Address')
            ->html($this->getEmailVerificationTemplate($verificationUrl));

        $this->mailer->send($emailMessage);
    }

    private function getPasswordResetEmailTemplate(string $resetUrl): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { display: inline-block; padding: 12px 24px; background-color: #4F46E5; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .button:hover { background-color: #4338CA; }
        .footer { margin-top: 30px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Password Reset Request</h1>
        <p>You requested to reset your password for Challenge Picker.</p>
        <p>Click the button below to reset your password. This link will expire in 1 hour.</p>
        <a href="{$resetUrl}" class="button">Reset Password</a>
        <p>If you didn't request this, please ignore this email.</p>
        <div class="footer">
            <p>This is an automated message. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getEmailVerificationTemplate(string $verificationUrl): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { display: inline-block; padding: 12px 24px; background-color: #10b981; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .button:hover { background-color: #059669; }
        .footer { margin-top: 30px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verify Your Email Address</h1>
        <p>Welcome to Challenge Picker! Please verify your email address to complete your registration.</p>
        <p>Click the button below to verify your email. This link will expire in 24 hours.</p>
        <a href="{$verificationUrl}" class="button">Verify Email</a>
        <p>If you didn't create an account, please ignore this email.</p>
        <div class="footer">
            <p>This is an automated message. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}

