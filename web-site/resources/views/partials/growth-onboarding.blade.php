@php
    /** @var array{done:int,total:int,percent:int,items:array,profile?:array,trial_days?:int,trial_hours?:int,is_on_trial?:bool,can_message?:bool} $onboarding */
    $showWelcome = session()->pull('growth_show_onboarding');
    $viewer = $viewer ?? auth()->user();
    $profilePercent = (int) ($onboarding['profile']['percent'] ?? 0);
    $trialHours = (int) ($onboarding['trial_hours'] ?? 0);
    $trialDays = (int) ($onboarding['trial_days'] ?? 0);
@endphp

@if(!empty($onboarding) && $viewer)
<section class="growth-onboarding" aria-label="İlk 24 saat checklist">
    @if($showWelcome)
        <div class="growth-onboarding-welcome" data-gk-event="onboarding_welcome_view">
            @if($viewer->gender === 'male')
                <h2>3 günlük denemen başladı</h2>
                <p>Hikâye paylaş, ilk mesajını gönder ve profilini güçlendir. Süre bitince paketleri uygulamadan yenileyebilirsin.</p>
                <a href="{{ route('premium') }}" class="btn btn-outline btn-sm" data-gk-event="trial_cta_click">Paketleri gör</a>
            @else
                <h2>Hoş geldin — senin için ücretsiz</h2>
                <p>Mesajlaşma, kimler baktı ve galeri kadın üyelerde ücretsiz. Güvenli ortamda tanışmaya başla.</p>
            @endif
        </div>
    @endif

    @if($viewer->gender === 'male' && !empty($onboarding['is_on_trial']))
        <div class="growth-trial-countdown" data-gk-event="trial_countdown_view">
            <strong>Deneme geri sayım</strong>
            <span>{{ $trialDays }} gün · {{ $trialHours }} saat kaldı</span>
            <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm" data-gk-event="trial_first_message_cta">İlk mesajını gönder</a>
        </div>
    @elseif($viewer->gender === 'male' && empty($onboarding['can_message']))
        <div class="growth-trial-countdown growth-trial-countdown--ended">
            <strong>Deneme bitti</strong>
            <span>Mesaj ve hikâye için premium paket gerekli.</span>
            <a href="{{ route('premium') }}#premium-packages" class="btn btn-primary btn-sm" data-gk-event="trial_cta_click" data-gk-event-label="ended">Paketleri incele</a>
        </div>
    @endif

    <div class="growth-onboarding-card">
        <div class="growth-onboarding-head">
            <h2>İlk 24 saat</h2>
            <span>{{ $onboarding['done'] }}/{{ $onboarding['total'] }}</span>
        </div>
        <div class="growth-profile-score" aria-label="Profil tamamlanma">
            <span>Profil skoru</span>
            <strong>%{{ $profilePercent }}</strong>
            <div class="growth-onboarding-bar" aria-hidden="true"><span style="width: {{ $profilePercent }}%"></span></div>
        </div>
        <div class="growth-onboarding-bar" aria-hidden="true">
            <span style="width: {{ $onboarding['percent'] }}%"></span>
        </div>
        <ul class="growth-onboarding-list">
            @foreach($onboarding['items'] as $item)
                <li class="{{ $item['done'] ? 'is-done' : '' }}">
                    <span class="growth-onboarding-check" aria-hidden="true">{{ $item['done'] ? '✓' : '○' }}</span>
                    @if($item['done'])
                        <span>{{ $item['label'] }}</span>
                    @else
                        <a href="{{ $item['href'] }}" data-gk-event="onboarding_step_click" data-gk-event-label="{{ $item['key'] }}">{{ $item['label'] }}</a>
                    @endif
                </li>
            @endforeach
        </ul>
        <p class="growth-onboarding-invite">
            <a href="{{ route('referral') }}" class="btn btn-primary btn-sm" data-gk-event="invite_share" data-gk-event-label="onboarding">WhatsApp ile davet et</a>
        </p>
    </div>
</section>
@endif
