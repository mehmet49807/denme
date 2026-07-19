<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\SeoHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'lastUpdated' => '19 Temmuz 2026',
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

        $body = "Ad: {$validated['name']}\n"
            ."E-posta: {$validated['email']}\n"
            .'Paket: '.($validated['package'] ?: '-')."\n"
            ."Konu: {$validated['subject']}\n\n"
            .$validated['message'];

        try {
            Mail::raw($body, function ($mail) use ($validated) {
                $mail->to('destek@gonulkoprusu.com')
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject('[Destek] '.$validated['subject']);
            });
        } catch (\Throwable) {
            return back()
                ->withInput()
                ->withErrors(['message' => 'Şu an e-posta gönderilemedi. Lütfen destek@gonulkoprusu.com adresine yazın.']);
        }

        return redirect()
            ->route('support')
            ->with('success', 'Mesajınız alındı. En kısa sürede dönüş yapacağız.');
    }
}
