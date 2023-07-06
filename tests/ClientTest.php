<?php

namespace Tests;

use Error;
use Illuminate\Config\Repository;
use Laravel\Scout\EngineManager;
use PHPUnit\Framework\TestCase;
use Taxusorg\XunSearchLaravel\Client;
use Taxusorg\XunSearchLaravel\ClientFactory;
use Taxusorg\XunSearchLaravel\XunSearchEngine;
use Tests\Src\SearchModel;
use Tests\Src\SearchModelWithTrait;
use XS;
use XSIndex;
use XSSearch;

class ClientTest extends TestCase
{
    public function testClient()
    {
        $xs = $this->createMock(XS::class);
        $client = new Client($xs);
        $this->assertEquals($xs, $client->getXS());

        $xs->expects(self::once())
            ->method('__get')
            ->with('test_get')
            ->willReturn('test_get_result');
        /** @noinspection PhpUndefinedFieldInspection */
        $this->assertEquals('test_get_result', $client->test_get);

        $xs->expects(self::once())
            ->method('__set')
            ->with('test_set', 1);
        /** @noinspection PhpUndefinedFieldInspection */
        $client->test_set = 1;

        $xs->expects(self::once())
            ->method('getField')
            ->with('test_name', false);
        $client->getField('test_name', false);
    }

    public function testBuilderClient()
    {
        $factorySub = $this->createMock(ClientFactory::class);

        $factory = function () {
            $clientSub = $this->createMock(Client::class);
            $xsSearch = $this->createMock(XSSearch::class);
            $xsIndex = $this->createMock(XSIndex::class);
            $clientSub->method('__call')->willReturnCallback(function ($arg) use ($xsSearch, $xsIndex) {
                switch($arg) {
                    case 'getSearch':
                        return $xsSearch;
                    case 'getIndex':
                        return $xsIndex;
                }
                throw new Error('error');
            });
            $clientSub->method('__get')->willReturnCallback(function ($arg) use ($xsSearch, $xsIndex) {
                switch($arg) {
                    case 'search':
                        return $xsSearch;
                    case 'index':
                        return $xsIndex;
                }
                throw new Error('error');
            });
            return $clientSub;
        };

        $factorySub->method('buildClient')->willReturnCallback($factory);
        $factorySub->method('buildClientWithoutModel')->willReturnCallback($factory);
        $factorySub->method('getKeyName')->willReturn('test_key');

        $app = createApp();

        $app->extend(EngineManager::class, function (EngineManager $manager) use ($factorySub) {
            return $manager->extend('xunsearch_mock', function () use ($factorySub) {
                return new XunSearchEngine($factorySub);
            });
        });

        tap($app->make("config"), function (Repository $repository) {
            $repository->set('scout.driver', 'xunsearch_mock');
        });


        $client = SearchModelWithTrait::XS();
        $this->assertInstanceOf(Client::class, $client);
        $this->assertFalse($client === SearchModelWithTrait::XS());

        $builder = SearchModel::search();
        $client2 = $builder->getXS();
        $this->assertTrue($client2 === $builder->getXS());
    }
}
