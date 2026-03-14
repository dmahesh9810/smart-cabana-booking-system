<x-mail::message>
# Booking Cancelled

Dear {{ $booking->user->name }},

This email is to notify you that your booking has been cancelled.

**Booking Details:**
- **Reference:** {{ $booking->booking_ref }}
- **Cabana:** {{ $booking->cabana->name }}
- **Check-in:** {{ $booking->check_in }}
- **Check-out:** {{ $booking->check_out }}

If this was unplanned or if you have any questions, please contact our support team.

<x-mail::button :url="config('app.url')">
Visit Website
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
