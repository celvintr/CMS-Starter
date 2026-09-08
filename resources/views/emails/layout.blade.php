@php
    $brand = $settings->primary_color ?: '#2563eb';
    $ink = $settings->secondary_color ?: '#0f172a';
    $name = $settings->site_name ?: 'Sitio';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#334155;-webkit-text-size-adjust:100%">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 12px">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0">
                    {{-- Encabezado con la marca --}}
                    <tr>
                        <td align="left" style="background:{{ $brand }};padding:22px 28px">
                            @if ($settings->logo_path)
                                <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="{{ $name }}" height="34" style="height:34px;display:block;border:0">
                            @else
                                <span style="color:#ffffff;font-size:20px;font-weight:bold;letter-spacing:.3px">{{ $name }}</span>
                            @endif
                        </td>
                    </tr>

                    @if ($heading)
                        <tr>
                            <td style="padding:28px 28px 0 28px">
                                <h1 style="margin:0;font-size:20px;line-height:1.3;color:{{ $ink }}">{{ $heading }}</h1>
                            </td>
                        </tr>
                    @endif

                    {{-- Cuerpo --}}
                    <tr>
                        <td style="padding:16px 28px 28px 28px;font-size:15px;line-height:1.6;color:#334155">
                            {!! $bodyHtml !!}
                        </td>
                    </tr>

                    {{-- Pie --}}
                    <tr>
                        <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 28px;font-size:12px;line-height:1.6;color:#94a3b8">
                            @if ($footerHtml)
                                <div style="margin-bottom:10px">{!! $footerHtml !!}</div>
                            @endif
                            <div style="font-weight:bold;color:#64748b">{{ $name }}</div>
                            @if ($settings->email)<div>{{ $settings->email }}</div>@endif
                            @if ($settings->phone)<div>{{ $settings->phone }}</div>@endif
                            <div style="margin-top:8px">© {{ date('Y') }} {{ $name }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
