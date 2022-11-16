<?php
namespace Taxusorg\XunSearchLaravel;

use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Taxusorg\XunSearchLaravel\Libs\BaseBuilderMixin;

class XunSearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws
     */
    public function boot()
    {
        \Laravel\Scout\Builder::mixin(new BaseBuilderMixin());

        $this->app->extend(EngineManager::class, function (EngineManager $obj) {
            return $obj->extend('xunsearch', function () {
                return new XunSearchEngine($this->app['config']['xunsearch']);
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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/xunsearch.php' => $this->app->configPath('xunsearch.php'),
            ], 'config');
        }

        $this->mergeConfigFrom(__DIR__.'/../config/xunsearch.php', 'xunsearch');
    }
}
