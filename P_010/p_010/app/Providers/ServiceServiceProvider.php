<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Interfaces\LevelServiceInterface;
use App\Services\LevelService;
//use App\Services\IOFactoryWrapper;

class ServiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LevelServiceInterface::class, LevelService::class);
//        $this->app->bind(ExcelReaderInterface::class, IOFactoryWrapper::class);
    }

    public function boot(): void
    {
        //
    }
}
