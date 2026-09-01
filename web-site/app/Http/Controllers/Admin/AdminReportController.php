<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with(['reporter', 'reported']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $reports = $query->latest()->paginate(20);

        return view('admin.reports.index', compact('reports'));
    }

    public function update(Request $request, Report $report)
    {
        $validated = $request->validate([
            'status'      => 'required|in:pending,investigating,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:1000',
            'ban_user'    => 'nullable|boolean',
        ]);

        $report->update([
            'status'       => $validated['status'],
            'admin_notes'  => $validated['admin_notes'] ?? null,
            'resolved_by'  => $request->user()->id,
            'resolved_at'  => in_array($validated['status'], ['resolved', 'dismissed']) ? now() : null,
        ]);

        if ($validated['status'] === 'resolved' && ! empty($validated['ban_user'])) {
            $reported = User::find($report->reported_id);
            if ($reported) {
                $reported->forceFill([
                    'is_banned'     => true,
                    'banned_at'      => now(),
                    'banned_reason'  => 'Şikayet #' . $report->id . ' sonucu banlandı.',
                ])->save();
            }
        }

        return back()->with('success', 'Şikayet güncellendi.');
    }
}
