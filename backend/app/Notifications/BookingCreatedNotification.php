<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Booking Received - ' . $this->booking->booking_ref)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking for ' . $this->booking->cabana->name . ' has been received.')
            ->line('Booking Reference: ' . $this->booking->booking_ref)
            ->line('Check-in: ' . $this->booking->check_in->format('Y-m-d'))
            ->line('Check-out: ' . $this->booking->check_out->format('Y-m-d'))
            ->line('Total Amount: LKR ' . number_format($this->booking->total_amount, 2))
            ->action('View Booking Details', url(env('FRONTEND_URL', 'http://localhost:5173') . '/dashboard/bookings/' . $this->booking->id))
            ->line('Please proceed with the payment to confirm your booking.')
            ->line('Thank you for choosing Smart Cabana!');
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_ref' => $this->booking->booking_ref,
            'title' => 'Booking Received',
            'message' => 'Your booking ' . $this->booking->booking_ref . ' is pending payment.',
            'type' => 'booking_created'
        ];
    }
}
