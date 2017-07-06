<?php

namespace IainConnor\IlluminateToDoctrineCache;

use Illuminate\Support\ServiceProvider;

class IlluminateToDoctrineCacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(IlluminateToDoctrineCacheFactory::class, function ($app) {
            return new IlluminateToDoctrineCacheFactory($app['cache']);
        });
    }
}
