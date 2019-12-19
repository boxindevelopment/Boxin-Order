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
        $this->app->bind('App\Repositories\Contracts\TypeSizeRepository', 'App\Repositories\TypeSizeRepository');
        $this->app->bind('App\Repositories\Contracts\BoxRepository', 'App\Repositories\BoxRepository');
        $this->app->bind('App\Repositories\Contracts\SpaceRepository', 'App\Repositories\SpaceRepository');
        $this->app->bind('App\Repositories\Contracts\OrderDetailRepository', 'App\Repositories\OrderDetailRepository');
        $this->app->bind('App\Repositories\Contracts\OrderDetailBoxRepository', 'App\Repositories\OrderDetailBoxRepository');
        $this->app->bind('App\Repositories\Contracts\PriceRepository', 'App\Repositories\PriceRepository');
        $this->app->bind('App\Repositories\Contracts\ReturnBoxRepository', 'App\Repositories\ReturnBoxRepository');
        $this->app->bind('App\Repositories\Contracts\DeliveryFeeRepository', 'App\Repositories\DeliveryFeeRepository');
        $this->app->bind('App\Repositories\Contracts\TypePickupRepository', 'App\Repositories\TypePickupRepository');
        $this->app->bind('App\Repositories\Contracts\CategoryRepository', 'App\Repositories\CategoryRepository');
        $this->app->bind('App\Repositories\Contracts\SettingRepository', 'App\Repositories\SettingRepository');
        $this->app->bind('App\Repositories\Contracts\VoucherRepository', 'App\Repositories\VoucherRepository');
        $this->app->bind('App\Repositories\Contracts\BannerRepository', 'App\Repositories\BannerRepository');
        $this->app->bind('App\Repositories\Contracts\SpaceSmallRepository', 'App\Repositories\SpaceSmallRepository');
        $this->app->bind('App\Repositories\Contracts\TransactionLogRepository', 'App\Repositories\TransactionLogRepository');
    }
}
