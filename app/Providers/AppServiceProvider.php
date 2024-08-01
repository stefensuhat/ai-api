<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Xendit\Configuration;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Configuration::setXenditKey(env('XENDIT_API_KEY'));

        Relation::enforceMorphMap([
            'chat' => 'App\Models\Chat',
        ]);
    }
}
