@extends('layouts.admin')
@section('title', 'Şikayet Paneli')

@section('content')
<form method="GET" style="margin-bottom:18px;">
    <select class="gk-select" name="status" onchange="this.form.submit()" style="max-width:220px;">
        <option value="">Tüm Durumlar</option>
        @foreach (['pending'=>'Bekleyen','reviewed'=>'İncelendi','resolved'=>'Çözüldü','dismissed'=>'Reddedildi'] as $k=>$v)
            <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
        @endforeach
    </select>
</form>

<div class="gk-card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:var(--gk-cream-2); text-align:left;">
                <th style="padding:12px;">Şikayet Eden</th>
                <th>Şikayet Edilen</th>
                <th>Sebep</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($reports as $r)
            <tr style="border-top:1px solid var(--gk-beige);">
                <td style="padding:12px;">{{ $r->reporter->username ?? '—' }}</td>
                <td>{{ $r->reported->username ?? '—' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($r->reason, 70) }}</td>
                <td><span class="gk-badge gk-badge--{{ $r->status==='pending'?'pending':'active' }}">{{ $r->status }}</span></td>
                <td>
                    <form method="POST" action="{{ route('admin.reports.update', $r) }}" style="display:flex; gap:6px;">
                        @csrf @method('PUT')
                        <select class="gk-select" name="status" style="padding:6px;">
                            @foreach (['pending','reviewed','resolved','dismissed'] as $s)
                                <option value="{{ $s }}" @selected($r->status===$s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        <button class="gk-btn">Güncelle</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div style="margin-top:16px;">{{ $reports->links() }}</div>
@endsection
