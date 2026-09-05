@php
    $prefs = $notificationPrefs ?? [
        'email_matches' => true,
        'email_likes' => true,
        'email_messages' => true,
        'email_marketing' => false,
    ];
@endphp
<section class="notification-prefs" aria-labelledby="notification-prefs-title">
    <h2 id="notification-prefs-title" style="font-size:1rem;margin:0 0 .5rem;">Bildirim tercihleri</h2>
    <p style="margin:0 0 .75rem;font-size:.88rem;color:#64748b;">Hangi e-posta bildirimlerini almak istediğini seç.</p>
    <form method="POST" action="{{ route('profile.notification-prefs') }}" class="notification-prefs__form">
        @csrf
        <label><input type="checkbox" name="email_matches" value="1" @checked(!empty($prefs['email_matches']))> Eşleşme bildirimleri</label>
        <label><input type="checkbox" name="email_likes" value="1" @checked(!empty($prefs['email_likes']))> Beğeni bildirimleri</label>
        <label><input type="checkbox" name="email_messages" value="1" @checked(!empty($prefs['email_messages']))> Mesaj bildirimleri</label>
        <label><input type="checkbox" name="email_marketing" value="1" @checked(!empty($prefs['email_marketing']))> Duyuru ve ipuçları</label>
        <button type="submit" class="btn btn-primary btn-sm" style="margin-top:.5rem;">Kaydet</button>
    </form>
</section>
<style>
.notification-prefs{margin:1rem 0;padding:1rem;border-radius:16px;border:1px solid rgba(15,23,42,.08);background:#fff;}
.notification-prefs__form{display:flex;flex-direction:column;gap:.45rem;font-size:.9rem;}
</style>
