<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
