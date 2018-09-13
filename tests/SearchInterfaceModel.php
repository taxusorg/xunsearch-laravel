<?php

namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\Contracts\XunSearchInterface;

class SearchInterfaceModel extends Model implements XunSearchInterface
{
    use Searchable;

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('test_scope', function(Builder $builder) {
            $builder->where('age', '>', 200);
        });
    }

    public function searchableUsing()
    {
        global $manager;

        return $manager->driver('xunsearch');
    }

    public function xunSearchFieldsType()
    {
        return [
            'id' => [
                'type' => self::XUNSEARCH_TYPE_NUMERIC,
            ],
            'title' => [
                'type' => self::XUNSEARCH_TYPE_TITLE,
                'tokenizer' => self::XUNSEARCH_TOKENIZER_SPLIT,
                'tokenizer_value' => 'a',
            ],
            'subtitle' => [
                'index' => self::XUNSEARCH_INDEX_BOTH,
            ],
            'content' => [
                'type' => self::XUNSEARCH_TYPE_BODY,
            ],
            'category_id' => [
                'type' => self::XUNSEARCH_TYPE_NUMERIC,
                'index' => self::XUNSEARCH_INDEX_SELF
            ],
            'created_at' => [
                'type' => self::XUNSEARCH_TYPE_DATE,
                'index' => self::XUNSEARCH_INDEX_NONE,
            ],
            'updated_at' => [
                'type' => self::XUNSEARCH_TYPE_DATE,
                'index' => self::XUNSEARCH_INDEX_NONE,
            ],
        ];
    }
}
