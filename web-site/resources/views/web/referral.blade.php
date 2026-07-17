@extends('layouts.app-with-sidebar')

@php
    $activeNav = 'referral';
    $isFemale = $user->gender === 'female';
@endphp

@section('title', ($isFemale ? 'Arkadaşını Davet Et' : 'Davet Et') . ' — Gönül Köprüsü')

@section('app-content')
<div class="referral-page feed-container">
    <header class="referral-hero {{ $isFemale ? 'referral-hero--female' : '' }}">
        <div class="referral-hero-glow" aria-hidden="true"></div>
        <div class="referral-hero-inner">
            <span class="referral-hero-badge">
                @include('partials.theme-icon', ['icon' => $isFemale ? 'heart' : 'users'])
                {{ $isFemale ? 'Güvenli topluluk' : 'Davet / Referans' }}
            </span>
            <h1>
                @if($isFemale)
                    Yakın çevreni davet et
                @else
                    Arkadaşlarını davet et
                @endif
            </h1>
            <p class="referral-hero-lead">
                @if($isFemale)
                    Güvendiğin bir arkadaşını davet et. Her başarılı davette profilin 24 saat öne çıkar.
                @else
                    Davet linkini paylaş; kayıt olan her arkadaşın için +{{ $rewardDays }} gün premium / deneme hakkı kazan.
                @endif
            </p>
            <div class="referral-hero-stat">
                <strong>{{ number_format($referralCount) }}</strong>
                <span>{{ $isFemale ? 'arkadaşın katıldı' : 'kişi davetinle katıldı' }}</span>
            </div>
            @if($referralCount > 0)
                <p class="referral-badge-note" role="status">Davetçi rozetin aktif — {{ $referralCount }} başarılı davet</p>
            @endif
        </div>
    </header>

    <div class="referral-grid">
        <section class="glass-card referral-card {{ $isFemale ? 'referral-card--female' : '' }}">
            <h2 class="referral-card-title">Davet linkin</h2>
            <p class="referral-card-hint">
                Linki WhatsApp ile gönder veya kopyala. Misafirler önce davet sayfasını görür, sonra kayıt olur.
            </p>
            <label for="inviteUrl" class="sr-only">Davet linki</label>
            <div class="referral-url-row">
                <input type="text" id="inviteUrl" value="{{ $inviteUrl }}" readonly class="referral-url-input">
                <button type="button" class="btn btn-primary" id="copyInviteBtn" data-gk-event="invite_share" data-gk-event-label="copy">Kopyala</button>
            </div>
            <div class="referral-share-actions">
                <a href="{{ $whatsappUrl }}" class="btn btn-outline referral-share-btn referral-share-btn--whatsapp" target="_blank" rel="noopener" data-gk-event="invite_share" data-gk-event-label="whatsapp">
                    WhatsApp ile paylaş
                </a>
            </div>
        </section>

        <section class="glass-card referral-tips {{ $isFemale ? 'referral-tips--female' : '' }}">
            <h2>Ödüller</h2>
            <ul class="referral-tips-list">
                @if($isFemale)
                    <li>Her başarılı davette 24 saat profil öne çıkarma</li>
                    <li>Güvendiğin kişilerle aynı güvenli ortam</li>
                @else
                    <li>Her kayıt için +{{ $rewardDays }} gün deneme uzatma</li>
                    <li>Aynı anda +{{ $rewardDays }} gün premium ödül</li>
                    <li>Davetçi rozeti profilinde görünür</li>
                @endif
            </ul>
        </section>
    </div>

    @if($recentReferrals->isNotEmpty())
        <section class="glass-card referral-recent">
            <h2>Son davetler</h2>
            <ul class="referral-list">
                @foreach($recentReferrals as $ref)
                    <li>
                        <strong>{{ $ref->referred?->username ?? 'Üye' }}</strong>
                        <span>{{ $ref->created_at?->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
@endsection

@push('page-scripts')
<script>
(function () {
    var btn = document.getElementById('copyInviteBtn');
    var input = document.getElementById('inviteUrl');
    if (!btn || !input) return;
    btn.addEventListener('click', function () {
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(function () {
            btn.textContent = 'Kopyalandı!';
            if (window.gkTrack) window.gkTrack('invite_share', { method: 'copy' });
            setTimeout(function () { btn.textContent = 'Kopyala'; }, 2000);
        });
    });
})();
</script>
@endpush
