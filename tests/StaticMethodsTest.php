<?php

namespace Tests;

use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;
use Taxusorg\XunSearchLaravel\Client;
use Tests\Src\SearchModelWithTrait;
use XSIndex;
use XSSearch;

class StaticMethodsTest extends TestCase
{
    use WithApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootApplication();
    }

    public function testXS()
    {
        $client = SearchModelWithTrait::XS();
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testXSSearch()
    {
        $text = Str::random();
        $this->searchMock->expects(self::once())
            ->method('setQuery')
            ->with($text)
            ->willReturnSelf();
        $this->assertInstanceOf(
            XSSearch::class,
            SearchModelWithTrait::XSSearch($text)
        );
    }

    public function testXSIndex()
    {
        $this->assertInstanceOf(
            XSIndex::class,
            SearchModelWithTrait::XSIndex()
        );
    }

    public function testXSTotal()
    {
        $total = rand();
        $this->searchMock->expects(self::once())
            ->method('getDbTotal')
            ->willReturn($total);
        $this->assertEquals($total, SearchModelWithTrait::XSTotal());
    }

    public function testXSAllSynonyms()
    {
        $array = [
            Str::random(),
            Str::random(),
            Str::random(),
        ];

        $this->searchMock->expects(self::once())
            ->method('setQuery')
            ->willReturnSelf();
        $this->searchMock->expects(self::once())
            ->method('getAllSynonyms')
            ->willReturn($array);
        $this->assertEquals($array, SearchModelWithTrait::XSAllSynonyms());
    }

    public function testXSSynonyms()
    {
        $array = [
            Str::random(),
            Str::random(),
            Str::random(),
        ];
        $text = Str::random();

        $this->searchMock->expects(self::once())
            ->method('setQuery')
            ->willReturnSelf();
        $this->searchMock->expects(self::once())
            ->method('getSynonyms')
            ->with($text)
            ->willReturn($array);

        $this->assertEquals($array, SearchModelWithTrait::XSSynonyms($text));
    }

    public function testXSHotQuery()
    {
        $array = [
            Str::random(),
            Str::random(),
            Str::random(),
        ];

        $this->searchMock->expects(self::once())
            ->method('setQuery')
            ->willReturnSelf();
        $this->searchMock->expects(self::once())
            ->method('getHotQuery')
            ->willReturn($array);

        $this->assertEquals($array, SearchModelWithTrait::XSHotQuery());
    }

    public function testXSRelatedQuery()
    {
        $array = [
            Str::random(),
            Str::random(),
            Str::random(),
        ];
        $text = Str::random();

        $this->searchMock->expects(self::once())
            ->method('setQuery')
            ->willReturnSelf();
        $this->searchMock->expects(self::once())
            ->method('getRelatedQuery')
            ->with($text)
            ->willReturn($array);

        $this->assertEquals($array, SearchModelWithTrait::XSRelatedQuery($text));
    }

    public function testXSExpandedQuery()
    {
        $array = [
            Str::random(),
            Str::random(),
            Str::random(),
        ];
        $text = Str::random();

        $this->searchMock->expects(self::once())
            ->method('setQuery')
            ->willReturnSelf();
        $this->searchMock->expects(self::once())
            ->method('getExpandedQuery')
            ->with($text)
            ->willReturn($array);

        $this->assertEquals($array, SearchModelWithTrait::XSExpandedQuery($text));
    }

    public function testXSCorrectedQuery()
    {
        $array = [
            Str::random(),
            Str::random(),
            Str::random(),
        ];
        $text = Str::random();

        $this->searchMock->expects(self::once())
            ->method('setQuery')
            ->willReturnSelf();
        $this->searchMock->expects(self::once())
            ->method('getCorrectedQuery')
            ->with($text)
            ->willReturn($array);

        $this->assertEquals($array, SearchModelWithTrait::XSCorrectedQuery($text));
    }
}