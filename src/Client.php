<?php

namespace Taxusorg\XunSearchLaravel;

use XS;

/**
 * @mixin XS
 */
class Client
{
    /**
     * @var XS $xs
     */
    private $xs;

    public function __construct($ini)
    {
        if ($ini instanceof XS) {
            $this->xs = $ini;
        } else {
            $this->xs = new XS($ini);
        }

    }

    public function __destruct()
    {
        $this->xs->__destruct();
    }

    public function getXS(): XS
    {
        return $this->xs;
    }

    public function __call($name, $arguments)
    {
        return $this->xs->{$name}(...$arguments);
    }

    public function __get($name)
    {
        return $this->xs->{$name};
    }

    public function __set($name, $value)
    {
        $this->xs->{$name} = $value;
    }
}