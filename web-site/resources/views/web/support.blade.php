@extends('layouts.content-page')

@section('title', 'Destek — Gönül Köprüsü')
@section('page-eyebrow', 'Yardım')
@section('page-title', 'Destek formu')
@section('page-lead', 'Hesap, güvenlik, premium paket talebi ve teknik konularda bize yazın.')

@section('page-content')
    @if(session('success'))
        <p class="flash-success">{{ session('success') }}</p>
    @endif

    <p>
        E-posta: <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
        · WhatsApp destek hattı yok; yanıtlar e-posta ile gelir.
    </p>

    <form method="POST" action="{{ route('support.store') }}" class="support-form">
        @csrf
        <div class="form-group">
            <label for="name">Adınız</label>
            <input id="name" name="name" type="text" value="{{ old('name', auth()->user()->first_name ?? auth()->user()->username ?? '') }}" required maxlength="120">
            @error('name') <small class="form-error">{{ $message }}</small> @enderror
        </div>
        <div class="form-group">
            <label for="email">E-posta</label>
            <input id="email" name="email" type="email" value="{{ old('email', auth()->user()->email ?? '') }}" required maxlength="190">
            @error('email') <small class="form-error">{{ $message }}</small> @enderror
        </div>
        <div class="form-group">
            <label for="subject">Konu</label>
            <input id="subject" name="subject" type="text" value="{{ old('subject', $subject !== '' ? $subject : ($package !== '' ? 'Premium paket talebi: '.$package : '')) }}" required maxlength="160">
            @error('subject') <small class="form-error">{{ $message }}</small> @enderror
        </div>
        <div class="form-group">
            <label for="package">Premium paket (isteğe bağlı)</label>
            <select id="package" name="package">
                <option value="">Seçilmedi</option>
                @foreach(['pro' => 'Pro', 'gold' => 'Gold', 'platinum' => 'Platinum'] as $key => $label)
                    <option value="{{ $key }}" @selected(old('package', $package) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="message">Mesajınız</label>
            <textarea id="message" name="message" rows="6" required maxlength="4000">{{ old('message') }}</textarea>
            @error('message') <small class="form-error">{{ $message }}</small> @enderror
        </div>
        <button type="submit" class="btn btn-primary">Gönder</button>
    </form>
@endsection
