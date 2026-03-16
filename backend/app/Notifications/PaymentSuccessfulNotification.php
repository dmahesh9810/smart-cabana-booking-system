<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessfulNotification extends Notification implements ShouldQueue
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
            ->subject('Payment Successful - ' . $this->booking->booking_ref)
            ->greeting('Great news ' . $notifiable->name . '!')
            ->line('We have successfully received your payment for booking ' . $this->booking->booking_ref . '.')
            ->line('Your stay is now confirmed.')
            ->line('Cabana: ' . $this->booking->cabana->name)
            ->line('Check-in: ' . $this->booking->check_in->format('Y-m-d'))
            ->action('View Booking', url(env('FRONTEND_URL', 'http://localhost:5173') . '/dashboard/bookings/' . $this->booking->id))
            ->line('We look forward to seeing you soon!')
            ->line('Thank you for choosing Smart Cabana!');
    }

    public function toArray($notifiable)
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_ref' => $this->booking->booking_ref,
            'title' => 'Payment Successful',
            'message' => 'Payment received for ' . $this->booking->booking_ref . '. Your stay is confirmed!',
            'type' => 'payment_success'
        ];
    }
}
