@php
    $returnTo = $returnTo ?? 'maintenance';
    $compact = $compact ?? false;
@endphp

<div class="admin-cache-clear-actions {{ $compact ? 'admin-cache-clear-actions--compact' : '' }}">
    <form method="POST" action="{{ route('admin.maintenance.clear-cache') }}" class="admin-cache-clear-form">
        @csrf
        <input type="hidden" name="target" value="all">
        <input type="hidden" name="return_to" value="{{ $returnTo }}">
        <button
            type="submit"
            class="btn btn-primary"
            onclick="return confirm('Web ve Admin önbelleği temizlensin mi?')"
        >
            Web + Admin Temizle
        </button>
    </form>

    <form method="POST" action="{{ route('admin.maintenance.clear-cache') }}" class="admin-cache-clear-form">
        @csrf
        <input type="hidden" name="target" value="web">
        <input type="hidden" name="return_to" value="{{ $returnTo }}">
        <button
            type="submit"
            class="btn btn-outline"
            onclick="return confirm('Web sitesi önbelleği temizlensin mi?')"
        >
            Sadece Web
        </button>
    </form>

    <form method="POST" action="{{ route('admin.maintenance.clear-cache') }}" class="admin-cache-clear-form">
        @csrf
        <input type="hidden" name="target" value="admin">
        <input type="hidden" name="return_to" value="{{ $returnTo }}">
        <button
            type="submit"
            class="btn btn-outline"
            onclick="return confirm('Admin panel önbelleği temizlensin mi?')"
        >
            Sadece Admin
        </button>
    </form>
</div>
