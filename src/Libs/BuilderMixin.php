<?php

namespace Taxusorg\XunSearchLaravel\Libs;

use Laravel\Scout\Builder;
use ReflectionException;
use ReflectionMethod;
use Taxusorg\XunSearchLaravel\Engines\XunSearchEngine;

/**
 * Class BuilderMixin
 * @property Builder $this
 * @package Taxusorg\XunSearchLaravel\Libs
 */
class BuilderMixin
{

    private static $builderClass = \Taxusorg\XunSearchLaravel\Builder::class;
    private static $builder;
    private static $reflection;

    /**
     * @param $name
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    private static function method($name)
    {
        if (! self::$reflection) {
            self::$reflection = new \ReflectionClass(self::$builderClass);
            self::$builder = self::$reflection->newInstanceWithoutConstructor();
        }

        return self::$reflection->getMethod($name)->getClosure(self::$builder);
    }

    /**
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    protected function test()
    {
        $reflection = new \ReflectionClass(self::$builderClass);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $argv = func_get_args();
        $t = $this;
        return self::method('setFuzzy');
    }

    /**
     * @return \Closure
     */
    protected function setFuzzy()
    {
        return function (bool $fuzzy = true) {
            $this->xunSearch()->setFuzzy($fuzzy);

            return $this;
        };
    }

    protected function fuzzy()
    {
        return $this->setFuzzy();
    }

    protected function setCutOff()
    {
        return function (int $percent, $weight = 0) {
            $this->xunSearch()->setCutOff($percent, $weight);

            return $this;
        };
    }

    protected function cutOff()
    {
        return $this->setCutOff();
    }

    protected function setRequireMatchedTerm()
    {
        return function (bool $v) {
            $this->xunSearch()->setRequireMatchedTerm($v);

            return $this;
        };
    }

    protected function requireMatchedTerm()
    {
        return $this->setRequireMatchedTerm();
    }

    protected function setWeightingScheme()
    {
        return function (int $v) {
            $this->xunSearch()->setWeightingScheme($v);

            return $this;
        };
    }

    protected function weightingScheme()
    {
        return $this->setWeightingScheme();
    }

    protected function setAutoSynonyms()
    {
        return function (bool $v) {
            $this->xunSearch()->setAutoSynonyms($v);

            return $this;
        };
    }

    protected function autoSynonyms()
    {
        return $this->setAutoSynonyms();
    }

    protected function setSynonymScale()
    {
        return function (float $v) {
            $this->xunSearch()->setSynonymScale($v);

            return $this;
        };
    }

    protected function synonymScale()
    {
        return $this->setSynonymScale();
    }

    protected function setGeodistSort()
    {
        return function (array $fields, bool $reverse = false, bool $relevance_first = false) {
            $this->xunSearch()->setGeodistSort($fields, $reverse, $relevance_first);

            return $this;
        };
    }

    protected function geodistSort()
    {
        return $this->setGeodistSort();
    }

    protected function setMultiSort()
    {
        return function ($fields, $reverse = false, $relevance_first = false) {
            $this->xunSearch()->setMultiSort($fields, $reverse, $relevance_first);

            return $this;
        };
    }

    protected function multiSort()
    {
        return $this->setMultiSort();
    }

    protected function setSort()
    {
        return function ($field, $asc = false, $relevance_first = false) {
            $this->xunSearch()->setSort($field, $asc, $relevance_first);

            return $this;
        };
    }

    protected function sort()
    {
        return $this->setSort();
    }

    protected function setDocOrder()
    {
        return function ($asc = false) {
            $this->xunSearch()->setDocOrder($asc);

            return $this;
        };
    }

    protected function docOrder()
    {
        return $this->setDocOrder();
    }

    protected function setCollapse()
    {
        return function ($field, $num = 1) {
            $this->xunSearch()->setCollapse($field, $num);

            return $this;
        };
    }

    protected function collapse()
    {
        return $this->setCollapse();
    }

    protected function addRange()
    {
        return function ($field, $from, $to) {
            $this->xunSearch()->addRange($field, $from, $to);

            return $this;
        };
    }

    protected function range()
    {
        return $this->addRange();
    }

    protected function addWeight()
    {
        return function ($field, $term, $weight = 1) {
            $this->xunSearch()->addWeight($field, $term, $weight);

            return $this;
        };
    }

    protected function weight()
    {
        return $this->addWeight();
    }

    /**
     * @return \Closure|$this
     */
    protected function setFacets()
    {
        return function ($field, $exact = false) {
            $this->xunSearch()->setFacets($field, $exact);

            return $this;
        };
    }

    protected function facets()
    {
        return $this->setFacets();
    }

    protected function getRelatedQuery()
    {
        return function ($limit = 6) {
            return $this->xunSearch()->getRelatedQuery(null, $limit);
        };
    }

    protected function getCorrectedQuery()
    {
        return function () {
            return $this->xunSearch()->getCorrectedQuery();
        };
    }

    /**
     * @return \Closure|\XSSearch
     */
    protected function xunSearch()
    {
        return function () {
            return $this->xunSearchServer()->search;
        };
    }

    /**
     * @return \Closure|\XSIndex
     */
    protected function xunSearchIndex()
    {
        return function () {
            return $this->xunSearchServer()->index;
        };
    }

    /**
     * @return \Closure|\XS
     */
    protected function xunSearchServer()
    {
        return function () {
            $engine = $this->engine();

            if (! $engine instanceof XunSearchEngine) {
                throw new \Error('Laravel Scout engine mast be instanceof XunSearchEngine.');
            }

            return $engine->getXSServer($this);
        };
    }
}
