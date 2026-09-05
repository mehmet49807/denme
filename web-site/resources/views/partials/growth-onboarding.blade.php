@php
    $showWelcome = session()->pull('growth_show_onboarding');
    $viewer = $viewer ?? auth()->user();
    $profilePercent = (int) ($onboarding['profile']['percent'] ?? 0);
    $trialHours = (int) ($onboarding['trial_hours'] ?? 0);
    $trialDays = (int) ($onboarding['trial_days'] ?? 0);
    $next = $onboarding['next'] ?? null;
@endphp

@if(!empty($onboarding) && $viewer)
<section class="growth-onboarding" aria-label="Başlangıç adımları">
    @if($showWelcome)
        <div class="growth-onboarding-welcome" data-gk-event="onboarding_welcome_view">
            @if($viewer->gender === 'male')
                <h2>Hoş geldin — denemen aktif</h2>
                <p>3 adımda profilini güçlendir, keşfet ve ilk mesajını gönder.
                    @if($trialDays > 0) Denemen yaklaşık <strong>{{ $trialDays }} gün</strong> sürüyor.@endif
                </p>
            @else
                <h2>Hoş geldin</h2>
                <p>Profilini tamamla, ilgini çekenleri beğen ve güvenle sohbet etmeye başla.</p>
            @endif
        </div>
    @endif

    @if($viewer->gender === 'male' && !empty($onboarding['is_on_trial']))
        <div class="growth-trial-countdown" data-gk-event="trial_countdown_view">
            <strong>Deneme geri sayım</strong>
            <span>{{ $trialDays }} gün · {{ $trialHours }} saat kaldı</span>
            <a href="{{ route('search') }}" class="btn btn-primary btn-sm" data-gk-event="trial_first_message_cta">Keşfetmeye başla</a>
        </div>
    @elseif($viewer->gender === 'male' && empty($onboarding['can_message']))
        <div class="growth-trial-countdown growth-trial-countdown--ended" data-gk-event="trial_ended_banner_view">
            <strong>{{ __('app.feed.trial_ended_title') }}</strong>
            <span>{{ __('app.feed.trial_ended_lead') }}</span>
            <div class="growth-trial-countdown__actions">
                <a href="{{ route('premium') }}#premium-packages" class="btn btn-primary btn-sm">Paketleri incele</a>
                <a href="{{ route('search') }}" class="btn btn-outline btn-sm">Üyeleri keşfet</a>
            </div>
        </div>
    @endif

    <div class="growth-onboarding-card">
        <div class="growth-onboarding-head">
            <div>
                <strong>Başlangıç rehberi</strong>
                <span>{{ $onboarding['done'] }}/{{ $onboarding['total'] }} tamamlandı · %{{ $onboarding['percent'] }}</span>
            </div>
            @if(is_array($next))
                <a href="{{ $next['href'] }}" class="btn btn-primary btn-sm" data-gk-event="onboarding_next_cta">{{ $next['label'] }}</a>
            @endif
        </div>

        <div class="growth-onboarding-bar" aria-hidden="true"><span style="width:{{ $onboarding['percent'] }}%"></span></div>

        <div class="growth-onboarding-steps">
            @foreach([1 => 'Profil', 2 => 'Keşif', 3 => 'Sohbet'] as $stepNum => $stepTitle)
                <div class="growth-onboarding-step">
                    <p class="growth-onboarding-step__title"><span>{{ $stepNum }}</span> {{ $stepTitle }}</p>
                    <ul>
                        @foreach(($onboarding['items'] ?? []) as $item)
                            @if(($item['step'] ?? 0) === $stepNum)
                                <li class="{{ !empty($item['done']) ? 'is-done' : '' }}">
                                    <span aria-hidden="true">{{ !empty($item['done']) ? '✓' : '○' }}</span>
                                    @if(!empty($item['done']))
                                        <span>{{ $item['label'] }}</span>
                                    @else
                                        <a href="{{ $item['href'] }}" data-gk-event="onboarding_step_click" data-gk-event-label="{{ $item['key'] }}">{{ $item['label'] }}</a>
                                    @endif
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        @if($profilePercent < 100)
            <p class="growth-onboarding-meta">Profil tamamlanma: <strong>%{{ $profilePercent }}</strong></p>
        @endif
        <p class="growth-onboarding-invite">
            <a href="{{ route('referral') }}" class="btn btn-outline btn-sm" data-gk-event="invite_share" data-gk-event-label="onboarding">WhatsApp ile davet et</a>
        </p>
    </div>
</section>
<style>
.growth-onboarding{margin:0 0 1rem}
.growth-onboarding-welcome{margin:0 0 .75rem;padding:.9rem 1rem;border-radius:16px;background:linear-gradient(135deg,rgba(124,58,237,.1),rgba(236,72,153,.08));border:1px solid rgba(124,58,237,.15)}
.growth-onboarding-welcome h2{margin:0 0 .35rem;font-size:1.05rem}
.growth-onboarding-welcome p{margin:0;font-size:.9rem;color:#4b5563}
.growth-onboarding-card{padding:1rem;border-radius:18px;border:1px solid rgba(15,23,42,.08);background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.04)}
.growth-onboarding-head{display:flex;justify-content:space-between;gap:.75rem;align-items:center;flex-wrap:wrap;margin-bottom:.65rem}
.growth-onboarding-head span{display:block;font-size:.8rem;color:#64748b;margin-top:.15rem}
.growth-onboarding-bar{height:8px;border-radius:999px;background:rgba(15,23,42,.08);overflow:hidden;margin-bottom:.85rem}
.growth-onboarding-bar span{display:block;height:100%;background:linear-gradient(90deg,#7c3aed,#db2777)}
.growth-onboarding-steps{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.65rem}
@media(max-width:720px){.growth-onboarding-steps{grid-template-columns:1fr}}
.growth-onboarding-step{padding:.7rem;border-radius:14px;background:#f8fafc;border:1px solid rgba(15,23,42,.05)}
.growth-onboarding-step__title{margin:0 0 .45rem;font-size:.78rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:#6d28d9;display:flex;align-items:center;gap:.35rem}
.growth-onboarding-step__title span{display:inline-flex;width:1.2rem;height:1.2rem;align-items:center;justify-content:center;border-radius:999px;background:#7c3aed;color:#fff;font-size:.7rem}
.growth-onboarding-step ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.35rem}
.growth-onboarding-step li{display:flex;gap:.4rem;align-items:flex-start;font-size:.84rem;line-height:1.35}
.growth-onboarding-step li.is-done{opacity:.65}
.growth-onboarding-step a{color:#5b21b6;font-weight:600;text-decoration:none}
.growth-onboarding-meta{margin:.75rem 0 0;font-size:.82rem;color:#64748b}
.growth-onboarding-invite{margin:.75rem 0 0}
</style>
@endif
