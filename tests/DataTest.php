<?php

namespace Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder as BaseBuilder;
use Laravel\Scout\EngineManager;
use PHPUnit\Framework\TestCase;
use Taxusorg\XunSearchLaravel\Builder;
use Taxusorg\XunSearchLaravel\Results;
use Tests\Src\SearchModel;
use Tests\Src\SearchModelWithTrait;

class DataTest extends TestCase
{
    public function testSearchable()
    {
        $model = new SearchModel([
            'title' => 'Test Searchable',
            'subtitle' => 'Test Searchable subtitle',
            'content' => 'Content 文本内容 test.'
        ]);
        $model['id'] = 1;
        $model->exists = true;

        $model->searchable();

        $model2 = new SearchModel([
            'title' => 'Test Searchable 2',
            'subtitle' => 'Test Searchable subtitle 2',
            'content' => 'Content 测试 test.'
        ]);
        $model2['id'] = 2;
        $model2->exists = true;

        $model2->searchable();

        $this->assertTrue(true);
    }

    public function testSearch()
    {
        $builder = SearchModel::search('test');
        $this->assertInstanceOf(BaseBuilder::class, $builder);
        $result1 = $builder->raw();
        $this->assertInstanceOf(Results::class, $result1);

        $this->assertIsInt($result1['total']);
        $this->assertEquals($result1['total'], $result1->getTotal());

        $builder = SearchModelWithTrait::search('test');
        $this->assertInstanceOf(Builder::class, $builder);
        $result2 = $builder->raw();
        $this->assertInstanceOf(Results::class, $result2);

        $this->assertEquals($result1['docs'], $result2['docs']);
        $this->assertEquals($result1['total'], $result2['total']);
    }

    public function testMapModels()
    {
        $builder = SearchModel::search('test');
        $result = $builder->raw();
        $ids = $result->getIds();
        $models = $ids->map(function ($id) {
            $model = new SearchModel();
            $model['id'] = $id;
            $model->exists = true;
            return $model;
        })->all();

        $modelMock = $this->createMock(SearchModel::class);
        $builder->model = $modelMock;
        $result->setBuilder($builder);

        if (count($models)) {
            $modelMock
                ->expects($this->once())
                ->method('getScoutModelsByIds')
                ->withConsecutive([$builder, $ids->all()])
                ->willReturn(new Collection($models));
        } else {
            $modelMock
                ->expects($this->once())
                ->method('newCollection')
                ->withConsecutive([])
                ->willReturn(new Collection());
        }

        $this->assertEquals($models, $result->getModels()->all());
    }

    public function testClean()
    {
        /**
         * @var EngineManager $manager
         */
        $manager = Container::getInstance()->make(EngineManager::class);
        $engine = $manager->engine();

        $this->assertTrue($engine->deleteIndex((new SearchModel())->searchableAs()));
    }
}
