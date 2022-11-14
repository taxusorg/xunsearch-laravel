<?php
namespace Taxusorg\XunSearchLaravel;

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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/xunsearch.php' => config_path('xunsearch.php'),
            ]);
        }

        $this->mergeConfigFrom(__DIR__.'/../config/xunsearch.php', 'xunsearch');

        \Laravel\Scout\Builder::mixin(new BaseBuilderMixin());
    }
}
