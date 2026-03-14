<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Payment Receipt</title>
  <style>
    body { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f8fafc; }
    .wrapper { max-width: 580px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
    .header { background: linear-gradient(135deg, #059669, #10b981); padding: 36px 32px; text-align: center; }
    .header h1 { color: #fff; margin: 0; font-size: 24px; font-weight: 700; }
    .header p  { color: rgba(255,255,255,.8); margin: 8px 0 0; font-size: 14px; }
    .icon { font-size: 52px; display: block; margin-bottom: 12px; }
    .body   { padding: 36px 32px; }
    .greeting { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
    .intro    { color: #64748b; font-size: 14px; line-height: 1.7; margin-bottom: 24px; }
    .card { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px 24px; margin-bottom: 24px; }
    .card-row { display: flex; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid #d1fae5; font-size: 14px; }
    .card-row:last-child { border-bottom: none; }
    .card-row .label { color: #64748b; font-weight: 500; }
    .card-row .value { color: #1e293b; font-weight: 600; }
    .total-row .value { color: #059669; font-size: 16px; }
    .footer { background: #f1f5f9; text-align: center; padding: 20px 32px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
  </style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <span class="icon">🧾</span>
    <h1>Payment Received</h1>
    <p>Your payment has been successfully processed.</p>
  </div>

  <div class="body">
    <p class="greeting">Thank you, {{ $guestName }}!</p>
    <p class="intro">
      We have received your payment for the booking at <strong>{{ $cabanaName }}</strong>.
      Please keep this receipt for your records.
    </p>

    <div class="card">
      <div class="card-row">
        <span class="label">Booking Reference</span>
        <span class="value">{{ $bookingRef }}</span>
      </div>
      <div class="card-row">
        <span class="label">Cabana</span>
        <span class="value">{{ $cabanaName }}</span>
      </div>
      <div class="card-row">
        <span class="label">Check-in</span>
        <span class="value">{{ \Carbon\Carbon::parse($checkIn)->format('D, d M Y') }}</span>
      </div>
      <div class="card-row">
        <span class="label">Check-out</span>
        <span class="value">{{ \Carbon\Carbon::parse($checkOut)->format('D, d M Y') }}</span>
      </div>
      <div class="card-row">
        <span class="label">Payment Method</span>
        <span class="value">{{ ucfirst($paymentMethod) }}</span>
      </div>
      <div class="card-row total-row">
        <span class="label">Amount Paid</span>
        <span class="value">LKR {{ number_format($paymentAmount, 2) }}</span>
      </div>
    </div>
  </div>

  <div class="footer">
    <p>© {{ date('Y') }} Smart Cabana Booking. All rights reserved.</p>
    <p>This is an automated receipt. Please do not reply to this email.</p>
  </div>
</div>
</body>
</html>
