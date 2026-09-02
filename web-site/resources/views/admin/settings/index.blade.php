@extends('layouts.admin')

@section('title', 'Sistem Ayarları — Gönül Köprüsü')

@section('content')
@if(session('success'))
<div style="background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-weight: 600;">
    {{ session('success') }}
</div>
@endif

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
                <input type="email" name="support_email" class="form-control" value="{{ $settings['support_email'] ?? 'destek@gonulkoprusu.com' }}">
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
                <label class="form-label">Destek Telefonu</label>
                <input type="text" name="support_phone" class="form-control" value="{{ $settings['support_phone'] ?? '' }}">
            </div>

            <div class="form-group">
                <label class="form-label">Destek WhatsApp</label>
                <input type="text" name="support_whatsapp" class="form-control" value="{{ $settings['support_whatsapp'] ?? '' }}">
            </div>

            <div class="form-group">
                <label class="form-label">Destek Saatleri</label>
                <input type="text" name="support_hours" class="form-control" value="{{ $settings['support_hours'] ?? '7/24' }}">
            </div>

            <div class="form-group">
                <label class="form-label">Google Analytics ID</label>
                <input type="text" name="google_analytics_id" class="form-control" value="{{ $settings['google_analytics_id'] ?? '' }}">
            </div>

            <div class="form-group">
                <label class="form-label">Google Tag Manager ID</label>
                <input type="text" name="google_tag_manager_id" class="form-control" value="{{ $settings['google_tag_manager_id'] ?? '' }}">
            </div>
        </div>

        <div class="form-group" style="margin-top: 12px;">
            <label class="form-label">Site Açıklaması (Meta Description)</label>
            <textarea name="default_description" class="form-control" rows="3">{{ $settings['default_description'] ?? 'Gönül Köprüsü — Türkiye\'nin güvenli tanışma, sohbet ve evlilik sitesi.' }}</textarea>
        </div>

        <div class="form-group" style="margin-top: 12px;">
            <label class="form-label">Anahtar Kelimeler (Meta Keywords)</label>
            <textarea name="default_keywords" class="form-control" rows="2">{{ $settings['default_keywords'] ?? '' }}</textarea>
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 16px;">Sosya Medya Linkleri</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Instagram URL</label>
                    <input type="text" name="instagram_url" class="form-control" value="{{ $settings['instagram_url'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Facebook URL</label>
                    <input type="text" name="facebook_url" class="form-control" value="{{ $settings['facebook_url'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Twitter / X URL</label>
                    <input type="text" name="twitter_url" class="form-control" value="{{ $settings['twitter_url'] ?? '' }}">
                </div>
            </div>
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 16px;">Şirket Bilgileri</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Şirket Adı</label>
                    <input type="text" name="company_name" class="form-control" value="{{ $settings['company_name'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Vergi Dairesi</label>
                    <input type="text" name="company_tax_office" class="form-control" value="{{ $settings['company_tax_office'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Vergi Numarası</label>
                    <input type="text" name="company_tax_number" class="form-control" value="{{ $settings['company_tax_number'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">MERSİS</label>
                    <input type="text" name="company_mersis" class="form-control" value="{{ $settings['company_mersis'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Ticaret Sicil</label>
                    <input type="text" name="company_trade_registry" class="form-control" value="{{ $settings['company_trade_registry'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Şirket Adresi</label>
                    <textarea name="company_address" class="form-control" rows="2">{{ $settings['company_address'] ?? '' }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Şirket Telefonu</label>
                    <input type="text" name="company_phone" class="form-control" value="{{ $settings['company_phone'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Şirket E-Postası</label>
                    <input type="email" name="company_email" class="form-control" value="{{ $settings['company_email'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">KVKK İletişim Kişisi</label>
                    <input type="text" name="company_kvkk_contact" class="form-control" value="{{ $settings['company_kvkk_contact'] ?? '' }}">
                </div>
            </div>
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
            <button type="submit" class="btn btn-primary">💾 Ayarları Kaydet</button>
        </div>
    </form>
</div>
@endsection
