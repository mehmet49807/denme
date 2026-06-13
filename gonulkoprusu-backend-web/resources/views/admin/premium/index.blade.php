@extends('layouts.admin')
@section('title', 'Premium Takibi')

@section('content')
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:18px; margin-bottom:22px;">
    @foreach (['pro'=>'Pro','gold'=>'Gold','platinum'=>'Platinum'] as $key=>$label)
        <div class="gk-card">
            <div style="color:var(--gk-text-soft);font-size:.85rem;">{{ $label }} Paketi</div>
            <div style="font-size:2rem;font-weight:700;">{{ $distribution[$key]->total ?? 0 }}</div>
            <div class="gk-location-box" style="margin-top:8px;">
                Gelir <span class="gk-sep">·</span> {{ number_format($distribution[$key]->revenue ?? 0, 2) }} TL
            </div>
        </div>
    @endforeach
    <div class="gk-card" style="background:linear-gradient(135deg,var(--gk-lavender),var(--gk-rose)); color:#fff;">
        <div style="font-size:.85rem;opacity:.9;">Toplam Gelir</div>
        <div style="font-size:2rem;font-weight:700;">{{ number_format($totalRevenue, 2) }} TL</div>
    </div>
</div>

<div class="gk-card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:var(--gk-cream-2); text-align:left;">
                <th style="padding:12px;">Kullanıcı</th>
                <th>Paket</th>
                <th>Tutar</th>
                <th>Bitiş</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($active as $sub)
            <tr style="border-top:1px solid var(--gk-beige);">
                <td style="padding:12px;">{{ $sub->user->username ?? '—' }}</td>
                <td><span class="gk-badge gk-badge--premium">{{ ucfirst($sub->package_type) }}</span></td>
                <td>{{ number_format($sub->price, 2) }} TL</td>
                <td>{{ $sub->expires_at?->format('d.m.Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div style="margin-top:16px;">{{ $active->links() }}</div>
@endsection
