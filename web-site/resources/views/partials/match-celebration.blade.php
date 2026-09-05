@php
    $matchCelebration = session('match_celebration');
@endphp
@if(is_array($matchCelebration) && !empty($matchCelebration['username']))
<div class="match-celebration" id="matchCelebration" role="dialog" aria-modal="true" aria-labelledby="matchCelebrationTitle">
    <div class="match-celebration__card">
        <p class="match-celebration__emoji" aria-hidden="true">💞</p>
        <h2 id="matchCelebrationTitle">Karşılıklı beğeni!</h2>
        <p><strong>{{ $matchCelebration['username'] }}</strong> ile eşleştiniz. Hemen sohbet edebilirsiniz.</p>
        <div class="match-celebration__actions">
            @if(!empty($matchCelebration['message_url']))
                <a href="{{ $matchCelebration['message_url'] }}" class="btn btn-primary">Mesaj gönder</a>
            @endif
            <a href="{{ route('matches.index') }}" class="btn btn-outline">Eşleşmeler</a>
            <button type="button" class="btn btn-ghost" data-close-match-celebration>Kapat</button>
        </div>
    </div>
</div>
<style>
.match-celebration{position:fixed;inset:0;z-index:80;display:flex;align-items:center;justify-content:center;padding:1rem;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);}
.match-celebration__card{max-width:22rem;width:100%;text-align:center;background:#fff;border-radius:22px;padding:1.5rem 1.25rem;box-shadow:0 20px 50px rgba(15,23,42,.25);animation:matchPop .35s ease;}
.match-celebration__emoji{font-size:2.4rem;margin:0 0 .35rem;}
.match-celebration__actions{display:flex;flex-direction:column;gap:.5rem;margin-top:1rem;}
@keyframes matchPop{from{transform:scale(.9);opacity:0}to{transform:scale(1);opacity:1}}
</style>
<script>
document.querySelectorAll('[data-close-match-celebration]').forEach(function(btn){
  btn.addEventListener('click', function(){
    var el=document.getElementById('matchCelebration');
    if(el) el.remove();
  });
});
</script>
@endif
