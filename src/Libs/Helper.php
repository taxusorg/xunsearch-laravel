<?php

namespace Taxusorg\XunSearchLaravel\Libs;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pagination\Paginator;
use XSSearch;

class Helper
{
    /**
     * @param  XSSearch  $search
     * @param  bool  $stemmed
     * @param  int|null  $perPage
     * @param  string  $pageName
     * @param  int|null  $page
     * @return Closure|mixed|object|null
     * @throws BindingResolutionException
     */
    public static function allSynonymsPaginate(
        XSSearch $search,
        bool $stemmed = false,
        int $perPage = null,
        string $pageName = 'page',
        int $page = null
    ) {
        $currentPage = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: 15;
        if ($perPage < 1) {
            $perPage = 100;
        }
        $options = [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ];

        $offset = $currentPage < 2 ? 0 : ($currentPage - 1) * $perPage;
        $items = $search->getAllSynonyms($perPage, $offset, $stemmed);

        return Container::getInstance()->makeWith(Paginator::class, compact(
            'items', 'perPage', 'currentPage', 'options'
        ));
    }
}