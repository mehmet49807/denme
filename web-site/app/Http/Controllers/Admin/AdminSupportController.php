<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class AdminSupportController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with('user');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $tickets = $query->latest()->paginate(20);

        return view('admin.support.index', compact('tickets'));
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status'       => 'required|in:open,replied,closed',
            'admin_reply'  => 'nullable|string|max:2000',
        ]);

        $data = ['status' => $validated['status']];

        if (! empty($validated['admin_reply'])) {
            $data['admin_reply'] = $validated['admin_reply'];
            $data['replied_at'] = now();
        }

        $ticket->update($data);

        return back()->with('success', 'Destek talebi güncellendi.');
    }
}
