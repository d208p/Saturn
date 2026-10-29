@extends('emails.layout')

@section('title', 'Profit distribution received')
@section('preheader', 'A distribution of €' . number_format($amount ?? 0, 2) . ' from ' . ($assetName ?? 'your asset') . ' has been credited to your account.')

@section('content')
    <p style="margin:0 0 10px; font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#C6A15B;">Profit distribution</p>
    <h1 style="margin:0 0 20px; font-size:26px; line-height:1.25; font-weight:700; letter-spacing:-0.4px; color:#F5F2EA;">Your distribution has arrived</h1>

    <p style="margin:0 0 6px; color:#A7B0C0;">Hello {{ $userName ?? 'Investor' }},</p>
    <p style="margin:0 0 30px; color:#A7B0C0;">
        A new profit distribution from <strong style="color:#F5F2EA;">{{ $assetName ?? 'your asset' }}</strong>
        has been credited to your Saturn account.
    </p>

    {{-- Amount --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 30px;">
        <tr>
            <td align="center" bgcolor="#0B1220" style="background-color:#0B1220; border:1px solid #1D2A40; border-radius:12px; padding:30px 20px;">
                <div style="font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#667085; margin-bottom:10px;">Amount credited</div>
                <div class="hero-amount" style="font-size:46px; line-height:1.1; font-weight:300; letter-spacing:-1.5px; color:#C6A15B;">+€{{ number_format($amount ?? 0, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Details --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 32px;">
        <tr>
            <td style="padding:14px 0; border-top:1px solid #1D2A40; font-size:14px; color:#667085;">Asset</td>
            <td align="right" style="padding:14px 0; border-top:1px solid #1D2A40; font-size:14px; font-weight:700; color:#F5F2EA;">{{ $assetName ?? 'Asset' }}</td>
        </tr>
        <tr>
            <td style="padding:14px 0; border-top:1px solid #1D2A40; font-size:14px; color:#667085;">Shares held</td>
            <td align="right" style="padding:14px 0; border-top:1px solid #1D2A40; font-size:14px; font-weight:700; color:#F5F2EA;">{{ number_format($shares ?? 0) }}</td>
        </tr>
        <tr>
            {{-- Hardcoded: the 12.1% management tax rate is shown as text, as in the original template. --}}
            <td style="padding:14px 0; border-top:1px solid #1D2A40; font-size:14px; color:#667085;">Management tax deducted (12.1%)</td>
            <td align="right" style="padding:14px 0; border-top:1px solid #1D2A40; font-size:14px; font-weight:700; color:#F5F2EA;">€{{ number_format($taxAmount ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td style="padding:14px 0; border-top:1px solid #1D2A40; border-bottom:1px solid #1D2A40; font-size:14px; color:#667085;">Date</td>
            <td align="right" style="padding:14px 0; border-top:1px solid #1D2A40; border-bottom:1px solid #1D2A40; font-size:14px; font-weight:700; color:#F5F2EA;">{{ ($date ?? now())->format('F j, Y') }}</td>
        </tr>
    </table>

    {{-- Button --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 30px;">
        <tr>
            <td class="btn-cell" align="center" bgcolor="#C6A15B" style="background-color:#C6A15B; border-radius:10px;">
                <a href="{{ $dashboardUrl ?? url('/dashboard') }}" style="display:inline-block; padding:14px 34px; font-family:'Manrope','Segoe UI',Helvetica,Arial,sans-serif; font-size:15px; font-weight:700; color:#0B1220; text-decoration:none; border-radius:10px;">View your dashboard</a>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:13px; color:#667085;">
        Your updated balance and full distribution history are available in your account under Income.
    </p>
@endsection