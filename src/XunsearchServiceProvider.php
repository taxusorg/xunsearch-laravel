<?php
namespace Taxusorg\XunsearchLaravel;

use Taxusorg\XunsearchLaravel\Engines\XunsearchEngine;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;

class XunsearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->extend(EngineManager::class, function (EngineManager $obj, $app) {
            return $obj->extend('xunsearch', function () {
                return new XunsearchEngine();
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
