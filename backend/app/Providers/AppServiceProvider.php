<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ChannelManagerInterface;
use App\Services\Integrations\BookingComService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ChannelManagerInterface::class, BookingComService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use default email verification url mechanism
        // Our api.php contains Route::get('/email/verify/{id}/{hash}')->name('verification.verify')
        // Laravel will automatically find and use this named route.
    }
}
