@php
    /** @var array{done:int,total:int,percent:int,items:array} $onboarding */
    $showWelcome = session()->pull('growth_show_onboarding');
    $viewer = $viewer ?? auth()->user();
@endphp

@if(!empty($onboarding) && $viewer)
<section class="growth-onboarding" aria-label="İlk 24 saat checklist">
    @if($showWelcome)
        <div class="growth-onboarding-welcome" data-gk-event="onboarding_welcome_view">
            @if($viewer->gender === 'male')
                <h2>3 günlük denemen başladı</h2>
                <p>Hikâye paylaş, mesaj gönder ve profilleri keşfet. Süre bitince paketleri uygulamadan yenileyebilirsin.</p>
                <a href="{{ route('premium') }}" class="btn btn-outline btn-sm" data-gk-event="trial_cta_click">Paketleri gör</a>
            @else
                <h2>Hoş geldin — senin için ücretsiz</h2>
                <p>Mesajlaşma, kimler baktı ve galeri kadın üyelerde ücretsiz. Güvenli ortamda tanışmaya başla.</p>
            @endif
        </div>
    @endif

    <div class="growth-onboarding-card">
        <div class="growth-onboarding-head">
            <h2>İlk 24 saat</h2>
            <span>{{ $onboarding['done'] }}/{{ $onboarding['total'] }}</span>
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
