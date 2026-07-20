@extends('layouts.admin')

@section('title', 'Personel Rolleri')
@section('lead', 'Admin, moderatör ve destek rollerini yönetin.')

@section('content')
@if($errors->any())
    <div class="admin-panel admin-panel--glass" style="border-color:rgba(239,68,68,.35);margin-bottom:1rem">
        <ul style="margin:0;padding-left:1.1rem;color:#b91c1c">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div class="admin-panel admin-panel--glass" style="border-color:rgba(16,185,129,.35);margin-bottom:1rem;color:#047857">
        {{ session('success') }}
    </div>
@endif

<div class="admin-panel admin-panel--glass form-card">
    <h3 class="admin-panel-title">Personel ekle</h3>
    <p class="admin-ops-meta" style="margin-bottom:0.85rem">Mevcut bir üyenin kullanıcı adını yazın; rolü admin / moderatör / destek yapılır.</p>
    <form method="POST" action="{{ route('admin.staff.promote') }}" class="admin-users-filter">
        @csrf
        <div class="admin-users-filter-field admin-users-filter-field--grow">
            <label for="staff-username">Kullanıcı adı</label>
            <input type="text" id="staff-username" name="username" required value="{{ old('username') }}" placeholder="mevcut üye kullanıcı adı" autocomplete="off">
        </div>
        <div class="admin-users-filter-field">
            <label for="staff-role">Rol</label>
            <select id="staff-role" name="role" class="admin-users-filter-select">
                <option value="moderator" @selected(old('role', 'moderator') === 'moderator')>Moderatör</option>
                <option value="support" @selected(old('role') === 'support')>Destek</option>
                <option value="admin" @selected(old('role') === 'admin')>Yönetici</option>
            </select>
        </div>
        <div class="admin-users-filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">Ekle</button>
        </div>
    </form>
</div>

<div class="admin-panel admin-panel--glass">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kullanıcı</th>
                    <th>E-posta</th>
                    <th>Rol</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staff as $member)
                    <tr>
                        <td><strong>{{ $member->username }}</strong></td>
                        <td>{{ $member->email }}</td>
                        <td>{{ $roleLabels[$member->role] ?? $member->role }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.staff.update', $member) }}" class="admin-inline-role-form">
                                @csrf
                                <select name="role">
                                    @foreach($roleLabels as $value => $label)
                                        <option value="{{ $value }}" @selected($member->role === $value)>{{ $label }}</option>
                                    @endforeach
                                    <option value="user">Üye (kaldır)</option>
                                </select>
                                <button type="submit" class="btn btn-outline btn-sm">Kaydet</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Henüz personel kaydı yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
