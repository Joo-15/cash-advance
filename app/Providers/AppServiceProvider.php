<?php

namespace App\Providers;

use App\Models\CashAdvance;
use App\Observers\CashAdvanceObserver;
use Illuminate\Support\Facades\Vite;
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
        // Vite::prefetch(concurrency: 3);
        CashAdvance::observe(CashAdvanceObserver::class);
    }
}
