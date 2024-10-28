<?php

namespace App\Providers;

use App\Services\JokeAggregator;
//use App\Services\WorldOfJokesProvider;
use App\Services\JokeApiProvider;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(JokeAggregator::class, function ($app) {
            $client = new Client();
            $providers = [
//                new WorldOfJokesProvider($client),
                new JokeApiProvider($client)
            ];

            return new JokeAggregator($providers, Log::channel('stack'));
        });
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
