<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelledNotification extends Notification implements ShouldQueue
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
            ->subject('Booking Cancelled - ' . $this->booking->booking_ref)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking ' . $this->booking->booking_ref . ' has been cancelled.')
            ->line('Cabana: ' . $this->booking->cabana->name)
            ->line('Check-in: ' . $this->booking->check_in->format('Y-m-d'))
            ->line('If this cancellation was not intended, please contact our support team immediately.')
            ->action('Browse Other Cabanas', url(env('FRONTEND_URL', 'http://localhost:5173')))
            ->line('We hope to see you another time.');
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_ref' => $this->booking->booking_ref,
            'title' => 'Booking Cancelled',
            'message' => 'Your booking ' . $this->booking->booking_ref . ' has been cancelled.',
            'type' => 'booking_cancelled'
        ];
    }
}
