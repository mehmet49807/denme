@php
    $viewer = $viewer ?? auth()->user();
    $showInviteBanner = !empty($showInviteBanner);
@endphp
@if($showInviteBanner && $viewer)
<section class="growth-invite-banner" aria-label="Arkadaş davet et">
    <div class="growth-invite-banner__inner">
        <div>
            <h2>Arkadaşını davet et, ödül kazan</h2>
            <p>WhatsApp ile paylaş. Kayıt olduğunda hesabına ödül tanımlanır.</p>
        </div>
        <a href="{{ route('referral') }}" class="btn btn-primary btn-sm" data-gk-event="invite_share" data-gk-event-label="feed_banner">Davet et</a>
    </div>
</section>
@endif
