<?php
namespace Taxusorg\XunSearchLaravel;

trait XunSearchTrait
{
    public static function cleanSearchable()
    {
        $self = new static();
        $self->searchableUsing()->clean($self);
    }
}