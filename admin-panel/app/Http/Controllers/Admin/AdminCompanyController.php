<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminCompanyController extends Controller
{
    public function index(SiteSettingsService $settings): View
    {
        $all = $settings->all();

        return view('admin.company', [
            'settings' => $all,
        ]);
    }

    public function update(Request $request, SiteSettingsService $settings): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_tax_office' => 'nullable|string|max:255',
            'company_tax_number' => 'nullable|string|max:50',
            'company_mersis' => 'nullable|string|max:50',
            'company_trade_registry' => 'nullable|string|max:100',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:255',
            'company_representative' => 'nullable|string|max:255',
            'company_kvkk_contact' => 'nullable|string|max:255',
        ], [
            'company_email.email' => 'Şirket e-posta adresi geçerli bir e-posta olmalıdır.',
        ]);

        $settings->setMany([
            'company_name' => trim((string) ($validated['company_name'] ?? '')),
            'company_tax_office' => trim((string) ($validated['company_tax_office'] ?? '')),
            'company_tax_number' => trim((string) ($validated['company_tax_number'] ?? '')),
            'company_mersis' => trim((string) ($validated['company_mersis'] ?? '')),
            'company_trade_registry' => trim((string) ($validated['company_trade_registry'] ?? '')),
            'company_address' => trim((string) ($validated['company_address'] ?? '')),
            'company_phone' => trim((string) ($validated['company_phone'] ?? '')),
            'company_email' => trim((string) ($validated['company_email'] ?? '')),
            'company_representative' => trim((string) ($validated['company_representative'] ?? '')),
            'company_kvkk_contact' => trim((string) ($validated['company_kvkk_contact'] ?? '')),
        ]);

        return redirect()
            ->route('admin.company')
            ->with('success', 'Şirket bilgileri kaydedildi. Hakkımızda, KVKK ve Gizlilik sayfalarında görünecek.');
    }
}
