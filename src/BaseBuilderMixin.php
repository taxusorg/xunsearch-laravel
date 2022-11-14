<?php

namespace Taxusorg\XunSearchLaravel;

use Closure;
use Taxusorg\XunSearchLaravel\Engines\XunSearchEngine;
use XS;
use XSIndex;
use XSSearch;

/**
 * @mixin \Laravel\Scout\Builder
 */
class BaseBuilderMixin
{
    public function getXSTotal()
    {
        return function () {
            return $this->getXSSearch()->getDbTotal();
        };
    }

    public function getXSSearch(): Closure
    {
        return function () {
            return $this->getXS()->search;
        };
    }

    public function getXSIndex(): Closure
    {
        return function () {
            return $this->getXS()->index;
        };
    }

    /**
     * @return Closure
     */
    public function getXS(): Closure
    {
        return function () {
            if (isset($this->XSClient) && ($this->XSClient instanceof Client || $this->XSClient instanceof XS)) {
                return $this->XSClient;
            }

            $engine = $this->engine();

            if (! $engine instanceof XunSearchEngine) {
                throw new \Error('Laravel Scout engine mast be instanceof XunSearchEngine.');
            }

            return $this->XSClient = $engine->buildClient($this->model);
        };
    }
}