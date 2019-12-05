<?php

namespace Taxusorg\XunSearchLaravel;

use Laravel\Scout\Builder;
use Taxusorg\XunSearchLaravel\Engines\XunSearchEngine;
use XS;

class XunSearchBuilderMixin
{
    protected function setFuzzy()
    {
        return function (bool $fuzzy = true) {
            $this->getXunSearchBuilder()->setFuzzy($fuzzy);

            return $this;
        };
    }

    protected function fuzzy()
    {
        return function (bool $fuzzy = true) {
            return $this->setFuzzy($fuzzy);
        };
    }

    protected function setCutOff()
    {
        return function (int $percent, $weight = 0) {
            $this->getXunSearchBuilder()->setCutOff($percent, $weight);

            return $this;
        };
    }

    protected function cutOff()
    {
        return function (int $percent, $weight = 0) {
            return $this->setCutOff($percent, $weight);
        };
    }

    protected function setRequireMatchedTerm()
    {
        return function (bool $v) {
            $this->getXunSearchBuilder()->setRequireMatchedTerm($v);

            return $this;
        };
    }

    protected function requireMatchedTerm()
    {
        return function (bool $v) {
            return $this->setRequireMatchedTerm($v);
        };
    }

    protected function setWeightingScheme()
    {
        return function (int $v) {
            $this->getXunSearchBuilder()->setWeightingScheme($v);

            return $this;
        };
    }

    protected function weightingScheme()
    {
        return function (int $v) {
            return $this->setWeightingScheme($v);
        };
    }

    protected function setAutoSynonyms()
    {
        return function (bool $v) {
            $this->getXunSearchBuilder()->setAutoSynonyms($v);

            return $this;
        };
    }

    protected function autoSynonyms()
    {
        return function (bool $v) {
            return $this->setAutoSynonyms($v);
        };
    }

    protected function setSynonymScale()
    {
        return function (float $v) {
            $this->getXunSearchBuilder()->setSynonymScale($v);

            return $this;
        };
    }

    protected function synonymScale()
    {
        return function (float $v) {
            return $this->setSynonymScale($v);
        };
    }

    protected function setGeodistSort()
    {
        return function (array $fields, bool $reverse = false, bool $relevance_first = false) {
            $this->getXunSearchBuilder()->setGeodistSort($fields, $reverse, $relevance_first);

            return $this;
        };
    }

    protected function geodistSort()
    {
        return function (array $fields, bool $reverse = false, bool $relevance_first = false) {
            return $this->setGeodistSort($fields, $reverse, $relevance_first);
        };
    }

    protected function setMultiSort()
    {
        return function ($fields, $reverse = false, $relevance_first = false) {
            $this->getXunSearchBuilder()->setMultiSort($fields, $reverse, $relevance_first);

            return $this;
        };
    }

    protected function multiSort()
    {
        return function ($fields, $reverse = false, $relevance_first = false) {
            return $this->setMultiSort($fields, $reverse, $relevance_first);
        };
    }

    protected function setSort()
    {
        return function ($field, $asc = false, $relevance_first = false) {
            $this->getXunSearchBuilder()->setSort($field, $asc, $relevance_first);

            return $this;
        };
    }

    protected function sort()
    {
        return function ($field, $asc = false, $relevance_first = false) {
            return $this->setSort($field, $asc, $relevance_first);
        };
    }

    protected function setDocOrder()
    {
        return function ($asc = false) {
            $this->getXunSearchBuilder()->setDocOrder($asc);

            return $this;
        };
    }

    protected function docOrder()
    {
        return function ($asc = false) {
            return $this->setDocOrder($asc);
        };
    }

    protected function setCollapse()
    {
        return function ($field, $num = 1) {
            $this->getXunSearchBuilder()->setCollapse($field, $num);

            return $this;
        };
    }

    protected function collapse()
    {
        return function ($field, $num = 1) {
            return $this->setCollapse($field, $num);
        };
    }

    protected function range()
    {
        return function ($field, $from, $to) {
            $this->getXunSearchBuilder()->addRange($field, $from, $to);

            return $this;
        };
    }

    protected function getXunSearchBuilder()
    {
        return function () {
            return $this->getXunSearchServer()->search;
        };
    }

    protected function getXunSearchServer()
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
