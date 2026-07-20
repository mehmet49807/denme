@extends('layouts.admin')

@section('title', 'Otomatik Kurallar')
@section('lead', 'Mesaj ve içerik güvenlik filtrelerini yönetin.')

@section('content')
<div class="admin-panel admin-panel--glass form-card">
    <form method="POST" action="{{ route('admin.auto-rules.update') }}">
        @csrf
        <h3 class="admin-panel-title">Kategori filtreleri</h3>
        <div class="admin-rules-list">
            @foreach($categories as $key => $label)
                <label class="admin-rule-toggle">
                    <input type="checkbox" name="{{ $key }}" value="1" @checked($enabled[$key] ?? true)>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <div class="form-group" style="margin-top:1.25rem">
            <label for="custom_patterns">Özel desenler (satır başına bir regex parçası)</label>
            <textarea id="custom_patterns" name="custom_patterns" rows="6" placeholder="örnek: dolandır&#10;papara">{{ old('custom_patterns', $customPatterns) }}</textarea>
            <small class="admin-ops-meta">Boş satırlar ve # ile başlayan satırlar yok sayılır.</small>
        </div>

        <button type="submit" class="btn btn-primary">Kaydet</button>
    </form>
</div>
@endsection
