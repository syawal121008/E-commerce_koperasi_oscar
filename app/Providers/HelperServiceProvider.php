<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Daftarkan helper files
        $helperFiles = [
            app_path('Helpers/NotificationHelper.php'),
        ];

        foreach ($helperFiles as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}