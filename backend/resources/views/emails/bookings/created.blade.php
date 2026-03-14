<x-mail::message>
# New Booking Created

Dear {{ $booking->user->name }},

Thank you for choosing Smart Cabana. Your booking has been successfully created and is currently pending payment.

**Booking Details:**
- **Reference:** {{ $booking->booking_ref }}
- **Cabana:** {{ $booking->cabana->name }}
- **Check-in:** {{ $booking->check_in }}
- **Check-out:** {{ $booking->check_out }}
- **Total Amount:** LKR {{ number_format($booking->total_amount, 2) }}

Please complete your payment within 15 minutes to secure your booking.

<x-mail::button :url="config('app.url') . '/dashboard/bookings/' . $booking->id">
View Booking
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
