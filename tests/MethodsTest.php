<?php

namespace Tests;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Laravel\Scout\EngineManager;
use PHPUnit\Framework\TestCase;
use Taxusorg\XunSearchLaravel\Client;
use Taxusorg\XunSearchLaravel\ClientFactory;
use Taxusorg\XunSearchLaravel\XunSearchEngine;
use Tests\Src\SearchModelWithTrait;
use XS;
use XSSearch;

class MethodsTest extends TestCase
{
    public $searchMock;

    protected $engine_bak;

    public function setUp()
    {
        parent::setUp();

        $factorySub = $this->createMock(ClientFactory::class);
        $xsSub = $this->createMock(XS::class);
        $xsSearch = $this->createMock(XSSearch::class);
        $client = new Client($xsSub);

        $factorySub->method('buildClient')->willReturn($client);
        $xsSub->method('getSearch')->willReturn($xsSearch);
        $xsSub->method('__get')->willReturn($xsSearch);

        $app = Application::getInstance();

        $this->engine_bak = $app->make(EngineManager::class);
        $app->extend(EngineManager::class, function (EngineManager $manager) use ($factorySub, $app) {
            if (method_exists(EngineManager::class, 'forgetDrivers')) {
                $manager->forgetDrivers('xunsearch_mock');

                return $manager->extend('xunsearch_mock', function () use ($factorySub) {
                    return new XunSearchEngine($factorySub);
                });
            }

            return (new EngineManager($app))->extend('xunsearch_mock', function () use ($factorySub) {
                return new XunSearchEngine($factorySub);
            });
        });

        tap($app->make("config"), function (Repository $repository) {
            $repository->set('scout.driver', 'xunsearch_mock');
        });

        $this->searchMock = $xsSearch;

        $this->searchMock->method('search')->willReturn([]);
        $this->searchMock->method('getLastCount')->willReturn(0);
    }

    public function tearDown()
    {
        $app = Application::getInstance();

        $app->extend(EngineManager::class, function (EngineManager $manager) use ($app) {
            return $this->engine_bak;
        });

        tap($app->make("config"), function (Repository $repository) {
            $repository->set('scout.driver', 'xunsearch');
        });

        parent::tearDown();
    }

    public function testSetFuzzy()
    {
        $this->searchMock
            ->expects($this->once())
            ->method('setFuzzy')
            ->withConsecutive([true]);

        SearchModelWithTrait::search('test')
            ->setFuzzy(false)
            ->setFuzzy()
            ->raw();
    }

    public function testSetCutOff()
    {
        $num1 = rand(0, 100);
        $num2 = rand(0.1, 25.5);

        $this->searchMock
            ->expects($this->once())
            ->method('setCutOff')
            ->withConsecutive([$num1, $num2]);

        SearchModelWithTrait::search('test')
            ->setCutOff(-1, -1)
            ->setCutOff($num1, $num2)
            ->raw();
    }

    public function testSetRequireMatchedTerm()
    {
        $this->searchMock
            ->expects($this->once())
            ->method('setRequireMatchedTerm')
            ->withConsecutive([true]);

        SearchModelWithTrait::search('test')
            ->setRequireMatchedTerm(false)
            ->setRequireMatchedTerm(true)
            ->raw();
    }

    public function testSetWeightingScheme()
    {
        $int = rand(0, 2);

        $this->searchMock
            ->expects($this->once())
            ->method('setWeightingScheme')
            ->withConsecutive([$int]);

        SearchModelWithTrait::search('test')
            ->setWeightingScheme(-1)
            ->setWeightingScheme($int)
            ->raw();
    }

    public function testSetAutoSynonyms()
    {
        $this->searchMock
            ->expects($this->once())
            ->method('setAutoSynonyms')
            ->withConsecutive([true]);

        SearchModelWithTrait::search('test')
            ->setAutoSynonyms(false)
            ->setAutoSynonyms()
            ->raw();
    }

    public function testSetSynonymScale()
    {
        $float = rand(0.01, 2.55);

        $this->searchMock
            ->expects($this->once())
            ->method('setSynonymScale')
            ->withConsecutive([$float]);

        SearchModelWithTrait::search('test')
            ->setSynonymScale(-1)
            ->setSynonymScale($float)
            ->raw();
    }

//    public function testSetSort()
//    {
//        $this->searchMock
//            ->expects($this->once())
//            ->method('setSort')
//            ->withConsecutive(['id', 10]);
//
//        SearchModelWithTrait::search('test')
//            ->setSort('-', 0)
//            ->setSort('id', 10)
//            ->raw();
//    }

    public function testSetDocOrder()
    {
        $this->searchMock
            ->expects($this->once())
            ->method('setDocOrder')
            ->withConsecutive([false]);

        SearchModelWithTrait::search('test')
            ->setDocOrder(true)
            ->setDocOrder(false)
            ->raw();
    }

    public function testSetCollapse()
    {
        $this->searchMock
            ->expects($this->once())
            ->method('setCollapse')
            ->withConsecutive(['id', 10]);

        SearchModelWithTrait::search('test')
            ->setCollapse('-', 0)
            ->setCollapse('id', 10)
            ->raw();
    }

    public function testAddRange()
    {
        $range1 = $this->randRange(1,100);
        $range2 = $this->randRange(1,100);

        $this->searchMock
            ->expects($this->exactly(2))
            ->method('addRange')
            ->withConsecutive(
                ['cat', $range1[0], $range1[1]],
                ['id', $range2[0], $range2[1]]
            );

        SearchModelWithTrait::search('test')
            ->addRange('cat', ...$range1)
            ->addRange('id', ...$range2)
            ->raw();
    }

    public function testAddWeight()
    {
        $this->searchMock
            ->expects($this->exactly(2))
            ->method('addWeight')
            ->withConsecutive(
                ['title', 'test_title', 2],
                ['body', 'test_body', 3]
            );

        SearchModelWithTrait::search('test')
            ->addWeight('title', 'test_title', 2)
            ->addWeight('body', 'test_body', 3)
            ->raw();
    }

    public function testSetScwsMulti()
    {
        $int = rand(0, 15);

        $this->searchMock
            ->expects($this->once())
            ->method('setScwsMulti')
            ->withConsecutive([$int]);

        SearchModelWithTrait::search('test')
            ->setScwsMulti(-1)
            ->setScwsMulti($int)
            ->raw();
    }

    protected function randRange(int $min, int $max): array
    {
        $one = rand($min, $max - 1);
        $two = rand($one + 1, $max);

        return [$one, $two];
    }
}
