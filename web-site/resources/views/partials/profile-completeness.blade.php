@php
    $completeness = $completeness ?? null;
@endphp
@if(is_array($completeness) && ($completeness['percent'] ?? 100) < 100)
<section class="profile-completeness" aria-labelledby="profile-completeness-title">
    <div class="profile-completeness__head">
        <strong id="profile-completeness-title">Profil tamamlanma</strong>
        <span class="profile-completeness__pct">%{{ (int) $completeness['percent'] }}</span>
    </div>
    <div class="profile-completeness__bar" role="progressbar" aria-valuenow="{{ (int) $completeness['percent'] }}" aria-valuemin="0" aria-valuemax="100">
        <span class="profile-completeness__fill" style="width: {{ (int) $completeness['percent'] }}%;"></span>
    </div>
    @if(!empty($completeness['missing']))
        <ul class="profile-completeness__list">
            @foreach(array_slice($completeness['missing'], 0, 4) as $item)
                <li>
                    <a href="{{ $item['href'] }}">{{ $item['label'] }} ekle</a>
                </li>
            @endforeach
        </ul>
    @endif
</section>
<style>
.profile-completeness{margin:0.75rem 0 1rem;padding:0.9rem 1rem;border-radius:16px;background:linear-gradient(135deg,rgba(124,58,237,.06),rgba(236,72,153,.05));border:1px solid rgba(124,58,237,.14);}
.profile-completeness__head{display:flex;justify-content:space-between;align-items:center;margin-bottom:.45rem;font-size:.9rem;}
.profile-completeness__pct{font-weight:800;color:#7c3aed;}
.profile-completeness__bar{height:8px;border-radius:999px;background:rgba(15,23,42,.08);overflow:hidden;}
.profile-completeness__fill{display:block;height:100%;border-radius:999px;background:linear-gradient(90deg,#7c3aed,#db2777);}
.profile-completeness__list{display:flex;flex-wrap:wrap;gap:.4rem .75rem;margin:.55rem 0 0;padding:0;list-style:none;}
.profile-completeness__list a{font-size:.8rem;font-weight:600;color:#6d28d9;text-decoration:none;}
</style>
@endif
