<?php

namespace App\Services;

use App\Mail\TemplatedMail;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserMailService
{
    public function templates(): array
    {
        return config('email_templates.templates', []);
    }

    public function templateOptions(): array
    {
        return collect($this->templates())
            ->map(fn (array $template, string $key) => [
                'key' => $key,
                'label' => $template['label'] ?? $key,
                'description' => $template['description'] ?? '',
            ])
            ->values()
            ->all();
    }

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

    public function sendWelcome(User $user, ?string $verificationCode = null): bool
    {
        $template = $user->gender === 'female' ? 'female_welcome' : 'welcome';
        $rendered = $this->render($template, $user);
        $body = $rendered['body'];

        // 1) Doğrulama kodu alanı (herkes)
        $codeBlock = $verificationCode
            ? $this->verificationCodeHtml($verificationCode)
            : '';

        // 2) Deneme paketi + premium paketler (yalnızca erkek)
        $premiumBlock = '';
        $trialBlock = '';
        if ($user->gender === 'male') {
            if (method_exists($user, 'isOnTrial') && $user->isOnTrial()) {
                $trialBlock = $this->trialPackageHtml($user);
            }
            $premiumBlock = $this->premiumPackagesHtml($user);
        }

        $extraBlocks = $codeBlock.$trialBlock.$premiumBlock;

        if (str_contains($body, '{verification_code_block}')) {
            $body = str_replace('{verification_code_block}', $codeBlock, $body);
            $extraBlocks = $trialBlock.$premiumBlock;
        }
        if (str_contains($body, '{trial_package_block}')) {
            $body = str_replace('{trial_package_block}', $trialBlock, $body);
            $extraBlocks = str_replace($trialBlock, '', $extraBlocks);
        }
        if (str_contains($body, '{premium_packages_block}')) {
            $body = str_replace('{premium_packages_block}', $premiumBlock, $body);
            $extraBlocks = str_replace($premiumBlock, '', $extraBlocks);
        }

        if ($extraBlocks !== '') {
            $body .= $extraBlocks;
        }

        if ($verificationCode) {
            $body = str_replace('{two_factor_code}', $verificationCode, $body);
        } else {
            $body = str_replace('{two_factor_code}', '', $body);
        }

        $body = str_replace(
            ['{verification_code_block}', '{trial_package_block}', '{premium_packages_block}'],
            ['', '', ''],
            $body
        );

        return $this->send($user, $rendered['subject'], $body, $template);
    }

    private function verificationCodeHtml(string $code): string
    {
        $safe = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:28px 0 8px;background:linear-gradient(135deg,#F5F3FF 0%,#FDF2F8 100%);border:1px solid rgba(124,58,237,0.18);border-radius:16px;overflow:hidden;">
    <tr>
        <td style="padding:22px 20px;text-align:center;">
            <p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:#7C3AED;">E-posta Doğrulama Kodu</p>
            <p style="margin:0 0 14px;font-size:14px;line-height:1.5;color:#3D3550;">Doğrulanmış profil rozeti için aşağıdaki 6 haneli kodu profil sayfanda gir. Kod <strong>15 dakika</strong> geçerlidir.</p>
            <p style="margin:0;display:inline-block;padding:14px 28px;border-radius:14px;background:#1A1523;color:#fff;font-size:28px;font-weight:800;letter-spacing:0.35em;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;">{$safe}</p>
        </td>
    </tr>
</table>
HTML;
    }

    private function trialPackageHtml(User $user): string
    {
        $days = method_exists($user, 'trialDaysRemaining') ? (int) $user->trialDaysRemaining() : 0;
        $hours = method_exists($user, 'trialHoursRemaining') ? (int) $user->trialHoursRemaining() : 0;
        $endsAt = $user->trial_ends_at
            ? $user->trial_ends_at->timezone(config('app.timezone', 'Europe/Istanbul'))->format('d.m.Y H:i')
            : '';

        $remaining = $days > 0
            ? ($days.' gün')
            : ($hours > 0 ? ($hours.' saat') : 'kısa süre');

        $endsLine = $endsAt !== ''
            ? '<p style="margin:8px 0 0;font-size:13px;color:#065F46;">Bitiş: <strong>'.htmlspecialchars($endsAt, ENT_QUOTES, 'UTF-8').'</strong></p>'
            : '';

        $remainingSafe = htmlspecialchars($remaining, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:22px 0 8px;background:linear-gradient(135deg,#ECFDF5 0%,#F0FDF4 100%);border:1px solid rgba(16,185,129,0.28);border-radius:16px;overflow:hidden;">
    <tr>
        <td style="padding:18px 20px;">
            <p style="margin:0 0 6px;font-size:13px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:#059669;">Deneme Paketin Aktif</p>
            <p style="margin:0;font-size:15px;line-height:1.55;color:#064E3B;">
                Premium özellikler seni bekliyor — deneme süren <strong>{$remainingSafe}</strong> daha devam ediyor.
                Bu süre boyunca mesajlaşma ve premium ayrıcalıklardan yararlanabilirsin.
            </p>
            {$endsLine}
        </td>
    </tr>
</table>
HTML;
    }

    private function premiumPackagesHtml(?User $user = null): string
    {
        $packages = [
            ['name' => 'Pro', 'days' => 7, 'price' => '350', 'desc' => 'Mesajlaşma ve temel premium özellikler'],
            ['name' => 'Gold', 'days' => 14, 'price' => '750', 'desc' => 'Öne çıkan profil + hikâye avantajları'],
            ['name' => 'Platinum', 'days' => 30, 'price' => '1200', 'desc' => 'En yüksek görünürlük ve tüm ayrıcalıklar'],
        ];

        try {
            if (class_exists(\App\Services\PremiumPackagesService::class)) {
                $catalog = app(\App\Services\PremiumPackagesService::class)->catalog();
                $mapped = [];
                foreach (['pro', 'gold', 'platinum'] as $key) {
                    if (! isset($catalog[$key])) {
                        continue;
                    }
                    $pkg = $catalog[$key];
                    $mapped[] = [
                        'name' => (string) ($pkg['name'] ?? ucfirst($key)),
                        'days' => (int) ($pkg['duration_days'] ?? 0),
                        'price' => (string) (int) ($pkg['price_tl'] ?? 0),
                        'desc' => match ($key) {
                            'pro' => 'Mesajlaşma ve temel premium özellikler',
                            'gold' => 'Öne çıkan profil + hikâye avantajları',
                            default => 'En yüksek görünürlük ve tüm ayrıcalıklar',
                        },
                    ];
                }
                if ($mapped) {
                    $packages = $mapped;
                }
            }
        } catch (\Throwable) {
            // Varsayılan paket listesi kullanılır
        }

        $rows = '';
        foreach ($packages as $pkg) {
            $name = htmlspecialchars($pkg['name'], ENT_QUOTES, 'UTF-8');
            $days = (int) $pkg['days'];
            $price = htmlspecialchars($pkg['price'], ENT_QUOTES, 'UTF-8');
            $desc = htmlspecialchars($pkg['desc'], ENT_QUOTES, 'UTF-8');
            $rows .= <<<HTML
<tr>
    <td style="padding:12px 14px;border-bottom:1px solid rgba(124,58,237,0.1);">
        <strong style="color:#5B21B6;">{$name}</strong>
        <span style="color:#6B7280;font-size:13px;"> · {$days} gün</span>
        <div style="font-size:13px;color:#4B5563;margin-top:4px;">{$desc}</div>
    </td>
    <td style="padding:12px 14px;border-bottom:1px solid rgba(124,58,237,0.1);text-align:right;white-space:nowrap;font-weight:800;color:#1A1523;">{$price} TL</td>
</tr>
HTML;
        }

        $base = rtrim((string) config('app.url'), '/');
        $premiumUrl = htmlspecialchars($base.'/premium', ENT_QUOTES, 'UTF-8');

        return <<<HTML
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:22px 0;background:#FAF5FF;border:1px solid rgba(124,58,237,0.16);border-radius:16px;overflow:hidden;">
    <tr>
        <td style="padding:18px 18px 8px;">
            <p style="margin:0 0 6px;font-size:13px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:#7C3AED;">Premium Paketler</p>
            <p style="margin:0 0 12px;font-size:14px;line-height:1.5;color:#3D3550;">Deneme süren bittikten sonra mesaj ve premium özellikler için aşağıdaki paketlerden birini seçebilirsin.</p>
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#fff;border-radius:12px;overflow:hidden;">
                {$rows}
            </table>
            <p style="margin:14px 0 0;text-align:center;">
                <a href="{$premiumUrl}" style="display:inline-block;padding:12px 22px;border-radius:999px;background:linear-gradient(135deg,#7C3AED,#DB2777);color:#fff;text-decoration:none;font-weight:700;font-size:14px;">Premium paketleri incele</a>
            </p>
        </td>
    </tr>
</table>
HTML;
    }

    public function sendEmailVerification(User $user, string $verificationUrl): bool
    {
        $rendered = $this->render('email_verification', $user);
        $body = str_replace('{verification_url}', $verificationUrl, $rendered['body']);

        return $this->send($user, $rendered['subject'], $body, 'email_verification');
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

    public function sendTwoFactorCode(User $user, string $code): bool
    {
        $rendered = $this->render('two_factor_code', $user);
        $body = str_replace('{two_factor_code}', $code, $rendered['body']);

        return $this->send($user, $rendered['subject'], $body, 'two_factor_code');
    }

    public function send(User $user, string $subject, string $body, ?string $templateKey = null, ?int $adminId = null): bool
    {
        try {
            Mail::to($user->email)->send(new TemplatedMail($subject, $body));

            $this->logEmail($adminId, $user, $templateKey, $subject, 'sent');

            return true;
        } catch (\Throwable $e) {
            report($e);

            \Illuminate\Support\Facades\Log::error('User mail send failed.', [
                'email' => $user->email,
                'template' => $templateKey,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            $this->logEmail($adminId, $user, $templateKey, $subject, 'failed', $e->getMessage());

            return false;
        }
    }

    public function sendBulk(Collection $users, string $subject, string $body, ?string $templateKey, int $adminId): array
    {
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

        return compact('sent', 'failed');
    }

    public function resolveRecipients(string $target, ?string $email = null): Collection
    {
        if ($target === 'single') {
            return $this->resolveSingleRecipient($email);
        }

        $query = User::query()->where('role', 'user')->where('is_banned', false);

        return match ($target) {
            'male' => $query->where('gender', 'male')->get(),
            'female' => $query->where('gender', 'female')->get(),
            default => $query->get(),
        };
    }

    public function countRecipients(string $target, ?string $email = null): int
    {
        return $this->resolveRecipients($target, $email)->count();
    }

    public function mailDiagnostics(): array
    {
        return [
            'mailer' => config('mail.default'),
            'from' => config('mail.from'),
            'templates' => count($this->templates()),
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
            '{instagram_url}' => \App\Support\InstagramUrl::withUtm('email', 'lifecycle', 'instagram'),
            '{two_factor_code}' => '',
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
