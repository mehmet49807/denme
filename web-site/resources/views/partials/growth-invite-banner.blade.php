@php
    $viewer = $viewer ?? auth()->user();
    $showInviteBanner = ! empty($showInviteBanner);
    $rewardDays = \App\Models\User::REFERRAL_REWARD_DAYS;
    $isFemale = $viewer && $viewer->gender === 'female';
    $whatsappUrl = null;
    $inviteUrl = null;
    $shareText = '';
    if ($showInviteBanner && $viewer) {
        try {
            $referral = app(\App\Services\ReferralService::class);
            $shareText = $isFemale
                ? 'Gönül Köprüsü\'nde buluşalım — seni davet ediyorum:'
                : 'Gönül Köprüsü\'ne gel, birlikte keşfedelim:';
            $whatsappUrl = $referral->whatsappShareUrl($viewer, $shareText);
            $inviteUrl = $referral->inviteUrl($viewer);
        } catch (\Throwable) {
            $whatsappUrl = route('referral');
            $inviteUrl = route('referral');
        }
    }
@endphp
@if($showInviteBanner && $viewer)
<section class="gib" id="gkInviteBanner" aria-label="Arkadaşını davet et">
    <button type="button" class="gib__dismiss" id="gkInviteDismiss" aria-label="Kapat">×</button>
    <div class="gib__glow" aria-hidden="true"></div>
    <div class="gib__inner">
        <div class="gib__visual" aria-hidden="true">
            <span class="gib__icon">
                @include('partials.theme-icon', ['icon' => 'heart'])
            </span>
            <span class="gib__spark gib__spark--a"></span>
            <span class="gib__spark gib__spark--b"></span>
        </div>

        <div class="gib__copy">
            <p class="gib__eyebrow">Davet · Ödül</p>
            <h2 class="gib__title">Arkadaşını davet et, ödül kazan</h2>
            <p class="gib__lead">
                @if($isFemale)
                    WhatsApp veya link ile paylaş. Her başarılı davette profilin 24 saat öne çıkar.
                @else
                    WhatsApp veya link ile paylaş. Kayıt olan her arkadaşın için +{{ $rewardDays }} gün ödül hesabına tanımlanır.
                @endif
            </p>
            <ul class="gib__perks" aria-label="Ödüller">
                @if($isFemale)
                    <li><span class="gib__perk-dot" aria-hidden="true"></span>24 saat öne çıkma</li>
                    <li><span class="gib__perk-dot" aria-hidden="true"></span>Davetçi rozeti</li>
                @else
                    <li><span class="gib__perk-dot" aria-hidden="true"></span>+{{ $rewardDays }} gün premium</li>
                    <li><span class="gib__perk-dot" aria-hidden="true"></span>Deneme uzatma</li>
                @endif
            </ul>
        </div>

        <div class="gib__actions">
            <a
                href="{{ $whatsappUrl }}"
                class="gib__btn gib__btn--whatsapp"
                target="_blank"
                rel="noopener"
                data-gk-event="invite_share"
                data-gk-event-label="feed_banner_whatsapp"
                data-gk-invite-share="1"
            >
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.15 6.4 2.15 11.83c0 1.94.52 3.76 1.43 5.34L2 22l5.01-1.51a9.9 9.9 0 0 0 5.03 1.35h.01c5.46 0 9.89-4.4 9.89-9.83C21.94 6.4 17.5 2 12.04 2zm5.76 13.98c-.24.68-1.4 1.25-1.93 1.33-.5.07-1.13.1-1.82-.11-.42-.13-.96-.31-1.65-.61-2.9-1.25-4.79-4.18-4.93-4.37-.14-.19-1.15-1.53-1.15-2.92 0-1.39.73-2.07.99-2.35.26-.28.57-.35.76-.35h.54c.17 0 .4-.06.63.48.24.56.81 1.94.88 2.08.07.14.12.3.02.48-.1.19-.14.3-.28.46-.14.16-.29.35-.41.47-.14.14-.28.29-.12.56.16.28.7 1.15 1.5 1.86 1.03.91 1.9 1.19 2.17 1.33.27.14.43.12.59-.07.16-.19.68-.79.86-1.06.18-.28.36-.23.61-.14.24.1 1.54.73 1.8.86.27.14.44.2.51.31.07.12.07.67-.17 1.35z"/></svg>
                WhatsApp ile davet
            </a>
            <button
                type="button"
                class="gib__btn gib__btn--ghost"
                id="gkInviteNativeShare"
                data-share-url="{{ $inviteUrl }}"
                data-share-text="{{ $shareText }}"
                data-gk-event="invite_share"
                data-gk-event-label="feed_banner_share"
                data-gk-invite-share="1"
            >
                Paylaş / kopyala
            </button>
            <a
                href="{{ route('referral') }}"
                class="gib__btn gib__btn--ghost"
                data-gk-event="invite_share"
                data-gk-event-label="feed_banner"
                data-gk-invite-share="1"
            >
                Linki al
            </a>
        </div>
    </div>
</section>
<script>
(function () {
    var KEY = 'gk_invite_banner_dismissed';
    var banner = document.getElementById('gkInviteBanner');
    if (!banner) return;
    try {
        if (localStorage.getItem(KEY) === '1') {
            banner.remove();
            return;
        }
    } catch (e) {}

    function markShared() {
        try {
            document.cookie = 'gk_invite_shared=1;path=/;max-age=' + (60 * 60 * 24 * 30) + ';SameSite=Lax';
        } catch (e) {}
    }

    banner.querySelectorAll('[data-gk-invite-share]').forEach(function (el) {
        el.addEventListener('click', markShared);
    });

    var dismiss = document.getElementById('gkInviteDismiss');
    if (dismiss) {
        dismiss.addEventListener('click', function () {
            try { localStorage.setItem(KEY, '1'); } catch (e) {}
            banner.remove();
        });
    }

    var shareBtn = document.getElementById('gkInviteNativeShare');
    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            markShared();
            var url = shareBtn.getAttribute('data-share-url') || '';
            var text = (shareBtn.getAttribute('data-share-text') || '').trim();
            var payload = { title: 'Gönül Köprüsü', text: text, url: url };
            if (navigator.share) {
                navigator.share(payload).catch(function () {});
                return;
            }
            if (navigator.clipboard && url) {
                navigator.clipboard.writeText((text ? text + ' ' : '') + url).then(function () {
                    shareBtn.textContent = 'Kopyalandı';
                }).catch(function () {});
            }
        });
    }
})();
</script>
@endif
