<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Interfaces\LevelServiceInterface;
use App\Services\LevelService;

class ServiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LevelServiceInterface::class, LevelService::class);
    }

    public function boot(): void
    {
        //
    }
}
