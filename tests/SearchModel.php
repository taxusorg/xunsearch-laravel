<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\Contracts\XunSearch;

class SearchModel extends Model implements XunSearch
{
    use Searchable;

    public function searchableUsing()
    {
        global $manager;

        return $manager->driver('xunsearch');
    }

    public function searchableFieldsType()
    {
        return [

        ];
    }
}
