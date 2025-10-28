<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;


class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Paginator::useBootstrapFive(); // o useBootstrapFour() si usas Bootstrap 4
    }

    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */

}
