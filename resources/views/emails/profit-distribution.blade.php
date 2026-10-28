<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0d1117; color: #e6edf3; margin: 0; padding: 40px; }
        .card { background: #161b22; border: 1px solid #30363d; border-radius: 8px; padding: 32px; max-width: 500px; margin: 0 auto; }
        .title { font-size: 20px; font-weight: 700; color: #f0f6fc; margin-bottom: 16px; }
        .stat { font-size: 24px; font-weight: 700; color: #C6A15B; margin: 16px 0; }
        .muted { color: #8b949e; font-size: 14px; line-height: 1.5; }
        .footer { margin-top: 24px; font-size: 12px; color: #484f58; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="title">Profit Distribution Received</div>
        <p class="muted">Hello {{ $userName ?? 'Investor' }},</p>
        <p class="muted">A new profit distribution has been successfully credited to your Saturn account for your holdings in <strong>{{ $assetName ?? 'Asset' }}</strong>.</p>
        
        <div class="stat">+€{{ number_format($amount ?? 0, 2) }}</div>
        
        <p class="muted">
            <strong>Shares Held:</strong> {{ number_format($shares ?? 0) }}<br>
            <strong>Management Tax Deducted:</strong> €{{ number_format($taxAmount ?? 0, 2) }} (12.1%)
        </p>
        
        <p class="muted">You can view your updated balance and track your portfolio performance directly on your dashboard.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} Saturn Platform. All rights reserved.
        </div>
    </div>
</body>
</html>