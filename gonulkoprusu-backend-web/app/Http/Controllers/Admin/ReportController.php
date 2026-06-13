<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $reports = Report::with(['reporter', 'reported'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.reports.index', compact('reports'));
    }

    public function updateStatus(Request $request, Report $report): RedirectResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['pending', 'reviewed', 'resolved', 'dismissed'])],
        ]);

        $report->update(['status' => $request->status]);

        return back()->with('status', 'Şikayet durumu güncellendi.');
    }
}
