@extends('emails.layout')

@section('title', 'Reset your Saturn password')
@section('preheader', 'Use this link to choose a new password. It expires in 60 minutes.')

@section('footnote')
    For your security, Saturn will never ask for your password by email.
@endsection

@section('content')
    <p style="margin:0 0 10px; font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#C6A15B;">Account security</p>
    <h1 style="margin:0 0 20px; font-size:26px; line-height:1.25; font-weight:700; letter-spacing:-0.4px; color:#F5F2EA;">Reset your password</h1>

    <p style="margin:0 0 6px; color:#A7B0C0;">Hello {{ $userName ?? 'there' }},</p>
    <p style="margin:0 0 30px; color:#A7B0C0;">
        We received a request to reset the password for your Saturn account. Choose a new password using the button below.
    </p>

    {{-- Button --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 30px;">
        <tr>
            <td class="btn-cell" align="center" bgcolor="#C6A15B" style="background-color:#C6A15B; border-radius:10px;">
                <a href="{{ $resetUrl }}" style="display:inline-block; padding:14px 34px; font-family:'Manrope','Segoe UI',Helvetica,Arial,sans-serif; font-size:15px; font-weight:700; color:#0B1220; text-decoration:none; border-radius:10px;">Reset password</a>
            </td>
        </tr>
    </table>

    {{-- Expiry notice --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 28px;">
        <tr>
            <td bgcolor="#0B1220" style="background-color:#0B1220; border:1px solid #1D2A40; border-left:3px solid #C6A15B; border-radius:8px; padding:14px 18px; font-size:13px; line-height:1.6; color:#A7B0C0;">
                <strong style="color:#F5F2EA;">This link expires in 60 minutes.</strong>
                If you didn't request a password reset, you can safely ignore this email. Your password won't change.
            </td>
        </tr>
    </table>

    <p style="margin:0 0 6px; font-size:12px; color:#667085;">If the button doesn't work, copy and paste this link into your browser:</p>
    <p style="margin:0; font-size:12px; line-height:1.6; word-break:break-all;"><a href="{{ $resetUrl }}" style="color:#C6A15B; text-decoration:underline;">{{ $resetUrl }}</a></p>
@endsection