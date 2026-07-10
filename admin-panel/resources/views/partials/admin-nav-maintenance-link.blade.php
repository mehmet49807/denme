<a href="{{ route('admin.maintenance') }}" class="admin-sidebar-link admin-sidebar-link--violet {{ request()->routeIs('admin.maintenance') ? 'is-active' : '' }}">
    <span class="admin-sidebar-icon" aria-hidden="true">
        @include('partials.admin-icon', ['icon' => 'sparkles'])
    </span>
    <span>Önbellek</span>
</a>
