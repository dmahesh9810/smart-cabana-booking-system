<x-mail::message>
# Booking Confirmed

Dear {{ $booking->user->name }},

Great news! Your booking has been officially confirmed by our team.

**Booking Details:**
- **Reference:** {{ $booking->booking_ref }}
- **Cabana:** {{ $booking->cabana->name }}
- **Check-in:** {{ $booking->check_in }}
- **Check-out:** {{ $booking->check_out }}

We hope you have a fantastic stay.

<x-mail::button :url="config('app.url') . '/dashboard/bookings/' . $booking->id">
View Booking
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
