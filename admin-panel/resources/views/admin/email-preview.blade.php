@extends('layouts.admin')

@section('title', 'E-posta Önizleme')
@section('lead', 'Şablonun kullanıcıya nasıl görüneceğini kontrol edin.')

@section('content')
<div class="admin-panel admin-panel--glass">
    <div class="admin-panel-head">
        <h3 class="admin-panel-title">{{ $subject }}</h3>
        <a href="{{ route('admin.emails') }}" class="btn btn-outline btn-sm">Geri</a>
    </div>
    <div class="admin-email-preview" style="padding:1rem 0;">
        {!! $htmlBody !!}
    </div>
</div>
@endsection
