<?php

namespace Taxusorg\XunSearchLaravel\Libs;

use Illuminate\Database\Eloquent\SoftDeletes;

trait CheckSoftDeletes
{
    protected function addSoftDeleteData($models)
    {
        $models->each->pushSoftDeleteMetadata();

        return $models;
    }

    /**
     * @param $model
     * @return bool
     */
    protected function checkUsesSoftDelete($model): bool
    {
        return $model::usesSoftDelete()
            && config('scout.soft_delete', false);
    }
}