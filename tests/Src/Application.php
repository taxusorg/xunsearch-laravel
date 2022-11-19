<?php

namespace Tests\Src;

class Application extends \Illuminate\Container\Container implements \Illuminate\Contracts\Foundation\Application
{

    /**
     * @inheritDoc
     */
    public function version()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function basePath($path = '')
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function bootstrapPath($path = '')
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function configPath($path = '')
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function databasePath($path = '')
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function resourcePath($path = '')
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function storagePath()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function environment(...$environments)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function runningInConsole()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function runningUnitTests()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function isDownForMaintenance()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function registerConfiguredProviders()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function register($provider, $force = false)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function registerDeferredProvider($provider, $service = null)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function resolveProvider($provider)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function booting($callback)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function booted($callback)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function bootstrapWith(array $bootstrappers)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function getLocale()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function getNamespace()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function getProviders($provider)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function hasBeenBootstrapped()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function loadDeferredProviders()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function setLocale($locale)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function shouldSkipMiddleware()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function terminate()
    {
        //
    }
}