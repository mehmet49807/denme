<?php

namespace App\Services;

use App\Mail\TemplatedMail;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class UserMailService
{
    public function templates(): array
    {
        return config('email_templates.templates', []);
    }

    public function templateOptions(): array
    {
        return collect($this->templates())
            ->map(fn (array $tpl, string $key) => [
                'key' => $key,
                'label' => $tpl['label'],
                'description' => $tpl['description'] ?? '',
                'subject' => $tpl['subject'] ?? '',
                'body' => $tpl['body'] ?? '',
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{subject: string, body: string}
     */
    public function render(string $templateKey, User $user, array $overrides = []): array
    {
        $templates = $this->templates();
        $template = $templates[$templateKey] ?? $templates['custom'] ?? ['subject' => '', 'body' => ''];

        $subject = $overrides['subject'] ?? $template['subject'] ?? '';
        $body = $overrides['body'] ?? $template['body'] ?? '';

        return [
            'subject' => $this->replacePlaceholders($subject, $user),
            'body' => $this->replacePlaceholders($body, $user),
        ];
    }

    public function sendWelcome(User $user): bool
    {
        $template = $user->gender === 'female' ? 'female_welcome' : 'welcome';
        $rendered = $this->render($template, $user);

        return $this->send($user, $rendered['subject'], $rendered['body'], $template);
    }

    public function sendLifecycle(User $user, string $templateKey): bool
    {
        if (! isset($this->templates()[$templateKey])) {
            return false;
        }

        $rendered = $this->render($templateKey, $user);

        return $this->send($user, $rendered['subject'], $rendered['body'], $templateKey);
    }

    public function sendPasswordReset(User $user, string $token): bool
    {
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], false));

        $rendered = $this->render('password_reset', $user);
        $body = str_replace('{reset_url}', $resetUrl, $rendered['body']);

        return $this->send($user, $rendered['subject'], $body, 'password_reset');
    }

    public function send(User $user, string $subject, string $body, ?string $templateKey = null, ?int $adminId = null): bool
    {
        if (!$user->email) {
            return false;
        }

        try {
            Mail::to($user->email, trim($user->first_name.' '.$user->last_name) ?: null)
                ->send(new TemplatedMail($subject, $body, $user->first_name ?: null));

            $this->logEmail($adminId, $user, $templateKey, $subject, 'sent');

            return true;
        } catch (\Throwable $e) {
            Log::warning('Email send failed.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'template' => $templateKey,
                'error' => $e->getMessage(),
            ]);

            $this->logEmail($adminId, $user, $templateKey, $subject, 'failed', $e->getMessage());

            return false;
        }
    }

    /**
     * @return array{sent: int, failed: int}
     */
    public function sendBulk(Collection $users, string $subject, string $body, ?string $templateKey, int $adminId): array
    {
        @set_time_limit(300);

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            $rendered = [
                'subject' => $this->replacePlaceholders($subject, $user),
                'body' => $this->replacePlaceholders($body, $user),
            ];

            if ($this->send($user, $rendered['subject'], $rendered['body'], $templateKey, $adminId)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    public function resolveRecipients(string $target, ?string $email = null): Collection
    {
        $query = User::query()->where('role', 'user')->where('is_banned', false);

        return match ($target) {
            'male' => $query->where('gender', 'male')->get(),
            'female' => $query->where('gender', 'female')->get(),
            'single' => $this->resolveSingleRecipient($email),
            default => $query->get(),
        };
    }

    /** @return array<string, string|bool> */
    public function mailDiagnostics(): array
    {
        return [
            'mailer' => (string) config('mail.default'),
            'host' => (string) config('mail.mailers.smtp.host'),
            'port' => (string) config('mail.mailers.smtp.port'),
            'encryption' => (string) config('mail.mailers.smtp.encryption'),
            'from' => (string) config('mail.from.address'),
            'logs_ready' => Schema::hasTable('email_logs'),
        ];
    }

    private function resolveSingleRecipient(?string $email): Collection
    {
        $email = trim((string) $email);
        if ($email === '') {
            return collect();
        }

        $user = User::query()
            ->where('role', 'user')
            ->where('is_banned', false)
            ->where('email', $email)
            ->first();

        return collect([$user ?? $this->placeholderUser($email)]);
    }

    private function placeholderUser(string $email): User
    {
        $local = strstr($email, '@', true) ?: 'uye';

        $user = new User([
            'email' => $email,
            'first_name' => ucfirst($local),
            'last_name' => '',
            'username' => $local,
            'city' => '',
        ]);

        $user->exists = false;

        return $user;
    }

    private function replacePlaceholders(string $text, User $user): string
    {
        $base = rtrim(config('app.url'), '/');

        $map = [
            '{first_name}' => $user->first_name,
            '{last_name}' => $user->last_name,
            '{username}' => $user->username,
            '{email}' => $user->email,
            '{city}' => $user->city ?? '',
            '{app_url}' => $base,
            '{feed_url}' => $base.'/feed',
            '{profile_url}' => $base.'/profile',
            '{premium_url}' => $base.'/premium',
            '{safe_meeting_url}' => $base.'/guvenli-tanisma',
            '{support_url}' => $base.'/destek',
            '{invite_url}' => $user->referral_code
                ? $base.'/davet/'.$user->referral_code.'?utm_source=email&utm_medium=lifecycle&utm_campaign=invite'
                : $base.'/register?utm_source=email&utm_medium=lifecycle&utm_campaign=invite',
            '{referral_url}' => $base.'/davet',
            '{instagram_url}' => 'https://www.instagram.com/gonulkoprusucom/',
        ];

        return str_replace(array_keys($map), array_values($map), $text);
    }

    private function logEmail(
        ?int $adminId,
        User $user,
        ?string $templateKey,
        string $subject,
        string $status,
        ?string $error = null,
    ): void {
        if (!Schema::hasTable('email_logs')) {
            return;
        }

        EmailLog::create([
            'admin_id' => $adminId,
            'user_id' => $user->exists ? $user->id : null,
            'recipient_email' => $user->email,
            'template_key' => $templateKey,
            'subject' => $subject,
            'status' => $status,
            'error_message' => $error,
        ]);
    }
}
