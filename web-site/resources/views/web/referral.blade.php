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
                <button type="button" class="btn btn-primary referral-share-btn" id="inviteStoryKitBtn"
                    data-share-url="{{ $inviteUrl }}"
                    data-username="{{ $user->username }}"
                    data-gk-event="invite_share" data-gk-event-label="story_kit">
                    Story görseli indir
                </button>
                <button type="button" class="btn btn-outline referral-share-btn" id="nativeShareInviteBtn"
                    data-share-url="{{ $inviteUrl }}"
                    data-share-text="{{ $shareText }}"
                    data-gk-event="invite_share" data-gk-event-label="native_share">
                    Paylaş
                </button>
                <a href="{{ \App\Support\InstagramUrl::withUtm('referral', 'share', 'instagram') }}"
                   class="btn btn-ghost referral-share-btn" target="_blank" rel="noopener"
                   data-gk-event="instagram_cta" data-gk-event-label="referral">
                    Instagram’ı aç
                </a>
            </div>
            <p class="referral-card-hint" style="margin-top:.65rem">
                Reels / Hikâye: görseli indir → Instagram’a yükle → linki yapıştır. Link otomatik kopyalanır.
            </p>
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
            @if(!empty($nextMilestone))
                <p class="referral-next-milestone">
                    Sonraki hedef: <strong>{{ $nextMilestone['label'] }}</strong>
                    ({{ $nextMilestone['left'] }} davet kaldı)
                </p>
            @endif
            @if(!empty($milestones))
                <ol class="referral-milestones" aria-label="Davet hedefleri">
                    @foreach($milestones as $m)
                        <li class="{{ !empty($m['reached']) ? 'is-reached' : '' }}{{ !empty($m['current']) ? ' is-current' : '' }}">
                            <span>{{ $m['label'] }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif
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

    <section class="glass-card referral-leaderboard">
        <h2>Bu haftanın davet liderleri</h2>
        <p class="referral-card-hint">
            @if(!empty($weekStart) && !empty($weekEnd))
                {{ $weekStart->translatedFormat('d M') }} – {{ $weekEnd->translatedFormat('d M') }}
            @endif
            · 1. olan: erkeklerde +7 gün Premium, kadınlarda 48 saat öne çıkarma
            @if(($myWeeklyTotal ?? 0) > 0)
                · Senin bu hafta: <strong>{{ $myWeeklyTotal }}</strong> davet
            @endif
        </p>
        @if(!empty($leaderboard))
            <ol class="referral-leaderboard-list">
                @foreach($leaderboard as $i => $row)
                    <li class="{{ ($row['user_id'] ?? null) === $user->id ? 'is-me' : '' }}">
                        <span class="referral-leaderboard-rank">{{ $i + 1 }}</span>
                        <strong>{{ $row['username'] }}</strong>
                        @if(!empty($row['city']))
                            <span>{{ $row['city'] }}</span>
                        @endif
                        <em>{{ number_format($row['total']) }} davet</em>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="referral-card-hint">Bu hafta henüz davet yok — ilk sırayı sen kap.</p>
        @endif
        <p class="referral-card-hint">
            Instagram’da hikâyene link yapıştır:
            <a href="{{ $instagramUrl ?? \App\Support\InstagramUrl::withUtm('referral', 'share', 'instagram') }}" target="_blank" rel="noopener" data-gk-event="instagram_cta" data-gk-event-label="referral_leaderboard">@gonulkoprusucom</a>
        </p>
    </section>
</div>
@endsection

@push('page-scripts')
@include('partials.asset', ['path' => 'js/invite-story-kit.min.js', 'defer' => true])
<script>
(function () {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    function markShared(method) {
        if (window.gkTrack) window.gkTrack('invite_share', { method: method || 'share', event_category: 'growth' });
        if (!csrf) return;
        fetch(@json(route('referral.mark-shared')), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).catch(function () {});
    }

    var btn = document.getElementById('copyInviteBtn');
    var input = document.getElementById('inviteUrl');
    if (btn && input) {
        btn.addEventListener('click', function () {
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(function () {
                btn.textContent = 'Kopyalandı!';
                markShared('copy');
                setTimeout(function () { btn.textContent = 'Kopyala'; }, 2000);
            });
        });
    }
    var shareBtn = document.getElementById('nativeShareInviteBtn');
    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            var url = shareBtn.getAttribute('data-share-url') || '';
            var text = (shareBtn.getAttribute('data-share-text') || '').trim();
            var full = (text ? text + ' ' : '') + url;
            markShared('native_share');
            if (navigator.share) {
                navigator.share({ title: 'Gönül Köprüsü', text: text, url: url }).catch(function () {});
                return;
            }
            if (navigator.clipboard && full) {
                navigator.clipboard.writeText(full).then(function () {
                    shareBtn.textContent = 'Link kopyalandı';
                });
            }
        });
    }
    document.querySelectorAll('[data-gk-event="invite_share"]').forEach(function (el) {
        if (el.id === 'copyInviteBtn' || el.id === 'nativeShareInviteBtn') return;
        el.addEventListener('click', function () {
            markShared(el.getAttribute('data-gk-event-label') || 'share');
        });
    });
})();
</script>
@endpush
