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
        global $manager;

        $builder = SearchInterfaceModel::search('test');

        $result = $builder->get();

        $this->assertTrue(true);
    }
}