<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine ?? config('app.name', 'Gönül Köprüsü') }}</title>
</head>
<body style="margin:0;background:#f5f3ff;color:#1f2937;font-family:Arial,Helvetica,sans-serif;line-height:1.6;">
    <div style="max-width:640px;margin:0 auto;padding:24px 16px;">
        <div style="background:#ffffff;border:1px solid #e9d5ff;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(76,29,149,.08);">
            <div style="padding:22px 24px;background:linear-gradient(135deg,#7c3aed,#c026d3);color:#ffffff;">
                <div style="font-size:20px;font-weight:700;">Gönül Köprüsü</div>
                <div style="margin-top:4px;font-size:13px;opacity:.9;">Kalpleri birleştiren güvenli platform</div>
            </div>
            <div style="padding:28px 24px;">
                @if(!empty($greetingName))
                    <p style="margin:0 0 16px;">Merhaba {{ $greetingName }},</p>
                @endif
                <div style="font-size:15px;">{!! $bodyHtml !!}</div>
            </div>
            <div style="padding:18px 24px;border-top:1px solid #f3e8ff;color:#6b7280;font-size:12px;">
                Bu e-posta Gönül Köprüsü hesabınızla ilgili olarak gönderilmiştir. Bu iletiyi siz talep etmediyseniz güvenlik ekibimizle iletişime geçebilirsiniz.
            </div>
        </div>
    </div>
</body>
</html>
