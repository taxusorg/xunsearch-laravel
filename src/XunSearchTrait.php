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
        Builder::macro('fuzzy', function () {
            $this->xunsearch_parameters['fuzzy'] = true;

            return $this;
        });
    }

    public static function cleanSearchable()
    {
        $self = new static();
        $self->searchableUsing()->clean($self);
    }
}