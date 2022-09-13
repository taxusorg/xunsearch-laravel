<?php
include_once __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MySqlConnection;

date_default_timezone_set('PRC');
//$dotenv = new Dotenv('./');
//$dotenv->load();

$app = new \Illuminate\Container\Container();
$app['config'] = [];

$manager = new \Laravel\Scout\EngineManager($app);

$manager = new \Laravel\Scout\EngineManager($container);
$manager->extend('xunsearch', function () {
    return new \Taxusorg\XunSearchLaravel\Engines\XunSearchEngine(['server_host' => '192.168.1.100']);
});

function config($key, $default = null) {
    return $default;
}

/**
 * @param $class
 * @param array $p
 * @return \Laravel\Scout\EngineManager|\Laravel\Scout\Builder
 */
function app($class, array $p = []) {
    global $manager;

    if ($class == \Laravel\Scout\Builder::class)
        return new $class($p['model'], $p['query'], $p['callback'], $p['softDelete']);

    if ($class == \Laravel\Scout\EngineManager::class)
        return $manager;
}
