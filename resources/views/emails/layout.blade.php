<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <meta name="supported-color-schemes" content="dark light">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Saturn')</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        a { color: #C6A15B; }
        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; }
            .px { padding-left: 24px !important; padding-right: 24px !important; }
            .hero-amount { font-size: 38px !important; }
            .btn-cell, .btn-cell a { display: block !important; width: 100% !important; text-align: center !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background-color:#0B1220;" bgcolor="#0B1220">

    {{-- Preheader: the preview line shown next to the subject in inboxes. --}}
    <div style="display:none; font-size:1px; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden; mso-hide:all;">
        @yield('preheader')&nbsp;&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0B1220" style="background-color:#0B1220;">
        <tr>
            <td align="center" style="padding:40px 16px 48px;">

                <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px;">

                    {{-- Brand header --}}
                    <tr>
                        <td align="center" style="padding:0 0 28px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td valign="middle" style="padding-right:12px;">
                                        {{-- asset() builds an absolute URL from APP_URL, which must be publicly reachable for the logo to load. --}}
                                        <img src="{{ asset('images/v5yuA.png') }}" alt="" height="34" style="display:block; height:34px; width:auto;">
                                    </td>
                                    <td valign="middle" style="font-family:'Manrope','Segoe UI',Helvetica,Arial,sans-serif; font-size:17px; font-weight:800; letter-spacing:6px; color:#F5F2EA;">
                                        SATURN
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background-color:#101828; border:1px solid #1D2A40; border-radius:16px;" bgcolor="#101828">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td height="3" style="height:3px; line-height:3px; font-size:3px; background-color:#C6A15B; border-radius:16px 16px 0 0;" bgcolor="#C6A15B">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class="px" style="padding:44px 48px 44px; font-family:'Manrope','Segoe UI',Helvetica,Arial,sans-serif; color:#A7B0C0; font-size:15px; line-height:1.7;">
                                        @yield('content')
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" class="px" style="padding:28px 24px 0; font-family:'Manrope','Segoe UI',Helvetica,Arial,sans-serif; font-size:12px; line-height:1.7; color:#667085;">
                            @hasSection('footnote')
                                @yield('footnote')<br><br>
                            @endif
                            This is an automated message from Saturn, so please don't reply to it.<br>
                            &copy; {{ date('Y') }} Saturn Platform. All rights reserved.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
