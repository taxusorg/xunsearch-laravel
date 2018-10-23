<?php
include_once __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MySqlConnection;

date_default_timezone_set('PRC');
//$dotenv = new Dotenv('./');
//$dotenv->load();



$manager = new \Laravel\Scout\EngineManager(null);

$manager->extend('xunsearch', function () {
    return new \Taxusorg\XunSearchLaravel\Engines\XunSearchEngine(['server_host' => 'localhost']);
});

function config($key, $default = null) {
    return $default;
}

