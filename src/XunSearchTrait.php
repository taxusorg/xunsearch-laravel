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
        $this->registerScoutOrWhere();
        $this->registerScoutAndSearch();
        $this->registerScoutOrSearch();
        $this->registerScoutExactSearch();
        $this->registerScoutOrExactSearch();
        $this->registerScoutNearSearch();
        $this->registerScoutOrNearSearch();
        $this->registerScoutRangeSearch();
        $this->registerScoutFuzzy();
    }

    public function registerScoutOrWhere()
    {
        Builder::macro('orWhere', function ($field, $value) {
            $this->or_wheres[$field] = $value;

            return $this;
        });
    }

    public function registerScoutAndSearch()
    {
        Builder::macro('andSearch', function ($value) {
            $this->query .= ' AND ' . $value;

            return $this;
        });
    }

    public function registerScoutOrSearch()
    {
        Builder::macro('orSearch', function ($value) {
            $this->query .= ' OR ' .$value;

            return $this;
        });
    }

    public function registerScoutExactSearch()
    {
        Builder::macro('exactSearch', function ($value) {
            $this->query .= ' AND "' .$value. '"';

            return $this;
        });
    }

    public function registerScoutOrExactSearch()
    {
        Builder::macro('orExactSearch', function ($value) {
            $this->query .= ' OR "' .$value. '"';

            return $this;
        });
    }

    public function registerScoutNearSearch()
    {
        Builder::macro('nearSearch', function ($v1, $v2, $span = 10, $ordering = false) {
            if (!$ordering) {
                $this->query .= " $v1 NEAR/$span $v2";
            }else{
                $this->query .= " $v1 ADJ/$span $v2";
            }

            return $this;
        });
    }

    public function registerScoutOrNearSearch()
    {
        Builder::macro('orNearSearch', function ($v1, $v2, $span = 10, $ordering = false) {
            if (!$ordering) {
                $this->query .= " OR $v1 NEAR/$span $v2";
            }else{
                $this->query .= " OR $v1 ADJ/$span $v2";
            }

            return $this;
        });
    }

    public function registerScoutRangeSearch()
    {
        Builder::macro('rangeSearch', function ($word, $from, $to) {
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

    public static function exactSearch($query, $callback = null)
    {
        return self::search('"'.$query.'"', $callback);
    }

    public static function nearSearch($query1, $query2, $span = 10, $ordering = false, $callback = null)
    {
        if (!$ordering) {
            return self::search("$query1 NEAR/$span $query2", $callback);
        }else{
            return self::search("$query1 ADJ/$span $query2", $callback);
        }
    }

    public static function cleanSearchable()
    {
        $self = new static();
        $self->searchableUsing()->clean($self);
    }
}