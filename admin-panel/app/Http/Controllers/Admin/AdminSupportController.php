<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminSupportController extends Controller
{
    public function __construct(private SiteSettingsService $settings) {}

    public function index(): View
    {
        $tableReady = SupportTicket::ensureTable() || Schema::hasTable('support_tickets');

        $tickets = $tableReady
            ? SupportTicket::with('user:id,username,email')
                ->latest()
                ->paginate(25)
            : collect();

        $pendingCount = $tableReady
            ? SupportTicket::where('status', 'pending')->count()
            : 0;

        return view('admin.support', [
            'tableReady' => $tableReady,
            'tickets' => $tickets,
            'pendingCount' => $pendingCount,
            'supportEmail' => (string) $this->settings->get('support_email'),
            'supportPhone' => (string) $this->settings->get('support_phone', ''),
            'supportWhatsapp' => (string) $this->settings->get('support_whatsapp', ''),
            'supportHours' => (string) $this->settings->get('support_hours', '7/24'),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'support_email' => 'required|email|max:190',
            'support_phone' => 'nullable|string|max:40',
            'support_whatsapp' => 'nullable|string|max:40',
            'support_hours' => 'nullable|string|max:80',
        ]);

        $this->settings->setMany($validated);

        return back()->with('success', '7/24 destek ayarları güncellendi.');
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'admin_reply' => 'required|string|max:5000',
            'status' => 'required|in:pending,answered,closed',
        ]);

        $ticket->update([
            'admin_reply' => $validated['admin_reply'],
            'status' => $validated['status'],
            'replied_at' => now(),
        ]);

        return back()->with('success', 'Yanıt kaydedildi.');
    }
}
