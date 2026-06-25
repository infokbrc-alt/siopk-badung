<?php

namespace App\Providers;

use App\Services\AiOpkAnalyzer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiOpkAnalyzer::class, function () {
            return new AiOpkAnalyzer(
                apiKey: config('services.claude.api_key', '')
            );
        });
    }

    public function boot(): void {}
}
