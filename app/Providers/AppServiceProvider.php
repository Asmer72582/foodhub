<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class

AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // On Hostinger, where the Laravel project is in public_html/foodhub
        // we map the public directory to the parent public_html to ensure
        // all images render at the domain root (https://foodhub.space/).
        if ($this->app->environment('production')) {
            $this->app->bind('path.public', function() {
                return base_path('../');
            });
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
