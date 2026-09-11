<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (config('nativephp-internal.platform') === 'android' && extension_loaded('intl')) {
            $icuMajorVersion = explode('.', INTL_ICU_VERSION)[0];

            if (is_file(resource_path("icu/icudt{$icuMajorVersion}l.dat"))) {
                putenv('ICU_DATA='.resource_path('icu'));
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
