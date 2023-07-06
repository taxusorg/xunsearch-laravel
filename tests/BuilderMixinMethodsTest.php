<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Taxusorg\XunSearchLaravel\Client;
use Tests\Src\SearchModel;
use Tests\Src\SearchModelWithTrait;
use XS;
use XSIndex;
use XSSearch;

class BuilderMixinMethodsTest extends TestCase
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

    public function testGetDbTotal()
    {
        $total = rand();
        $this->searchMock->expects(self::once())
            ->method('getDbTotal')
            ->willReturn($total);
        $builder = SearchModel::search();
        $result = $builder->getXSTotal();
        $this->assertEquals($total, $result);
    }

    public function testGetXS()
    {
        $builder = SearchModel::search();
        $client = $builder->getXS();
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testGetXSIndex()
    {
        $builder = SearchModel::search();
        $xsIndex = $builder->getXSIndex();
        $this->assertInstanceOf(XSIndex::class, $xsIndex);
    }

    public function testGetXSSearch()
    {
        $builder = SearchModel::search();
        $xsSearch = $builder->getXSSearch();
        $this->assertInstanceOf(XSSearch::class, $xsSearch);
    }
}
