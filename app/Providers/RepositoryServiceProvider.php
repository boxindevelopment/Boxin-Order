<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Repositories\Contracts\BoxRepository', 'App\Repositories\BoxRepository');
        $this->app->bind('App\Repositories\Contracts\RoomRepository', 'App\Repositories\RoomRepository');
        $this->app->bind('App\Repositories\Contracts\OrderDetailRepository', 'App\Repositories\OrderDetailRepository');
        $this->app->bind('App\Repositories\Contracts\PriceRepository', 'App\Repositories\PriceRepository');
    }
}
