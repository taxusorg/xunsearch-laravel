<?php

namespace Tests;

use Error;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Laravel\Scout\EngineManager;
use PHPUnit\Framework\MockObject\MockObject;
use Taxusorg\XunSearchLaravel\Client;
use Taxusorg\XunSearchLaravel\ClientFactory;
use Taxusorg\XunSearchLaravel\XunSearchEngine;
use XSIndex;
use XSSearch;

trait WithApplication
{
    /**
     * @var Application
     */
    protected $app;
    /**
     * @var MockObject|XSSearch
     */
    protected $searchMock;
    /**
     * @var MockObject|XSIndex
     */
    protected $indexMock;

    protected function bootApplication()
    {
        $factorySub = $this->createMock(ClientFactory::class);
        $clientSub = $this->createMock(Client::class);
        $this->searchMock = $xsSearch = $this->createMock(XSSearch::class);
        $this->indexMock = $xsIndex = $this->createMock(XSIndex::class);

        $factorySub->method('buildClient')->willReturn($clientSub);
        $factorySub->method('buildClientWithoutModel')->willReturn($clientSub);
        $factorySub->method('getKeyName')->willReturn('test_key');
        $clientSub->method('__call')->willReturnCallback(function ($arg) {
            switch($arg) {
                case 'getSearch':
                    return $this->searchMock;
                case 'getIndex':
                    return $this->indexMock;
            }
            throw new Error('error');
        });
        $clientSub->method('__get')->willReturnCallback(function ($arg) {
            switch($arg) {
                case 'search':
                    return $this->searchMock;
                case 'index':
                    return $this->indexMock;
            }
            throw new Error('error');
        });

        $this->app = createApp();

        $this->app->extend(EngineManager::class, function (EngineManager $manager) use ($factorySub) {
            return $manager->extend('xunsearch_mock', function () use ($factorySub) {
                return new XunSearchEngine($factorySub);
            });
        });

        tap($this->app->make("config"), function (Repository $repository) {
            $repository->set('scout.driver', 'xunsearch_mock');
        });
    }
}