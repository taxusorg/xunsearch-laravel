<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Facade;
use Laravel\Scout\Builder;
use Laravel\Scout\EngineManager;
use Laravel\Scout\ScoutServiceProvider;
use Taxusorg\XunSearchLaravel\Libs\BaseBuilderMixin;
use Taxusorg\XunSearchLaravel\ClientFactory;
use Taxusorg\XunSearchLaravel\XunSearchEngine;
use Taxusorg\XunSearchLaravel\XunSearchServiceProvider;
use Tests\Src\Application;

include_once __DIR__ . '/../../vendor/autoload.php';

date_default_timezone_set('PRC');
$app = Application::getInstance();
Facade::setFacadeApplication($app);

/**
 * @param $class
 * @param array $p
 * @return EngineManager|Builder|Container
 * @throws BindingResolutionException
 */
function app($class = null, array $p = []) {
    $app = Container::getInstance();
    if (! $class) return $app;

    return $app->make($class, $p);
}

function config($key, $default = null) {
    return app('config')[$key] ?? $default;
}
$app->singleton('config', function () {
    $config = new Illuminate\Config\Repository();
    $config->set('scout.driver', 'xunsearch');
    return $config;
});

function registerEngine() {
    $app = Application::getInstance();

    tap(new ScoutServiceProvider($app), function (ScoutServiceProvider $provider) {
        $provider->register();
        $provider->boot();;
    });
    tap(new XunSearchServiceProvider($app), function (XunSearchServiceProvider $provider) {
        $provider->register();
        $provider->boot();;
    });
}
registerEngine();
