<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingExpiredNotification extends Notification implements ShouldQueue
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
            ->subject('Booking Expired - ' . $this->booking->booking_ref)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We noticed that payment was not received for your booking ' . $this->booking->booking_ref . '.')
            ->line('As a result, your booking has expired and the reserved dates have been released.')
            ->line('Cabana: ' . $this->booking->cabana->name)
            ->line('Check-in: ' . $this->booking->check_in->format('Y-m-d'))
            ->action('Re-book Now', url(env('FRONTEND_URL', 'http://localhost:5173') . '/cabana/' . $this->booking->cabana_id))
            ->line('If you still wish to stay with us, please make a new booking.');
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_ref' => $this->booking->booking_ref,
            'title' => 'Booking Expired',
            'message' => 'Your booking ' . $this->booking->booking_ref . ' has expired due to non-payment.',
            'type' => 'booking_expired'
        ];
    }
}
