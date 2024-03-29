<?php

namespace Taxusorg\XunSearchLaravel;

use Illuminate\Contracts\Container\BindingResolutionException;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\Libs\Helper;
use XS;
use XSIndex;
use XSSearch;
use XSException;

/**
 * Trait XunSearchTrait
 * @package Taxusorg\XunSearchLaravel
 */
trait XunSearchTrait
{
    use Searchable;

    /**
     * @param  string|null  $query
     * @return XSSearch
     */
    public static function XSSearch(?string $query = null): XSSearch
    {
        return static::search()->getXSSearch()->setQuery($query);
    }

    /**
     * @return XSIndex
     */
    public static function XSIndex(): XSIndex
    {
        return static::search()->getXSIndex();
    }

    /**
     * @return XS|Client
     */
    public static function XS()
    {
        return static::search()->getXS();
    }

    /**
     * @return int
     */
    public static function XSTotal(): int
    {
        return static::search()->getXSTotal();
    }

    /**
     * @param  string  $query
     * @param  null  $callback
     * @return Builder
     */
    public static function search($query = '', $callback = null): Builder
    {
        return app(Builder::class, [
            'model' => new static,
            'query' => $query,
            'callback' => $callback,
            'softDelete' => static::usesSoftDelete() && config('scout.soft_delete', false),
        ]);
    }

    /**
     * 获取当前库内的全部同义词列表
     * $limit 为 0 则默认为 100
     *
     * @param  int  $limit
     * @param  int  $offset
     * @param  bool  $stemmed
     * @return array
     */
    public static function XSAllSynonyms(int $limit = 0, int $offset = 0, bool $stemmed = false): array
    {
        return static::XSSearch()->getAllSynonyms($limit, $offset, $stemmed);
    }

    /**
     * 分页获取当前库内的全部同义词列表
     *
     * @throws BindingResolutionException
     */
    public static function XSAllSynonymsPaginate(
        bool $stemmed = false,
        $perPage = null,
        $pageName = 'page',
        $page = null
    ) {
        return Helper::allSynonymsPaginate(static::XSSearch(), $stemmed, $perPage, $pageName, $page);
    }

    /**
     * @param  int  $limit
     * @param  int  $offset
     * @param  bool  $stemmed
     * @return array
     * @deprecated 使用 XSAllSynonyms 代替
     */
    public static function searchableAllSynonyms(int $limit = 0, int $offset = 0, bool $stemmed = false): array
    {
        return static::XSAllSynonyms($limit, $offset, $stemmed);
    }

    /**
     * 获取指定词汇的同义词列表
     *
     * @param  string  $term
     * @return string[]
     */
    public static function XSSynonyms(string $term): array
    {
        return static::XSSearch()->getSynonyms($term);
    }

    /**
     * @param  string  $term
     * @return string[]
     * @deprecated 使用 XSSynonyms 代替
     */
    public static function searchableSynonyms(string $term): array
    {
        return static::XSSynonyms($term);
    }

    /**
     * 获取热门搜索词列表
     * 最多 50 个
     *
     * @param  int  $limit
     * @param  string|'total'|'lastnum'|'currnum' $type
     * @return string[]
     * @throws XSException
     */
    public static function XSHotQuery(int $limit = 6, string $type = 'total'): array
    {
        return static::XSSearch()->getHotQuery($limit, $type);
    }

    /**
     * @param  int  $limit
     * @param  string|'total'|'lastnum'|'currnum' $type
     * @return string[]
     * @throws XSException
     * @deprecated 使用 XSHotQuery 代替
     */
    public static function searchableHotQuery(int $limit = 6, string $type = 'total'): array
    {
        return static::XSHotQuery($limit, $type);
    }

    /**
     * 获取相关搜索词列表
     *
     * @param  string  $query
     * @param  int  $limit
     * @return string[]
     * @throws XSException
     */
    public static function XSRelatedQuery(string $query, int $limit = 6): array
    {
        return static::XSSearch()->getRelatedQuery($query, $limit);
    }

    /**
     * @param  string  $query
     * @param  int  $limit
     * @return string[]
     * @throws XSException
     * @deprecated 使用 XSRelatedQuery 代替
     */
    public static function searchableRelatedQuery(string $query, int $limit = 6): array
    {
        return static::XSRelatedQuery($query, $limit);
    }

    /**
     * 获取展开的搜索词列表
     * 输入前缀，返回补全的搜索词，最多 20 个
     *
     * @param  string  $query
     * @param  int  $limit
     * @return string[]
     * @throws XSException
     */
    public static function XSExpandedQuery(string $query, int $limit = 10): array
    {
        return static::XSSearch()->getExpandedQuery($query, $limit);
    }

    /**
     * @param  string  $query
     * @param  int  $limit
     * @return string[]
     * @throws XSException
     * @deprecated 使用 XSExpandedQuery 代替
     */
    public static function searchableExpandedQuery(string $query, int $limit = 10): array
    {
        return static::XSExpandedQuery($query, $limit);
    }

    /**
     * 获取修正后的搜索词列表
     *
     * @param  string  $query
     * @return string[]
     * @throws XSException
     */
    public static function XSCorrectedQuery(string $query): array
    {
        return static::XSSearch()->getCorrectedQuery($query);
    }

    /**
     * 获取修正后的搜索词列表
     *
     * @param  string  $query
     * @return string[]
     * @throws XSException
     * @deprecated 使用 XSCorrectedQuery 代替
     */
    public static function searchableCorrectedQuery(string $query): array
    {
        return static::XSCorrectedQuery($query);
    }
}
