<?php

namespace App\Providers;

use App\Services\CloudinaryImageUploadService;
use App\Services\Contracts\ImageUploadServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ImageUploadServiceInterface::class, CloudinaryImageUploadService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
