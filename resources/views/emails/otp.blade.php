<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Your Kids Avon verification code</title>
</head>
<body style="margin:0;padding:0;background-color:#f6f3f2;">
    {{-- Preview line shown in the inbox list --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">Your code is {{ $code }}. It works for {{ $minutes }} minutes.</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f6f3f2;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:480px;background-color:#ffffff;border-radius:16px;">
                    <tr>
                        <td style="padding:28px 32px 8px 32px;font-family:Arial,Helvetica,sans-serif;">
                            <p style="margin:0;font-size:20px;font-weight:bold;color:#bc0100;">Kids Avon</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 32px 0 32px;font-family:Arial,Helvetica,sans-serif;font-size:16px;line-height:24px;color:#1c1b1b;">
                            <p style="margin:0 0 12px 0;">{{ $greeting }}</p>
                            <p style="margin:0 0 20px 0;">Use this code to {{ $action }}:</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:0 32px;">
                            <p style="margin:0;padding:16px 0;background-color:#fff0ed;border-radius:12px;font-family:'Courier New',Courier,monospace;font-size:34px;font-weight:bold;letter-spacing:8px;color:#1c1b1b;">{{ $code }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px 28px 32px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:22px;color:#5d3f3a;">
                            <p style="margin:0 0 12px 0;">The code works for {{ $minutes }} minutes and can be used once.</p>
                            <p style="margin:0 0 12px 0;">If you did not ask for this code, you can ignore this email. Nobody can use your account without the code.</p>
                            <p style="margin:0;">Need help? Reply to this email or write to {{ $supportEmail }}.</p>
                        </td>
                    </tr>
                </table>
                <p style="margin:16px 0 0 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:18px;color:#8f706a;">Kids Avon – Avon Cycles</p>
            </td>
        </tr>
    </table>
</body>
</html>
