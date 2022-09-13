<?php


namespace Taxusorg\XunSearchLaravel;


use Taxusorg\XunSearchLaravel\Engines\XunSearchEngine;
use XS;
use XSIndex;
use XSSearch;

/**
 * Class Builder
 * @mixin  \Laravel\Scout\Builder
 * @package Taxusorg\XunSearchLaravel
 */
class Builder
{
    protected function __construct()
    {
    }

    /**
     * @param bool $value
     * @return Builder
     * @see XSSearch::setFuzzy
     */
    public function setFuzzy($value = true)
    {
        $this->getXSSearch()->setFuzzy($value);

        return $this;
    }

    /**
     * @return XSSearch
     */
    public function getXSSearch()
    {
        return $this->getXSServer()->search;
    }

    /**
     * @return XSIndex
     */
    public function getXSIndex()
    {
        return $this->getXSServer()->index;
    }

    /**
     * @return XS
     */
    public function getXSServer()
    {
        $engine = $this->engine();

        if (! $engine instanceof XunSearchEngine) {
            throw new \Error('Laravel Scout engine mast be instanceof XunSearchEngine.');
        }

        return $engine->getXSServer($this);
    }
}