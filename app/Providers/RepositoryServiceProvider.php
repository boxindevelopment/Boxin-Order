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
        $this->app->bind('App\Repositories\Contracts\SpaceRepository', 'App\Repositories\SpaceRepository');
        $this->app->bind('App\Repositories\Contracts\OrderDetailRepository', 'App\Repositories\OrderDetailRepository');
        $this->app->bind('App\Repositories\Contracts\OrderDetailBoxRepository', 'App\Repositories\OrderDetailBoxRepository');
        $this->app->bind('App\Repositories\Contracts\PriceRepository', 'App\Repositories\PriceRepository');
        $this->app->bind('App\Repositories\Contracts\ReturnBoxRepository', 'App\Repositories\ReturnBoxRepository');
    }
}
