<?php
namespace Taxusorg\XunSearchLaravel;

use Laravel\Scout\Builder;
use Taxusorg\XunSearchLaravel\Libs\BuilderMixin;

trait XunSearchTrait
{
    /**
     * Boot the trait.
     *
     * @return void
     * @throws \ReflectionException
     */
    public static function bootXunSearchTrait()
    {
        static::registerXunSearchBuilderMacros();
    }

    /**
     * @throws \ReflectionException
     */
    public static function registerXunSearchBuilderMacros()
    {
        Builder::mixin(new BuilderMixin());
    }

    /**
     * @param string|null $query
     * @return \XSSearch
     */
    public static function xunSearch($query = null)
    {
        return static::search()->xunSearch()->setQuery($query);
    }

    /**
     * @return \XSIndex
     */
    public static function xunSearchIndex()
    {
        return static::search()->xunSearchIndex();
    }

    /**
     * @return \XS
     */
    public static function xunSearchServer()
    {
        return static::search()->xunSearchServer();
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param bool $stemmed
     * @return array
     */
    public static function searchableAllSynonyms($limit = 0, $offset = 0, $stemmed = false)
    {
        return static::search()->xunSearch()->getAllSynonyms($limit, $offset, $stemmed);
    }

    /**
     * @param $term
     * @return array
     */
    public static function searchableSynonyms($term)
    {
        return static::search()->xunSearch()->getSynonyms($term);
    }

    /**
     * @param int $limit
     * @param string|'total'|'lastnum'|'currnum' $type
     * @return array
     */
    public static function searchableHotQuery($limit = 6, $type = 'total')
    {
        return static::search()->xunSearch()->getHotQuery($limit, $type);
    }

    /**
     * @param string $query
     * @param int $limit
     * @return array
     */
    public static function searchableRelatedQuery($query, $limit = 6)
    {
        return static::search()->xunSearch()->getRelatedQuery($query, $limit);
    }

    /**
     * @param string $query
     * @param int $limit
     * @return array
     */
    public static function searchableExpandedQuery($query, $limit = 10)
    {
        return static::search()->xunSearch()->getExpandedQuery($query, $limit);
    }

    /**
     * @param string $query
     * @return array
     */
    public static function searchableCorrectedQuery($query)
    {
        return static::search()->xunSearch()->getCorrectedQuery($query);
    }
}
