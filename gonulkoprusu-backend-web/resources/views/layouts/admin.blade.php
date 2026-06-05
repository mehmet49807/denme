<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gonul Koprusu Admin</title>
    <link rel="stylesheet" href="/resources/css/gonulkoprusu.css">
</head>
<body>
    <div class="admin-shell">
        <aside class="admin-menu" aria-label="Admin right side navigation">
            <div class="logo-mark">Gonul Koprusu</div>
            <nav>
                <a href="{{ route('admin.users') }}">User Management</a>
                <a href="{{ route('admin.messages') }}">Message Auditor</a>
                <a href="{{ route('admin.reports') }}">Complaints/Reports Dashboard</a>
                <a href="{{ route('admin.premium') }}">Premium Tracker</a>
                <a href="{{ route('admin.broadcasts') }}">Admin Broadcast System</a>
            </nav>
        </aside>
        <main class="admin-content">
            @yield('content')
        </main>
    </div>
</body>
</html>
