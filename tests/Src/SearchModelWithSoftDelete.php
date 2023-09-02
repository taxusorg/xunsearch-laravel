<?php

namespace Tests\Src;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\XunSearchModelInterface;

/**
 * Class SearchModelInterfaceModel
 * @method static \Taxusorg\XunSearchLaravel\Builder search($query = '', $callback = null)
 * @package Tests
 */
class SearchModelWithSoftDelete extends Model implements XunSearchModelInterface
{
    use Searchable;
    use SoftDeletes;

    protected $fillable = ['title', 'subtitle', 'content'];
    protected $dateFormat = DATE_W3C;

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
