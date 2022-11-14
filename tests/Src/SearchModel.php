<?php

namespace Tests\Src;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\XunSearchModelInterface;

/**
 * Class SearchModelInterfaceModel
 * @method static \Taxusorg\XunSearchLaravel\Builder search($query = '', $callback = null)
 * @package Tests
 */
class SearchModel extends Model implements XunSearchModelInterface
{
    use Searchable;

    protected $fillable = ['title', 'subtitle', 'content'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('test_scope', function(Builder $builder) {
            $builder->where('age', '>', 200);
        });
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
