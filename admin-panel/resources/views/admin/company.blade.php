@extends('layouts.admin')

@section('title', 'Şirket Bilgileri')
@section('lead', 'Ticari ünvan, vergi, MERSIS ve iletişim bilgileri. Hakkımızda, KVKK ve Gizlilik sayfalarında otomatik görüntülenir.')

@section('content')
<div class="admin-company-page">
    @if(session('success'))
        <div class="alert alert-success" style="background:#e8f5e9;border:1px solid #81c784;color:#2e7d32;padding:1rem 1.25rem;border-radius:.5rem;margin-bottom:1.25rem;font-size:.9rem;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="background:#ffebee;border:1px solid #e57373;color:#c62828;padding:1rem 1.25rem;border-radius:.5rem;margin-bottom:1.25rem;font-size:.9rem;">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <div class="admin-panel admin-panel--glass" style="margin:1.25rem 0;padding:1.5rem;">
        <h3 style="margin:0 0 .5rem;font-size:1.1rem;color:#333;">📋 KVKK Uyum Rehberi</h3>
        <p style="margin:0 0 .75rem;font-size:.85rem;color:#666;line-height:1.6;">
            Aşağıdaki bilgiler 6698 sayılı KVKK ve 6502 sayılı Tüketicinin Korunması Kanunu kapsamında gereklidir.
            Doldurduğunuzda Hakkımızda, KVKK Aydınlatma Metni ve Gizlilik Sözleşmesi sayfalarında otomatik olarak görüntülenir.
        </p>
        <ul style="margin:0;padding-left:1.25rem;font-size:.85rem;color:#555;line-height:1.8;">
            <li><strong>Ticari Ünvan:</strong> Şirketin resmi adı (ör. "Gönül Köprüsü Tanışma Hizmetleri Ltd. Şti.")</li>
            <li><strong>Vergi Dairesi & VKN:</strong> Ticaret sicil kayıtlı vergi bilgileri</li>
            <li><strong>MERSIS:</strong> Muhasebe ve Kaynak Sistemi numarası (26 haneli)</li>
            <li><strong>Ticaret Sicil No:</strong> Kayıtlı olduğu ticaret sicil numarası</li>
            <li><strong>İş Adresi:</strong> Şirketin fiziksel adresi</li>
            <li><strong>KVKK İletişim:</strong> Kişisel veri talepleri için sorumlu kişi/e-posta</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('admin.company.update') }}" class="admin-company-form">
        @csrf

        <div class="admin-panel admin-panel--glass" style="margin:1.25rem 0;padding:1.5rem;">
            <h3 style="margin:0 0 1rem;font-size:1.1rem;color:#333;">
                <span style="margin-right:.5rem;">🏢</span> Ticari Bilgiler
            </h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;">
                <div class="form-group">
                    <label for="company_name" style="display:block;font-size:.8rem;font-weight:600;color:#555;margin-bottom:.35rem;">Ticari Ünvan</label>
                    <input type="text" id="company_name" name="company_name"
                        value="{{ old('company_name', $settings['company_name'] ?? '') }}"
                        placeholder="Gönül Köprüsü Tanışma Hizmetleri Ltd. Şti."
                        maxlength="255"
                        style="width:100%;padding:.6rem .75rem;border:1px solid #ddd;border-radius:.4rem;font-size:.9rem;">
                </div>
                <div class="form-group">
                    <label for="company_tax_office" style="display:block;font-size:.8rem;font-weight:600;color:#555;margin-bottom:.35rem;">Vergi Dairesi</label>
                    <input type="text" id="company_tax_office" name="company_tax_office"
                        value="{{ old('company_tax_office', $settings['company_tax_office'] ?? '') }}"
                        placeholder="Örn. Çankaya Vergi Dairesi"
                        maxlength="255"
                        style="width:100%;padding:.6rem .75rem;border:1px solid #ddd;border-radius:.4rem;font-size:.9rem;">
                </div>
                <div class="form-group">
                    <label for="company_tax_number" style="display:block;font-size:.8rem;font-weight:600;color:#555;margin-bottom:.35rem;">Vergi Kimlik No (VKN)</label>
                    <input type="text" id="company_tax_number" name="company_tax_number"
                        value="{{ old('company_tax_number', $settings['company_tax_number'] ?? '') }}"
                        placeholder="1234567890"
                        maxlength="50"
                        style="width:100%;padding:.6rem .75rem;border:1px solid #ddd;border-radius:.4rem;font-size:.9rem;">
                </div>
                <div class="form-group">
                    <label for="company_mersis" style="display:block;font-size:.8rem;font-weight:600;color:#555;margin-bottom:.35rem;">MERSIS Numarası</label>
                    <input type="text" id="company_mersis" name="company_mersis"
                        value="{{ old('company_mersis', $settings['company_mersis'] ?? '') }}"
                        placeholder="0000000000000000000 (26 haneli)"
                        maxlength="50"
                        style="width:100%;padding:.6rem .75rem;border:1px solid #ddd;border-radius:.4rem;font-size:.9rem;">
                </div>
                <div class="form-group">
                    <label for="company_trade_registry" style="display:block;font-size:.8rem;font-weight:600;color:#555;margin-bottom:.35rem;">Ticaret Sicil No</label>
                    <input type="text" id="company_trade_registry" name="company_trade_registry"
                        value="{{ old('company_trade_registry', $settings['company_trade_registry'] ?? '') }}"
                        placeholder="123456-İstanbul"
                        maxlength="100"
                        style="width:100%;padding:.6rem .75rem;border:1px solid #ddd;border-radius:.4rem;font-size:.9rem;">
                </div>
                <div class="form-group">
                    <label for="company_representative" style="display:block;font-size:.8rem;font-weight:600;color:#555;margin-bottom:.35rem;">Yetkili Kişi</label>
                    <input type="text" id="company_representative" name="company_representative"
                        value="{{ old('company_representative', $settings['company_representative'] ?? '') }}"
                        placeholder="Ad Soyad"
                        maxlength="255"
                        style="width:100%;padding:.6rem .75rem;border:1px solid #ddd;border-radius:.4rem;font-size:.9rem;">
                </div>
            </div>
        </div>

        <div class="admin-panel admin-panel--glass" style="margin:1.25rem 0;padding:1.5rem;">
            <h3 style="margin:0 0 1rem;font-size:1.1rem;color:#333;">
                <span style="margin-right:.5rem;">📞</span> İletişim Bilgileri
            </h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.25rem;">
                <div class="form-group">
                    <label for="company_address" style="display:block;font-size:.8rem;font-weight:600;color:#555;margin-bottom:.35rem;">İş Adresi</label>
                    <textarea id="company_address" name="company_address" rows="3"
                        placeholder="Mahalle, Sokak, No, İlçe, İl"
                        maxlength="500"
                        style="width:100%;padding:.6rem .75rem;border:1px solid #ddd;border-radius:.4rem;font-size:.9rem;resize:vertical;">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="company_phone" style="display:block;font-size:.8rem;font-weight:600;color:#555;margin-bottom:.35rem;">Telefon</label>
                    <input type="text" id="company_phone" name="company_phone"
                        value="{{ old('company_phone', $settings['company_phone'] ?? '') }}"
                        placeholder="+90 850 ..."
                        maxlength="30"
                        style="width:100%;padding:.6rem .75rem;border:1px solid #ddd;border-radius:.4rem;font-size:.9rem;">
                </div>
                <div class="form-group">
                    <label for="company_email" style="display:block;font-size:.8rem;font-weight:600;color:#555;margin-bottom:.35rem;">Şirket E-postası</label>
                    <input type="email" id="company_email" name="company_email"
                        value="{{ old('company_email', $settings['company_email'] ?? '') }}"
                        placeholder="info@gonulkoprusu.com"
                        maxlength="255"
                        style="width:100%;padding:.6rem .75rem;border:1px solid #ddd;border-radius:.4rem;font-size:.9rem;">
                </div>
                <div class="form-group">
                    <label for="company_kvkk_contact" style="display:block;font-size:.8rem;font-weight:600;color:#555;margin-bottom:.35rem;">KVKK Veri Sorumlusu İletişim</label>
                    <input type="text" id="company_kvkk_contact" name="company_kvkk_contact"
                        value="{{ old('company_kvkk_contact', $settings['company_kvkk_contact'] ?? '') }}"
                        placeholder="kvkk@gonulkoprusu.com veya yetkili adı"
                        maxlength="255"
                        style="width:100%;padding:.6rem .75rem;border:1px solid #ddd;border-radius:.4rem;font-size:.9rem;">
                </div>
            </div>
        </div>

        @if(!empty($settings['company_name']))
        <div class="admin-panel admin-panel--glass" style="margin:1.25rem 0;padding:1.5rem;background:#f8fbf8;">
            <h3 style="margin:0 0 .75rem;font-size:1.1rem;color:#333;">👁️ Önizleme (Hakkımızda sayfasında nasıl görünür)</h3>
            <div style="font-size:.85rem;color:#555;line-height:1.8;padding:1rem;background:#fff;border:1px solid #e0e0e0;border-radius:.4rem;">
                <p style="margin:0 0 .5rem;"><strong>{{ $settings['company_name'] ?? '' }}</strong></p>
                @if(!empty($settings['company_address']))
                <p style="margin:0 0 .25rem;">{{ $settings['company_address'] ?? '' }}</p>
                @endif
                <p style="margin:0 0 .25rem;">
                    @if(!empty($settings['company_tax_office']))Vergi Dairesi: {{ $settings['company_tax_office'] }}@endif
                    @if(!empty($settings['company_tax_number'])) · VKN: {{ $settings['company_tax_number'] }}@endif
                </p>
                @if(!empty($settings['company_mersis']))
                <p style="margin:0 0 .25rem;">MERSIS: {{ $settings['company_mersis'] }}</p>
                @endif
                @if(!empty($settings['company_trade_registry']))
                <p style="margin:0 0 .25rem;">Ticaret Sicil No: {{ $settings['company_trade_registry'] }}</p>
                @endif
                @if(!empty($settings['company_phone']) || !empty($settings['company_email']))
                <p style="margin:0;">
                    @if(!empty($settings['company_phone']))Tel: {{ $settings['company_phone'] }}@endif
                    @if(!empty($settings['company_phone']) && !empty($settings['company_email'])) · @endif
                    @if(!empty($settings['company_email']))E-posta: {{ $settings['company_email'] }}@endif
                </p>
                @endif
            </div>
        </div>
        @endif

        <div style="display:flex;gap:.75rem;align-items:center;margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#ff6a88,#ff9a8b);color:#fff;border:none;padding:.75rem 2rem;border-radius:.5rem;font-size:.9rem;font-weight:600;cursor:pointer;">
                💾 Kaydet
            </button>
            <a href="{{ route('admin.dashboard') }}" style="color:#888;font-size:.85rem;text-decoration:none;">İptal</a>
        </div>
    </form>
</div>
@endsection
