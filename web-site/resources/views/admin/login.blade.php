<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi — Gönül Köprüsü</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.min.css') }}">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #1a1a2e;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            background-color: #ffffff;
            color: #333333;
            width: 100%;
            max-width: 400px;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .login-brand {
            text-align: center;
            margin-bottom: 24px;
        }

        .login-brand .logo-icon {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 8px;
        }

        .login-brand h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #7C3AED;
        }

        .login-brand p {
            font-size: 0.875rem;
            color: #666666;
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            color: #333333;
        }

        .form-control {
            width: 100%;
            padding: 11px 14px;
            font-size: 0.9rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background-color: #f9fafb;
            color: #111827;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #7C3AED;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            color: #666666;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #7C3AED;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #6D28D9;
        }

        .alert-error {
            background-color: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.875rem;
        }

        .alert-error ul {
            padding-left: 18px;
            margin: 0;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-brand">
            <span class="logo-icon">🌉</span>
            <h1>Gönül Köprüsü</h1>
            <p>Yönetici Paneli Girişi</p>
        </div>

        @if($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-error">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">E-posta veya Kullanıcı Adı</label>
                <input type="text" name="login" class="form-control" placeholder="admin@gonulkoprusu.com" value="{{ old('login') }}" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Şifre</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="form-check">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" style="cursor: pointer;">Beni Hatırla</label>
            </div>

            <button type="submit" class="btn-submit">
                Giriş Yap
            </button>
        </form>
    </div>

</body>
</html>
