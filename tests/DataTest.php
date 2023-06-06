<?php

namespace Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Laravel\Scout\Builder as BaseBuilder;
use Laravel\Scout\EngineManager;
use PHPUnit\Framework\TestCase;
use Taxusorg\XunSearchLaravel\Builder;
use Taxusorg\XunSearchLaravel\Results;
use Tests\Src\SearchModel;
use Tests\Src\SearchModelWithTrait;
use XSDocument;

class DataTest extends TestCase
{
    use WithApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootApplication();
    }

    public function testSearchable()
    {
        $model = new SearchModel([
            'title' => 'Test Searchable',
            'subtitle' => 'Test Searchable subtitle',
            'content' => 'Content 文本内容 test.'
        ]);
        $model['id'] = 1;
        $model->exists = true;

        $model2 = new SearchModel([
            'title' => 'Test Searchable 2',
            'subtitle' => 'Test Searchable subtitle 2',
            'content' => 'Content 测试 test.'
        ]);
        $model2['id'] = 2;
        $model2->exists = true;

        $model3 = new SearchModel([
            'title' => 'Test Searchable 2',
            'subtitle' => 'Test Searchable subtitle 2',
            'content' => 'Content 测试 test.'
        ]);
        $model3['id'] = 2;
        $model3->exists = true;

        $this->indexMock->expects(self::exactly(3))
            ->method('update')
            ->with($this->isInstanceOf(XSDocument::class))
            ->willReturnSelf();

        $model->searchable();
        /** @noinspection PhpUndefinedMethodInspection */
        (new Collection([$model2, $model3]))->searchable();

        $this->assertTrue(true);
    }

    public function testSearch()
    {
        $this->searchMock
            ->expects(self::exactly(0))
            ->method('setLimit');
        $this->searchMock
            ->expects(self::once())
            ->method('setQuery')
            ->with('test');
        $this->searchMock
            ->expects(self::once())
            ->method('search')
            ->willReturn([]);
        $this->searchMock
            ->expects(self::once())
            ->method('getLastCount')
            ->willReturn(0);

        $builder = SearchModel::search('test');
        $this->assertInstanceOf(BaseBuilder::class, $builder);
        $result = $builder->raw();
        $this->assertInstanceOf(Results::class, $result);

        $this->assertIsInt($result['total']);
        $this->assertEquals($result['total'], $result->getTotal());
        $this->assertEquals([], $result->toArray());
    }

    public function testSearchModelWithTrait()
    {
        $this->searchMock
            ->expects(self::exactly(1))
            ->method('setLimit');
        $this->searchMock
            ->expects(self::once())
            ->method('setQuery')
            ->with('test');
        $this->searchMock
            ->expects(self::once())
            ->method('search')
            ->willReturn([]);
        $this->searchMock
            ->expects(self::once())
            ->method('getLastCount')
            ->willReturn(0);

        $builder = SearchModelWithTrait::search('test');
        $this->assertInstanceOf(Builder::class, $builder);
        $page = $builder->paginateRaw();
        $this->assertEquals([], $page->items());
        $this->assertEquals(0, $page['total']);
    }

    public function testSearchNull()
    {
        $this->searchMock
            ->expects(self::once())
            ->method('setQuery')
            ->with(null);
        $this->searchMock
            ->expects(self::once())
            ->method('search')
            ->willReturn([]);
        $this->searchMock
            ->expects(self::once())
            ->method('getLastCount')
            ->willReturn(0);

        $builder = SearchModelWithTrait::search(null);
        $this->assertInstanceOf(Builder::class, $builder);
        $result = $builder->raw();
        $this->assertInstanceOf(Results::class, $result);
    }

    public function testMapModels()
    {
        $def_ids = [1, 2, 3];

        $documents = collect($def_ids)->map(function ($id) {
            $doc = new XSDocument();
            $doc->setField('test_key', $id);
            return $doc;
        })->values()->toArray();

        $this->searchMock
            ->expects(self::once())
            ->method('search')
            ->willReturn($documents);
        $this->searchMock
            ->expects(self::once())
            ->method('getLastCount')
            ->willReturn(0);

        $builder = SearchModel::search('test');
        $result = $builder->raw();
        $ids = $result->getIds();
        $this->assertInstanceOf(BaseCollection::class, $ids);
        $this->assertEquals($def_ids, $ids->toArray());

        $models = $ids->map(function ($id) {
            $model = new SearchModel();
            $model['id'] = $id;
            $model->exists = true;
            return $model;
        })->all();
        $modelMock = $this->createMock(SearchModel::class);
        $builder->model = $modelMock;
        $result->setBuilder($builder);
        $modelMock
            ->expects($this->once())
            ->method('getScoutModelsByIds')
            ->with($builder, $ids->all())
            ->willReturn(new Collection($models));

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
