<?php

namespace Tests;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder;
use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testSimple()
    {
        $builder = SearchModelInterfaceModel::search('test')->fuzzy()->range('id',1,200);
        $xss = $builder->xunSearch();
        $builder2 = SearchModelInterfaceModel::search('测试')->range('id', 1, 100);
        $xss2 = $builder2->xunSearch();
        $builder3 = SearchModelInterfaceModel::search('searchable');

        $result = $builder->raw();
        $result2 = $builder2->raw();
        $result3 = $builder3->raw();

        $relation = $builder->getRelatedQuery();
        $relation2 = SearchModelInterfaceModel::searchableRelatedQuery('test');
        $hot = SearchModelInterfaceModel::searchableHotQuery();
        $cor = SearchModelInterfaceModel::searchableCorrectedQuery('测');

        $this->assertTrue(true);
    }

    public function testSearchable()
    {
        $model = new SearchModelInterfaceModel([
            'title' => 'Test Searchable',
            'subtitle' => 'Test Searchable subtitle',
            'content' => 'Content 文本内容 test.'
        ]);
        $model['id'] = 1;
        $model->exists = true;

        $model->searchable();

        $model2 = new SearchModelInterfaceModel([
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

    /**
     * @param int $id
     * @return SearchModelInterfaceModel
     */
    protected function buildTestModel(int $id)
    {
        $m = new SearchModelInterfaceModel([
            'title' => 'Test Searchable ' . $id,
            'subtitle' => 'Test Searchable subtitle ' . $id,
            'content' => 'Content 测试 test.'
        ]);
        $m['id'] = $id;
        $m->exists = true;

        return $m;
    }
}
