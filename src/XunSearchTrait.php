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
        (new static)->registerScoutMacros();
    }

    public function registerScoutMacros()
    {
        $this->registerScoutRangeSearch();
        $this->registerScoutFuzzy();
    }

    public function registerScoutRangeSearch()
    {
        Builder::macro('range', function ($word, $from, $to) {
            $this->ranges[$word]['from'] = $from;
            $this->ranges[$word]['to'] = $to;

            return $this;
        });
    }

    public function registerScoutFuzzy()
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
}