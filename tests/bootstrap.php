<?php
include_once __DIR__.'/../vendor/autoload.php';

date_default_timezone_set('PRC');

/**
 * @param $class
 * @param array $p
 * @return \Laravel\Scout\EngineManager|\Laravel\Scout\Builder|\Illuminate\Container\Container
 */
function app($class = null, array $p = []) {
    $app = \Illuminate\Container\Container::getInstance();
    if (! $class) return $app;

    return $app->make($class, $p);
}
$app = \Illuminate\Container\Container::getInstance();

$app['config'] = [];
function config($key, $default = null) {

    return $default;
}

$manager = new \Laravel\Scout\EngineManager($app);
$manager->extend('xunsearch', function () {
    return new \Taxusorg\XunSearchLaravel\Engines\XunSearchEngine(new \Taxusorg\XunSearchLaravel\ClientFactory(['server_host' => 'localhost']));
});
$app->instance(\Laravel\Scout\EngineManager::class, $manager);


\Laravel\Scout\Builder::mixin(new \Taxusorg\XunSearchLaravel\BaseBuilderMixin());
