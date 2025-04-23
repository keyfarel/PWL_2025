<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\LevelRepositoryInterface;
use App\Repositories\Eloquent\EloquentLevelRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LevelRepositoryInterface::class, EloquentLevelRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
