@extends('emails.layout')

@section('title', 'Verify your email address')
@section('preheader', 'Confirm your email address to finish setting up your Saturn account.')

@section('footnote')
    If you didn't create a Saturn account, no further action is required.
@endsection

@section('content')
    <p style="margin:0 0 10px; font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#C6A15B;">Welcome to Saturn</p>
    <h1 style="margin:0 0 20px; font-size:26px; line-height:1.25; font-weight:700; letter-spacing:-0.4px; color:#F5F2EA;">Verify your email address</h1>

    <p style="margin:0 0 6px; color:#A7B0C0;">Hello {{ $userName ?? 'there' }},</p>
    <p style="margin:0 0 30px; color:#A7B0C0;">
        Thank you for joining Saturn. Please confirm your email address to activate your account and start exploring investment opportunities.
    </p>

    {{-- Button --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 30px;">
        <tr>
            <td class="btn-cell" align="center" bgcolor="#C6A15B" style="background-color:#C6A15B; border-radius:10px;">
                <a href="{{ $verificationUrl }}" style="display:inline-block; padding:14px 34px; font-family:'Manrope','Segoe UI',Helvetica,Arial,sans-serif; font-size:15px; font-weight:700; color:#0B1220; text-decoration:none; border-radius:10px;">Verify email address</a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 6px; font-size:12px; color:#667085;">If the button doesn't work, copy and paste this link into your browser:</p>
    <p style="margin:0; font-size:12px; line-height:1.6; word-break:break-all;"><a href="{{ $verificationUrl }}" style="color:#C6A15B; text-decoration:underline;">{{ $verificationUrl }}</a></p>
@endsection