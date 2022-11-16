<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Laravel\Scout\Builder;
use Laravel\Scout\EngineManager;
use Taxusorg\XunSearchLaravel\Libs\BaseBuilderMixin;
use Taxusorg\XunSearchLaravel\ClientFactory;
use Taxusorg\XunSearchLaravel\XunSearchEngine;

include_once __DIR__ . '/../../vendor/autoload.php';

date_default_timezone_set('PRC');

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
$app = Container::getInstance();

function config($key, $default = null) {
    return app('config')[$key] ?? $default;
}
$app->singleton('config', function () {
    return [
        'scout.driver' => 'xunsearch'
    ];
});

function registerEngine() {
    $app = Container::getInstance();

    $app->singleton(EngineManager::class, function ($app) {
        return new EngineManager($app);
    });

    $app->extend(EngineManager::class, function (EngineManager $manager) {
        return $manager->extend('xunsearch', function () {
            return new XunSearchEngine(new ClientFactory());
        });
    });
}
registerEngine();

Builder::mixin(new BaseBuilderMixin());
