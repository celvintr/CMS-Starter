<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación en dos pasos — {{ $settings->site_name }}</title>
    @if ($settings->favicon_path)
        <link rel="icon" href="{{ asset('storage/' . $settings->favicon_path) }}">
    @endif
    <style>
        :root { --brand: {{ $settings->primary_color ?: '#2563eb' }}; --ink: {{ $settings->secondary_color ?: '#0f172a' }}; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               background: #f1f5f9; font-family: ui-sans-serif, system-ui, "Segoe UI", Roboto, sans-serif; color: #334155; padding: 24px; }
        .card { background: #fff; width: 100%; max-width: 400px; border-radius: 18px; padding: 36px 32px;
                box-shadow: 0 10px 40px -12px rgba(15,23,42,.25); border: 1px solid #e2e8f0; }
        .icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center;
                background: color-mix(in srgb, var(--brand) 12%, #fff); color: var(--brand); margin-bottom: 18px; }
        h1 { font-size: 20px; margin: 0 0 6px; color: var(--ink); font-weight: 800; }
        p.sub { margin: 0 0 22px; font-size: 14px; color: #64748b; line-height: 1.5; }
        label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        input[name="code"] { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 12px; font-size: 20px;
                letter-spacing: 6px; text-align: center; outline: none; }
        input[name="code"]:focus { border-color: var(--brand); box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 25%, transparent); }
        .err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 13px; padding: 9px 12px; border-radius: 10px; margin-bottom: 16px; }
        button.submit { width: 100%; margin-top: 18px; padding: 12px; border: 0; border-radius: 999px; background: var(--brand);
                color: #fff; font-weight: 700; font-size: 15px; cursor: pointer; }
        button.submit:hover { opacity: .92; }
        .foot { margin-top: 20px; text-align: center; font-size: 13px; }
        .foot a, .foot button { color: #94a3b8; background: none; border: 0; cursor: pointer; font-size: 13px; text-decoration: underline; }
        .hint { margin-top: 14px; font-size: 12px; color: #94a3b8; text-align: center; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.7">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>
        <h1>Verificación en dos pasos</h1>
        <p class="sub">Abre tu app de autenticación (Google Authenticator, Authy…) e ingresa el código de 6 dígitos.</p>

        @if ($errors->any())
            <div class="err">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('two-factor.verify') }}">
            @csrf
            <label for="code">Código de verificación</label>
            <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus
                   placeholder="000000" maxlength="20">
            <button type="submit" class="submit">Verificar</button>
        </form>

        <p class="hint">¿Perdiste tu teléfono? Ingresa uno de tus <strong>códigos de recuperación</strong> en el mismo campo.</p>

        <div class="foot">
            <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
