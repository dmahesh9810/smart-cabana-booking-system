<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-email', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('This is a test email from Smart Cabana Booking System using Mailtrap!', function ($message) {
            $message->to('test@example.com')
                    ->subject('Mailtrap Test Email');
        });
        return 'Test email sent! Check your Mailtrap inbox.';
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Mailtrap Test Failed: ' . $e->getMessage());
        return 'Failed to send email. Check logs. Error: ' . $e->getMessage();
    }
});
