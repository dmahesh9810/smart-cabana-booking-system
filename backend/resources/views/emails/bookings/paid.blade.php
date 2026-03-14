<x-mail::message>
# Payment Successful

Dear {{ $booking->user->name }},

We have successfully received your payment for the following booking. Your cabana is now reserved!

**Booking Details:**
- **Reference:** {{ $booking->booking_ref }}
- **Cabana:** {{ $booking->cabana->name }}
- **Check-in:** {{ $booking->check_in }}
- **Check-out:** {{ $booking->check_out }}
- **Amount Paid:** LKR {{ number_format($booking->total_amount, 2) }}

We look forward to hosting you.

<x-mail::button :url="config('app.url') . '/dashboard/bookings/' . $booking->id">
View Booking Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
