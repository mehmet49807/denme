<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PremiumPackagesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPackagesController extends Controller
{
    public function index(PremiumPackagesService $packages): View
    {
        return view('admin.packages', [
            'packages' => $packages->catalog(),
            'packageTypes' => array_keys($packages->defaults()),
            'icons' => ['star' => 'Yıldız', 'crown' => 'Taç', 'bolt' => 'Şimşek', 'heart' => 'Kalp', 'sparkles' => 'Parıltı'],
        ]);
    }

    public function update(Request $request, PremiumPackagesService $packages): RedirectResponse
    {
        $types = array_keys($packages->defaults());

        $rules = [];
        foreach ($types as $type) {
            $rules["packages.{$type}.name"] = 'required|string|max:40';
            $rules["packages.{$type}.duration_days"] = 'required|integer|min:1|max:365';
            $rules["packages.{$type}.price_tl"] = 'required|numeric|min:0|max:999999';
            $rules["packages.{$type}.badge_label"] = 'required|string|max:30';
            $rules["packages.{$type}.badge_icon"] = 'required|string|in:star,crown,bolt,heart,sparkles';
            $rules["packages.{$type}.rozet_label"] = 'required|string|max:40';
            $rules["packages.{$type}.rozet_text"] = 'nullable|string|max:180';
            $rules["packages.{$type}.gradient_from"] = 'nullable|string|max:7';
            $rules["packages.{$type}.gradient_to"] = 'nullable|string|max:7';
            $rules["packages.{$type}.badge_enabled"] = 'nullable|boolean';
            $rules["packages.{$type}.featured"] = 'nullable|boolean';
        }

        $validated = $request->validate($rules, [
            'packages.*.name.required' => 'Paket adı zorunludur.',
            'packages.*.duration_days.required' => 'Paket süresi zorunludur.',
            'packages.*.price_tl.required' => 'Paket fiyatı zorunludur.',
            'packages.*.badge_label.required' => 'Rozet etiketi zorunludur.',
        ]);

        $payload = [];
        foreach ($types as $type) {
            $row = $validated['packages'][$type] ?? [];
            $payload[$type] = array_merge($row, [
                'badge_enabled' => $request->boolean("packages.{$type}.badge_enabled"),
                'featured' => $request->input('featured_package') === $type,
            ]);
        }

        $packages->save($payload);

        return redirect()
            ->route('admin.packages')
            ->with('success', 'Premium paketleri ve rozetler kaydedildi.');
    }
}
