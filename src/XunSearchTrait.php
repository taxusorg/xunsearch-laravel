<?php
namespace Taxusorg\XunSearchLaravel;

use Laravel\Scout\Builder;

trait XunSearchTrait
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootXunSearchTrait()
    {
        (new static)->registerXunSearchMacros();
    }

    public function registerXunSearchMacros()
    {
        $this->registerSearchableRangeSearch();
        $this->registerSearchableFuzzy();
    }

    public function registerSearchableRangeSearch()
    {
        Builder::macro('range', function ($word, $from, $to) {
            $this->ranges[$word]['from'] = $from;
            $this->ranges[$word]['to'] = $to;

            return $this;
        });
    }

    public function registerSearchableFuzzy()
    {
        Builder::macro('fuzzy', function () {
            $this->fuzzy = true;

            return $this;
        });
    }

    public static function cleanSearchable()
    {
        $self = new static();
        $self->searchableUsing()->clean($self);
    }

    public function getSearchableKeyName()
    {
        return null;
    }

    public function getSearchableKey()
    {
        return $this->getKey();
    }
}