<?php

namespace Tests\Src;

use Taxusorg\XunSearchLaravel\XunSearchTrait;

class SearchModelWithTrait extends SearchModel
{
    use XunSearchTrait;

    protected $table = 'search_models';
}