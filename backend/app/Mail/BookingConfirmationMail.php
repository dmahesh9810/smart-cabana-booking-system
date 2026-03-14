<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmed — ' . $this->booking->booking_ref,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
            with: [
                'booking'     => $this->booking,
                'cabanaName'  => $this->booking->cabana?->name ?? 'Your Cabana',
                'guestName'   => $this->booking->user?->name ?? 'Valued Guest',
                'checkIn'     => $this->booking->check_in,
                'checkOut'    => $this->booking->check_out,
                'totalAmount' => $this->booking->total_amount,
                'bookingRef'  => $this->booking->booking_ref,
            ]
        );
    }
}
