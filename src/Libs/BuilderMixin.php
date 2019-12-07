<?php

namespace Taxusorg\XunSearchLaravel\Libs;

use Taxusorg\XunSearchLaravel\Engines\XunSearchEngine;

class BuilderMixin
{
    protected function setFuzzy()
    {
        return function (bool $fuzzy = true) {
            $this->xunSearch()->setFuzzy($fuzzy);

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
            $this->xunSearch()->setCutOff($percent, $weight);

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
            $this->xunSearch()->setRequireMatchedTerm($v);

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
            $this->xunSearch()->setWeightingScheme($v);

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
            $this->xunSearch()->setAutoSynonyms($v);

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
            $this->xunSearch()->setSynonymScale($v);

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
            $this->xunSearch()->setGeodistSort($fields, $reverse, $relevance_first);

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
            $this->xunSearch()->setMultiSort($fields, $reverse, $relevance_first);

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
            $this->xunSearch()->setSort($field, $asc, $relevance_first);

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
            $this->xunSearch()->setDocOrder($asc);

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
            $this->xunSearch()->setCollapse($field, $num);

            return $this;
        };
    }

    protected function collapse()
    {
        return function ($field, $num = 1) {
            return $this->setCollapse($field, $num);
        };
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
        return function ($field, $from, $to) {
            return $this->addRange($field, $from, $to);
        };
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
        return function ($field, $term, $weight = 1) {
            return $this->addWeight($field, $term, $weight);
        };
    }

    protected function setFacets()
    {
        return function ($field, $exact = false) {
            $this->xunSearch()->setFacets($field, $exact);

            return $this;
        };
    }

    protected function facets()
    {
        return function ($field, $exact = false) {
            return $this->setFacets($field, $exact);
        };
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

    protected function xunSearch()
    {
        return function () {
            return $this->xunSearchServer()->search;
        };
    }

    protected function xunSearchIndex()
    {
        return function () {
            return $this->xunSearchServer()->index;
        };
    }

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
