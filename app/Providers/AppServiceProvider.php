<?php

namespace App\Providers;

use App\Services\Providers\BackgroundRemovalProvider;
use App\Services\Providers\PythonModelProvider;
use App\Services\Providers\RemoveBgProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BackgroundRemovalProvider::class, fn () => match (config('bgremover.provider')) {
            'removebg' => new RemoveBgProvider,
            default => new PythonModelProvider,
        });
    }

    public function boot(): void
    {
        RateLimiter::for('uploads', fn (Request $r) => Limit::perHour(config('bgremover.limits.uploads_per_hour'))->by($r->ip()));
        RateLimiter::for('poll', fn (Request $r) => Limit::perMinute(240)->by($r->ip()));
    }
}
