<?php

namespace Tests;

use Laravel\Scout\Builder as BaseBuilder;
use PHPUnit\Framework\TestCase;
use Taxusorg\XunSearchLaravel\Builder;
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
        $this->assertIsArray($result1);

        $builder = SearchModelWithTrait::search('test');
        $this->assertInstanceOf(Builder::class, $builder);
        $result2 = $builder->raw();
        $this->assertIsArray($result2);

        $this->assertEquals($result1, $result2);
    }
}
