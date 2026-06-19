<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject }}</title>
</head>
<body style="margin: 0; padding: 0; background: #eef4ff; color: #0f172a; font-family: Arial, Helvetica, sans-serif;">
    <span style="display: none !important; visibility: hidden; opacity: 0; color: transparent; height: 0; width: 0; overflow: hidden; mso-hide: all;">
        {{ $preheader }}
    </span>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width: 100%; background: #eef4ff; padding: 32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; background: #ffffff; border: 1px solid #dbe7ff; border-radius: 18px; overflow: hidden; box-shadow: 0 18px 48px rgba(20, 64, 148, 0.12);">
                    <tr>
                        <td style="padding: 0; background: #0847d7;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding: 22px 28px;">
                                        <div style="font-size: 12px; line-height: 16px; color: #bfdbfe; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 700;">
                                            Жалобная книга
                                        </div>
                                        <div style="margin-top: 8px; font-size: 22px; line-height: 28px; color: #ffffff; font-weight: 800;">
                                            {{ $appName }}
                                        </div>
                                    </td>
                                    <td align="right" style="padding: 22px 28px;">
                                        <div style="display: inline-block; padding: 9px 12px; background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.22); border-radius: 999px; color: #ffffff; font-size: 12px; line-height: 16px; font-weight: 700;">
                                            Безопасный вход
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 34px 32px 12px;">
                            <h1 style="margin: 0; color: #082f80; font-size: 26px; line-height: 34px; font-weight: 800;">
                                {{ $heading }}
                            </h1>
                            <p style="margin: 14px 0 0; color: #334155; font-size: 16px; line-height: 24px;">
                                {{ $intro }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 18px 32px 8px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #f8fbff; border: 1px solid #c7d9ff; border-radius: 16px;">
                                <tr>
                                    <td align="center" style="padding: 26px 18px;">
                                        <div style="color: #64748b; font-size: 13px; line-height: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;">
                                            Ваш код подтверждения
                                        </div>
                                        <div style="margin-top: 14px; color: #073fb8; font-family: 'Courier New', Courier, monospace; font-size: 38px; line-height: 46px; font-weight: 800; letter-spacing: 9px;">
                                            {{ $code }}
                                        </div>
                                        <div style="margin-top: 14px; color: #475569; font-size: 14px; line-height: 20px;">
                                            Код действует {{ $expiresInMinutes }} минут.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 18px 32px 28px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 14px;">
                                <tr>
                                    <td style="padding: 16px 18px;">
                                        <div style="color: #9a3412; font-size: 14px; line-height: 21px; font-weight: 700;">
                                            {{ $warning }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 20px 32px 28px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; color: #64748b; font-size: 13px; line-height: 20px;">
                                Это автоматическое письмо. Отвечать на него не нужно.
                            </p>
                            <p style="margin: 8px 0 0; color: #94a3b8; font-size: 12px; line-height: 18px;">
                                Если код не вводить, действие не будет подтверждено.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
