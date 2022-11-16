<?php

namespace Tests;

use Laravel\Scout\Builder as BaseBuilder;
use PHPUnit\Framework\TestCase;
use Taxusorg\XunSearchLaravel\Builder;
use Taxusorg\XunSearchLaravel\Client;
use Tests\Src\SearchModel;
use Tests\Src\SearchModelWithTrait;

class SimpleTest extends TestCase
{
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
}
