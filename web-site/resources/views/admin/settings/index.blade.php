@extends('layouts.admin')

@section('title', 'Sistem Ayarları — Gönül Köprüsü')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Site ve Platform Ayarları</h2>
    </div>
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PATCH')

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div class="form-group">
                <label class="form-label">Site Başlığı / Adı</label>
                <input type="text" name="site_name" class="form-control" value="{{ $settings['site_name'] ?? 'Gönül Köprüsü' }}">
            </div>

            <div class="form-group">
                <label class="form-label">İletişim E-Posta Adresi</label>
                <input type="email" name="contact_email" class="form-control" value="{{ $settings['contact_email'] ?? 'destek@gonulkoprusu.com' }}">
            </div>

            <div class="form-group">
                <label class="form-label">Bakım Modu</label>
                <select name="maintenance_mode" class="form-select">
                    <option value="0" {{ ($settings['maintenance_mode'] ?? '0') == '0' ? 'selected' : '' }}>Kapalı (Site Aktif)</option>
                    <option value="1" {{ ($settings['maintenance_mode'] ?? '0') == '1' ? 'selected' : '' }}>Açık (Site Bakımda)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Yeni Üye Kaydı</label>
                <select name="registration_enabled" class="form-select">
                    <option value="1" {{ ($settings['registration_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Açık (Yeni Kayıtlara İzin Ver)</option>
                    <option value="0" {{ ($settings['registration_enabled'] ?? '1') == '0' ? 'selected' : '' }}>Kapalı (Kayıtlar Durduruldu)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Fotoğraf Onay Zorunluluğu</label>
                <select name="require_photo_approval" class="form-select">
                    <option value="1" {{ ($settings['require_photo_approval'] ?? '1') == '1' ? 'selected' : '' }}>Zorunlu (Yönetici Onayı Gerekli)</option>
                    <option value="0" {{ ($settings['require_photo_approval'] ?? '1') == '0' ? 'selected' : '' }}>Otomatik (Onay Gerekmez)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Ücretsiz Günlük Mesaj Limiti</label>
                <input type="number" name="free_message_limit" class="form-control" value="{{ $settings['free_message_limit'] ?? '10' }}">
            </div>

            <div class="form-group">
                <label class="form-label">Minimum Yaş Sınırı</label>
                <input type="number" name="min_age" class="form-control" value="{{ $settings['min_age'] ?? '18' }}">
            </div>
        </div>

        <div class="form-group" style="margin-top: 12px;">
            <label class="form-label">Site Açıklaması (Meta Description)</label>
            <textarea name="site_description" class="form-control" rows="3">{{ $settings['site_description'] ?? 'Türkiye'nin güvenilir evlilik ve arkadaşlık platformu.' }}</textarea>
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-color);">
            <button type="submit" class="btn btn-primary">💾 Ayarları Kaydet</button>
        </div>
    </form>
</div>
@endsection
