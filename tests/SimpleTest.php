<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testSimple()
    {
        $builder = SearchInterfaceModel::search('te')->fuzzy()->range('id',1,200);

        $result = $builder->get();

        print_r($result);

        $this->assertTrue(true);
    }
}
