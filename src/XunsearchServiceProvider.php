<?php
namespace Taxusorg\XunSearchLaravel;

use Taxusorg\XunSearchLaravel\Engines\XunSearchEngine;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;

class XunSearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/xunsearch.php', 'xunsearch');

        $this->app->extend(EngineManager::class, function (EngineManager $obj, $app) {
            return $obj->extend('xunsearch', function () use ($app) {
                return new XunSearchEngine($app->config['xunsearch']);
            });
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
