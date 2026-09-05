@php
    $trialBannerUser = $viewer ?? auth()->user();
    $showTrialEnding = false;
    $trialHoursLeft = 0;
    if ($trialBannerUser && ($trialBannerUser->gender ?? null) === 'male' && method_exists($trialBannerUser, 'isOnTrial') && $trialBannerUser->isOnTrial()) {
        $trialHoursLeft = method_exists($trialBannerUser, 'trialHoursRemaining') ? (int) $trialBannerUser->trialHoursRemaining() : 0;
        $showTrialEnding = $trialHoursLeft > 0 && $trialHoursLeft <= 24;
    }
@endphp
@if($showTrialEnding)
<section class="trial-ending-banner" role="status">
    <div>
        <strong>Deneme süren bitmek üzere</strong>
        <p>Yaklaşık <strong>{{ $trialHoursLeft }} saat</strong> sonra denemen sona erecek. Premium özellikleri kaçırma.</p>
    </div>
    <a href="{{ route('premium') }}" class="btn btn-primary btn-sm">Paketleri gör</a>
</section>
<style>
.trial-ending-banner{display:flex;gap:.85rem;align-items:center;justify-content:space-between;flex-wrap:wrap;margin:0 0 1rem;padding:.9rem 1rem;border-radius:16px;background:linear-gradient(135deg,#fff7ed,#fef3c7);border:1px solid rgba(245,158,11,.35);}
.trial-ending-banner p{margin:.2rem 0 0;font-size:.88rem;color:#7c2d12;}
</style>
@endif
