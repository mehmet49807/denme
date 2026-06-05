@extends('layouts.admin')
@section('title', 'Mesaj Denetimi')

@section('content')
<form method="GET" class="gk-row" style="margin-bottom:18px; max-width:480px;">
    <input class="gk-input" name="q" value="{{ request('q') }}" placeholder="Mesaj içeriğinde ara">
    <button class="gk-btn">Ara</button>
</form>

<div class="gk-card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:var(--gk-cream-2); text-align:left;">
                <th style="padding:12px;">Gönderen</th>
                <th>Alıcı</th>
                <th>Mesaj</th>
                <th>Tarih</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($messages as $m)
            <tr style="border-top:1px solid var(--gk-beige);">
                <td style="padding:12px;">{{ $m->sender->username ?? '—' }}</td>
                <td>{{ $m->receiver->username ?? '—' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($m->message_text, 90) }}</td>
                <td>{{ $m->created_at?->format('d.m.Y H:i') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div style="margin-top:16px;">{{ $messages->links() }}</div>
@endsection
