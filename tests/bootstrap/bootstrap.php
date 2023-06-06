<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Support\Facades\Facade;
use Laravel\Scout\Builder;
use Laravel\Scout\EngineManager;
use Laravel\Scout\ScoutServiceProvider;
use Taxusorg\XunSearchLaravel\Libs\BaseBuilderMixin;
use Taxusorg\XunSearchLaravel\ClientFactory;
use Taxusorg\XunSearchLaravel\XunSearchEngine;
use Taxusorg\XunSearchLaravel\XunSearchServiceProvider;
use Illuminate\Config\Repository as Config;
use Illuminate\Foundation\Application;

include_once __DIR__ . '/../../vendor/autoload.php';

date_default_timezone_set('PRC');

function createApp()
{
    $app = new Application(dirname(__DIR__));
    Application::setInstance($app);
    Facade::setFacadeApplication($app);

    $app->singleton('config', function () {
        $config = new Config();
        $config->set('scout.driver', 'xunsearch');
        $config->set('app.providers', [
            ScoutServiceProvider::class,
            XunSearchServiceProvider::class,
        ]);
        return $config;
    });

    $app->registerConfiguredProviders();
    $app->boot();

    return $app;
}
