<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder as BaseBuilder;
use PHPUnit\Framework\TestCase;
use Taxusorg\XunSearchLaravel\Builder;
use Taxusorg\XunSearchLaravel\Client;

class SimpleTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testSimple()
    {
        $XS_1 = SearchModel::search()->getXS();
        $XS_2 = SearchModel::search()->getXS();

        $this->assertInstanceOf(Client::class, $XS_1);
        $this->assertInstanceOf(\XSIndex::class, $XS_1->index);
        $this->assertInstanceOf(\XSSearch::class, $XS_1->search);
        $this->assertFalse($XS_1 === $XS_2);
        $this->assertFalse($XS_1->index === $XS_2->index);
        $this->assertFalse($XS_1->search === $XS_2->search);

        $resource = $XS_2->index->getSocket();
        $this->assertIsResource($resource);
        unset($XS_2);
//        $gc = gc_collect_cycles();
        $this->assertFalse(is_resource($resource));

        $this->assertInstanceOf(Builder::class, SearchModelWithTrait::search());
        $this->assertInstanceOf(\XSIndex::class, SearchModelWithTrait::XS()->index);

        $this->assertInstanceOf(BaseBuilder::class, SearchModel::search());
        $this->assertNotInstanceOf(Builder::class, SearchModel::search());
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

        $model->searchable();

        $model2 = new SearchModel([
            'title' => 'Test Searchable 2',
            'subtitle' => 'Test Searchable subtitle 2',
            'content' => 'Content 测试 test.'
        ]);
        $model2['id'] = 2;
        $model2->exists = true;

        $model2->searchable();

        $collection = new Collection();
        $collection->add($this->buildTestModel(3));
        $collection->add($this->buildTestModel(4));
        $collection->add($this->buildTestModel(5));

        $collection->searchable();

        $this->assertTrue(true);
    }

    public function testSearch()
    {
        $builder = SearchModel::search('test');
        $this->assertInstanceOf(BaseBuilder::class, $builder);
        $result1 = $builder->raw();
        $this->assertIsArray($result1);

        $builder = SearchModelWithTrait::search('test');
        $this->assertInstanceOf(Builder::class, $builder);
        $result2 = $builder->raw();
        $this->assertIsArray($result2);

        $this->assertEquals($result1, $result2);
    }

    public function testMethod()
    {
        $builder = SearchModelWithTrait::search('test');
        $builder->setFuzzy(true);
        $builder->addWeight('title', 'test', 100);
//        $builder->setCutOff(0, 0);
        $builder->setRequireMatchedTerm();
        $builder->setWeightingScheme(0);
        $builder->setAutoSynonyms();
        $builder->setSynonymScale(5);
        $builder->setCollapse('id', 5);
        $result = $builder->raw();

        $this->assertTrue(true);
    }
}
