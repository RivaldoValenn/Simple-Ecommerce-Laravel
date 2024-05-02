<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
        Blade::directive('currency', function ($expression) {
            // Check if $expression is a valid numeric value
            if (is_numeric($expression)) {
                // Format as currency only if it's not zero
                if ($expression != 0) {
                    return "Rp. <?php echo number_format($expression, 0, ',', '.'); ?>";
                } else {
                    return "Rp. 0"; // Return zero as a valid currency
                }
            } else {
                return $expression; // Return the original value if not numeric
            }
        });
    }
}
