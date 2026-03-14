<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Booking Reminder</title>
  <style>
    body { margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f8fafc; }
    .wrapper { max-width: 580px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
    .header { background: linear-gradient(135deg, #f59e0b, #ef4444); padding: 36px 32px; text-align: center; }
    .header h1 { color: #fff; margin: 0; font-size: 24px; font-weight: 700; }
    .header p  { color: rgba(255,255,255,.85); margin: 8px 0 0; font-size: 14px; }
    .icon { font-size: 52px; display: block; margin-bottom: 12px; }
    .body   { padding: 36px 32px; }
    .greeting { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
    .intro    { color: #64748b; font-size: 14px; line-height: 1.7; margin-bottom: 24px; }
    .card { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 20px 24px; margin-bottom: 24px; }
    .card-row { display: flex; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid #fef3c7; font-size: 14px; }
    .card-row:last-child { border-bottom: none; }
    .card-row .label { color: #64748b; font-weight: 500; }
    .card-row .value { color: #1e293b; font-weight: 600; }
    .highlight { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px 16px; border-radius: 8px; font-size: 14px; color: #92400e; margin-bottom: 24px; }
    .footer { background: #f1f5f9; text-align: center; padding: 20px 32px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
  </style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <span class="icon">⏰</span>
    <h1>Your Stay Is Tomorrow!</h1>
    <p>Just a friendly reminder about your upcoming cabana stay.</p>
  </div>

  <div class="body">
    <p class="greeting">Hello, {{ $guestName }}!</p>
    <p class="intro">
      Your stay at <strong>{{ $cabanaName }}</strong> begins <strong>tomorrow</strong>.
      We're looking forward to welcoming you! 🌴
    </p>

    <div class="highlight">
      🗓️ <strong>Check-in Tomorrow:</strong> {{ \Carbon\Carbon::parse($checkIn)->format('l, d F Y') }}
    </div>

    <div class="card">
      <div class="card-row">
        <span class="label">Booking Reference</span>
        <span class="value">{{ $bookingRef }}</span>
      </div>
      <div class="card-row">
        <span class="label">Cabana</span>
        <span class="value">{{ $cabanaName }}</span>
      </div>
      @if($location)
      <div class="card-row">
        <span class="label">Location</span>
        <span class="value">{{ $location }}</span>
      </div>
      @endif
      <div class="card-row">
        <span class="label">Check-in</span>
        <span class="value">{{ \Carbon\Carbon::parse($checkIn)->format('D, d M Y') }}</span>
      </div>
      <div class="card-row">
        <span class="label">Check-out</span>
        <span class="value">{{ \Carbon\Carbon::parse($checkOut)->format('D, d M Y') }}</span>
      </div>
    </div>

    <p style="color:#64748b; font-size:14px; line-height:1.7;">
      If you have any questions before your arrival, please don't hesitate to contact us.
      We hope you have a wonderful stay! 🏖️
    </p>
  </div>

  <div class="footer">
    <p>© {{ date('Y') }} Smart Cabana Booking. All rights reserved.</p>
  </div>
</div>
</body>
</html>
