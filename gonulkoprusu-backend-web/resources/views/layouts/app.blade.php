<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Gonul Koprusu' }}</title>
    <link rel="stylesheet" href="/resources/css/gonulkoprusu.css">
</head>
<body>
    <main class="app-shell">
        {{ $slot ?? '' }}
        @yield('content')
    </main>
</body>
</html>
