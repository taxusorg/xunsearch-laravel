<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Taxusorg\XunsearchLaravel\Engines\XunsearchEngine;

class XunsearchTest extends TestCase
{
    protected $xunsearchEngine;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->xunsearchEngine = new XunsearchEngine();
    }

    public function testEngine()
    {
    }
}