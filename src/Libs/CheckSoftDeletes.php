<?php

namespace Taxusorg\XunSearchLaravel\Libs;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

trait CheckSoftDeletes
{
    protected function addSoftDeleteData($models)
    {
        $models->each->pushSoftDeleteMetadata();

        return $models;
    }

    /**
     * @param Searchable|Model $model
     * @return bool
     */
    protected function checkUsesSoftDelete($model): bool
    {
        return $model::usesSoftDelete()
            && config('scout.soft_delete', false);
    }
}