<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class VerificationCodeNotification extends Notification
{
    use Queueable;

    private const EXPIRES_IN_MINUTES = 15;

    public function __construct(
        public readonly string $code,
        public readonly string $purpose,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $content = $this->content();

        return (new MailMessage)
            ->subject($content['subject'])
            ->view('emails.auth.verification-code', [
                'appName' => (string) config('app.name', 'Рука добра'),
                'code' => $this->code,
                'expiresInMinutes' => self::EXPIRES_IN_MINUTES,
                'heading' => $content['heading'],
                'intro' => $content['intro'],
                'preheader' => $content['preheader'],
                'subject' => $content['subject'],
                'warning' => $content['warning'],
            ]);
    }

    /**
     * @return array{subject: string, heading: string, intro: string, preheader: string, warning: string}
     */
    private function content(): array
    {
        return match ($this->purpose) {
            'password_reset' => [
                'subject' => 'Код восстановления пароля',
                'heading' => 'Восстановление пароля',
                'intro' => 'Введите этот код на странице восстановления, чтобы задать новый пароль.',
                'preheader' => 'Код восстановления пароля: '.$this->code,
                'warning' => 'Если вы не запрашивали восстановление пароля, просто проигнорируйте это письмо.',
            ],
            'email_two_factor_enable' => [
                'subject' => 'Код включения email-2FA',
                'heading' => 'Подключение двухфакторной проверки',
                'intro' => 'Введите этот код в личном кабинете, чтобы включить подтверждение входа по email.',
                'preheader' => 'Код для включения email-2FA: '.$this->code,
                'warning' => 'Если вы не включали email-2FA, проверьте безопасность аккаунта и смените пароль.',
            ],
            'two_factor_login' => [
                'subject' => 'Код входа в личный кабинет',
                'heading' => 'Подтверждение входа',
                'intro' => 'Введите этот код на экране входа, чтобы завершить авторизацию.',
                'preheader' => 'Код входа в личный кабинет: '.$this->code,
                'warning' => 'Если вы не входили в аккаунт, никому не сообщайте код и смените пароль.',
            ],
            default => [
                'subject' => 'Код подтверждения',
                'heading' => 'Подтверждение действия',
                'intro' => 'Введите этот код на сайте, чтобы подтвердить действие.',
                'preheader' => 'Код подтверждения: '.$this->code,
                'warning' => 'Если вы не запрашивали код, просто проигнорируйте это письмо.',
            ],
        };
    }
}
