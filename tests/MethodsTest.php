<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Src\SearchModelWithTrait;

class MethodsTest extends TestCase
{
    use WithApplication;

    /**
     * @throws
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->bootApplication();
        $this->searchMock
            ->expects(self::once())
            ->method('search')
            ->willReturn([]);
        $this->searchMock
            ->expects(self::once())
            ->method('getLastCount')
            ->willReturn(0);
    }

    public function testSetFuzzy()
    {
        $this->searchMock
            ->expects($this->once())
            ->method('setFuzzy')
            ->with(true);

        SearchModelWithTrait::search('test')
            ->setFuzzy(false)
            ->setFuzzy()
            ->raw();
    }

    public function testSetCutOff()
    {
        $num1 = rand(0, 100);
        $num2 = rand(1, 255) / 10;

        $this->searchMock
            ->expects($this->once())
            ->method('setCutOff')
            ->with($num1, $num2);

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
            ->with(true);

        SearchModelWithTrait::search('test')
            ->setRequireMatchedTerm(false)
            ->setRequireMatchedTerm()
            ->raw();
    }

    public function testSetWeightingScheme()
    {
        $int = rand(0, 2);

        $this->searchMock
            ->expects($this->once())
            ->method('setWeightingScheme')
            ->with($int);

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
            ->with(true);

        SearchModelWithTrait::search('test')
            ->setAutoSynonyms(false)
            ->setAutoSynonyms()
            ->raw();
    }

    public function testSetSynonymScale()
    {
        $float = rand(1, 255) / 100;

        $this->searchMock
            ->expects($this->once())
            ->method('setSynonymScale')
            ->with($float);

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
            ->with(false);

        SearchModelWithTrait::search('test')
            ->setDocOrder(true)
            ->setDocOrder()
            ->raw();
    }

    public function testSetCollapse()
    {
        $this->searchMock
            ->expects($this->once())
            ->method('setCollapse')
            ->with('id', 10);

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
            ->with($int);

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
