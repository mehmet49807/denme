@extends('layouts.admin')

@section('title', 'Personel Rolleri')
@section('lead', 'Admin, moderatör ve destek rollerini yönetin.')

@section('content')
<div class="admin-panel admin-panel--glass form-card">
    <h3 class="admin-panel-title">Personel ekle</h3>
    <form method="POST" action="{{ route('admin.staff.promote') }}" class="admin-users-filter">
        @csrf
        <div class="admin-users-filter-field admin-users-filter-field--grow">
            <label for="staff-username">Kullanıcı adı</label>
            <input type="text" id="staff-username" name="username" required placeholder="mevcut üye kullanıcı adı">
        </div>
        <div class="admin-users-filter-field">
            <label for="staff-role">Rol</label>
            <select id="staff-role" name="role" class="admin-users-filter-select">
                <option value="moderator">Moderatör</option>
                <option value="support">Destek</option>
                <option value="admin">Yönetici</option>
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
                @foreach($staff as $member)
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
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
