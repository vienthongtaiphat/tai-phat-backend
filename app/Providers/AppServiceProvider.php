<?php

namespace App\Providers;

use App\Interfaces\CtvUserRepositoryInterface;
use App\Interfaces\OtpRepositoryInterface;
use App\Repositories\CtvUserRepository;
use App\Repositories\OtpRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        OtpRepositoryInterface::class => OtpRepository::class,
        CtvUserRepositoryInterface::class => CtvUserRepository::class,
    ];
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
