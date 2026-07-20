<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Support\SeoHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SupportPageController extends Controller
{
    public function show(Request $request): View
    {
        SeoHelper::setMultiple([
            'title' => 'Destek — Gönül Köprüsü',
            'description' => 'Gönül Köprüsü destek formu: hesap, güvenlik, premium paket talebi ve teknik yardım.',
            'canonical' => url('/destek'),
            'robots' => 'noindex,follow',
        ]);

        return view('web.support', [
            'lastUpdated' => '20 Temmuz 2026',
            'package' => trim((string) $request->query('package', '')),
            'subject' => trim((string) $request->query('subject', '')),
            'contactEmail' => 'destek@gonulkoprusu.com',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'subject' => 'required|string|max:160',
            'message' => 'required|string|min:10|max:4000',
            'package' => 'nullable|string|max:40',
        ], [
            'message.min' => 'Mesajınız en az 10 karakter olmalı.',
        ]);

        $package = trim((string) ($validated['package'] ?? ''));
        $message = $validated['message'];
        if ($package !== '') {
            $message = 'Paket: '.$package."\n\n".$message;
        }

        $saved = false;
        $mailSent = false;

        if (SupportTicket::ensureTable()) {
            try {
                SupportTicket::create([
                    'user_id' => auth()->id(),
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'subject' => $validated['subject'],
                    'message' => $message,
                    'status' => 'pending',
                ]);
                $saved = true;
            } catch (\Throwable $e) {
                Log::warning('Support ticket save failed.', [
                    'email' => $validated['email'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $body = "Ad: {$validated['name']}\n"
            ."E-posta: {$validated['email']}\n"
            .'Paket: '.($package !== '' ? $package : '-')."\n"
            ."Konu: {$validated['subject']}\n\n"
            .$validated['message'];

        try {
            Mail::raw($body, function ($mail) use ($validated) {
                $mail->to('destek@gonulkoprusu.com')
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject('[Destek] '.$validated['subject']);
            });
            $mailSent = true;
        } catch (\Throwable $e) {
            Log::warning('Support form mail failed.', [
                'email' => $validated['email'],
                'saved' => $saved,
                'error' => $e->getMessage(),
            ]);
        }

        if (! $saved && ! $mailSent) {
            $this->appendFallback($validated, $package, $message);

            return back()
                ->withInput()
                ->withErrors(['message' => 'Şu an talep kaydedilemedi. Lütfen destek@gonulkoprusu.com adresine yazın.']);
        }

        return redirect()
            ->route('support')
            ->with(
                'success',
                $mailSent
                    ? 'Mesajınız alındı. En kısa sürede dönüş yapacağız.'
                    : 'Mesajınız kaydedildi. Destek ekibimiz en kısa sürede dönüş yapacak.'
            );
    }

    /**
     * @param  array{name: string, email: string, subject: string, message: string}  $validated
     */
    private function appendFallback(array $validated, string $package, string $message): void
    {
        try {
            $dir = storage_path('app/support');
            if (! is_dir($dir)) {
                @mkdir($dir, 0750, true);
            }
            $line = json_encode([
                'at' => now()->toIso8601String(),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
                'package' => $package,
                'message' => $message,
                'user_id' => auth()->id(),
            ], JSON_UNESCAPED_UNICODE);
            if (is_string($line) && $line !== '') {
                @file_put_contents($dir.'/fallback.jsonl', $line."\n", FILE_APPEND | LOCK_EX);
            }
        } catch (\Throwable) {
            //
        }
    }
}
