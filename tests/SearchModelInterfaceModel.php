<?php

namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\XunSearchModelInterface;
use Taxusorg\XunSearchLaravel\XunSearchTrait;

class SearchModelInterfaceModel extends Model implements XunSearchModelInterface
{
    use Searchable, XunSearchTrait;

    protected $fillable = ['title', 'subtitle', 'content'];

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
            ],
            'subtitle' => [
                'index' => self::XUNSEARCH_INDEX_BOTH,
            ],
            'content' => [
                'type' => self::XUNSEARCH_TYPE_BODY,
            ],
        ];
    }
}
