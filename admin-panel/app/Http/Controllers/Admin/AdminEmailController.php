<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBulkEmailJob;
use App\Models\EmailLog;
use App\Models\User;
use App\Services\UserMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminEmailController extends Controller
{
    private const BULK_SYNC_LIMIT = 5;

    public function __construct(private UserMailService $mailService) {}

    public function index(): View
    {
        $logs = Schema::hasTable('email_logs')
            ? EmailLog::with(['user', 'admin'])->latest('created_at')->limit(30)->get()
            : collect();

        $logsCount = Schema::hasTable('email_logs') ? EmailLog::count() : 0;

        return view('admin.emails', [
            'templates' => $this->mailService->templateOptions(),
            'logs' => $logs,
            'logsCount' => $logsCount,
            'userCount' => User::where('role', 'user')->where('is_banned', false)->count(),
            'tableReady' => Schema::hasTable('email_logs'),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template_key' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:20000',
            'target' => 'required|in:all,male,female,single',
            'single_email' => 'nullable|email|max:255',
        ], [
            'subject.required' => 'E-posta konusu zorunludur.',
            'body.required' => 'E-posta içeriği zorunludur.',
        ]);

        if ($validated['target'] === 'single' && empty($validated['single_email'])) {
            return back()->withErrors(['single_email' => 'Tek kullanıcı gönderimi için e-posta adresi girin.'])->withInput();
        }

        $recipientCount = $this->mailService->countRecipients(
            $validated['target'],
            $validated['single_email'] ?? null,
        );

        if ($recipientCount === 0) {
            return back()->withErrors(['single_email' => 'Bu kriterlere uygun kullanıcı bulunamadı.'])->withInput();
        }

        if ($recipientCount > self::BULK_SYNC_LIMIT) {
            SendBulkEmailJob::dispatchAfterResponse(
                $validated['target'],
                $validated['single_email'] ?? null,
                $validated['subject'],
                $validated['body'],
                $validated['template_key'],
                $request->user()->id,
            );

            return redirect()->route('admin.emails')->with(
                'success',
                "{$recipientCount} alıcıya e-posta gönderimi arka planda başlatıldı. Tamamlandığında log kayıtlarından takip edebilirsiniz."
            );
        }

        $recipients = $this->mailService->resolveRecipients(
            $validated['target'],
            $validated['single_email'] ?? null,
        );

        $result = $this->mailService->sendBulk(
            $recipients,
            $validated['subject'],
            $validated['body'],
            $validated['template_key'],
            $request->user()->id,
        );

        $message = "{$result['sent']} e-posta gönderildi";
        if ($result['failed'] > 0) {
            $message .= ", {$result['failed']} başarısız";
        }
        $message .= '.';

        return redirect()->route('admin.emails')->with('success', $message);
    }

    public function preview(Request $request): View
    {
        $request->validate([
            'template_key' => 'required|string',
        ]);

        $user = User::where('role', 'user')->first() ?? $request->user();
        $rendered = $this->mailService->render($request->template_key, $user);

        return view('admin.email-preview', [
            'subject' => $rendered['subject'],
            'htmlBody' => $rendered['body'],
        ]);
    }

    public function clearLogs(): RedirectResponse
    {
        if (! Schema::hasTable('email_logs')) {
            return redirect()->route('admin.emails')->with('error', 'E-posta log tablosu bulunamadı.');
        }

        $count = EmailLog::count();
        if ($count === 0) {
            return redirect()->route('admin.emails')->with('success', 'Temizlenecek e-posta kaydı yok.');
        }

        EmailLog::query()->delete();

        return redirect()->route('admin.emails')->with('success', "{$count} e-posta kaydı temizlendi.");
    }

    public function test(Request $request): RedirectResponse
    {
        $admin = $request->user();

        try {
            $this->mailService->sendBulk(
                collect([$admin]),
                'Gönül Köprüsü — Test E-postası',
                '<p>Bu bir test e-postasıdır. E-posta gönderim sistemi çalışıyor.</p>',
                'custom',
                $admin->id,
            );

            return redirect()->route('admin.emails')->with('success', 'Test e-postası '.$admin->email.' adresine gönderildi.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.emails')->with('error', 'Test e-postası gönderilemedi: '.$e->getMessage());
        }
    }
}
