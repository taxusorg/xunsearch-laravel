<?php

namespace Tests;

use Taxusorg\XunSearchLaravel\XunSearchTrait;

class SearchModelWithTrait extends SearchModel
{
    use XunSearchTrait;

    protected $table = 'search_models';

    public function searchableUsing()
    {
        return parent::searchableUsing();
    }
}