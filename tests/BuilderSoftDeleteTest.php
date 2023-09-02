<?php

namespace Tests;

use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;
use Tests\Src\SearchModelWithSoftDelete;
use XSDocument;

class BuilderSoftDeleteTest extends TestCase
{
    use WithApplication;

    /**
     * @throws
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->bootApplication();
    }

    public function testSearchableWithSoftDelete()
    {
        $model = new SearchModelWithSoftDelete([
            'title' => 'Test Searchable',
            'subtitle' => 'Test Searchable subtitle',
            'content' => 'Content 文本内容 test.'
        ]);
        $model['id'] = 1;
        $model->exists = true;

        $model2 = new SearchModelWithSoftDelete([
            'title' => 'Test Searchable 2',
            'subtitle' => 'Test Searchable subtitle 2',
            'content' => 'Content 测试 test.'
        ]);
        $model2['id'] = 2;
        $model2['deleted_at'] = now()->format(DATE_W3C);
        $model2->exists = true;

        $model3 = new SearchModelWithSoftDelete([
            'title' => 'Test Searchable 2',
            'subtitle' => 'Test Searchable subtitle 2',
            'content' => 'Content 测试 test.'
        ]);
        $model3['id'] = 3;
        $model3->exists = true;

        $this->indexMock->expects(self::exactly(3))
            ->method('update')
            ->with(
                $this->callback(function ($document) {
                    $this->assertInstanceOf(XSDocument::class, $document);
                    $this->assertArrayHasKey('__soft_deleted', $document->getFields());
                    return $document['__soft_deleted'] == (($document['test_key'] == 2) ? 1 : 0);
                })
            )
            ->willReturnSelf();

        $model->searchable();
        /** @noinspection PhpUndefinedMethodInspection */
        (new Collection([$model2, $model3]))->searchable();
    }

    public function testSearchWithNotSoftDeleted()
    {
        $this->searchMock
            ->expects(self::any())
            ->method('setLimit');
        $this->searchMock
            ->expects(self::once())
            ->method('setQuery')
            ->with(self::stringContains("__soft_deleted:0"));
        $this->searchMock
            ->expects(self::once())
            ->method('search')
            ->willReturn([]);
        $this->searchMock
            ->expects(self::once())
            ->method('getLastCount')
            ->willReturn(0);

        SearchModelWithSoftDelete::search('test')->raw();
    }

    public function testSearchOnlySoftDeleted()
    {
        $this->searchMock
            ->expects(self::any())
            ->method('setLimit');
        $this->searchMock
            ->expects(self::once())
            ->method('setQuery')
            ->with(self::stringContains("__soft_deleted:1"));
        $this->searchMock
            ->expects(self::once())
            ->method('search')
            ->willReturn([]);
        $this->searchMock
            ->expects(self::once())
            ->method('getLastCount')
            ->willReturn(0);

        SearchModelWithSoftDelete::search('test')->onlyTrashed()->raw();
    }

    public function testSearchWithSoftDeleted()
    {
        $this->searchMock
            ->expects(self::any())
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

        SearchModelWithSoftDelete::search('test')->withTrashed()->raw();
    }
}
