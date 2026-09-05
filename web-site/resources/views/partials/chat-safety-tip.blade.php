@if(!empty($isFirstMessage))
<div class="chat-safety-tip" role="note">
    <strong>Güvenli tanışma</strong>
    <p>Kişisel bilgilerini ve para taleplerini paylaşma. İlk buluşmayı kalabalık bir yerde planla.</p>
    <a href="{{ route('safe-meeting') }}" class="chat-safety-tip__link">İpuçlarını oku</a>
</div>
<style>
.chat-safety-tip{margin:0 0 .75rem;padding:.7rem .85rem;border-radius:12px;background:#fff7ed;border:1px solid rgba(245,158,11,.28);font-size:.82rem;color:#9a3412}
.chat-safety-tip p{margin:.2rem 0 .35rem}
.chat-safety-tip__link{font-weight:700;color:#c2410c;text-decoration:none}
</style>
@endif
