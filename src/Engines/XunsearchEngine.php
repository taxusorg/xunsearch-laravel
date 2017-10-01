<?php
namespace Taxusorg\XunsearchLaravel\Engines;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Builder;

class XunsearchEngine extends Engine
{
    protected $xss = [];

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function update($models)
    {
        foreach ($models as $model) {
            $doc = new \XSDocument();
            $doc->setField('XSDocKey', $model->getKey());
            $doc->setField($model->getKeyName(), $model->getKey());
            $doc->setFields($model->toSearchableArray());
            $this->getXS($model)->index->update($doc);//dd($this->getXS($model));
        }
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
        foreach ($models as $model) {
            //$doc = new \XSDocument();
            //$doc->setField($model->getKeyName(), $model->getKey());
            //$doc->setFields($model->toSearchableArray());
            $this->getXS($model)->index->del($model->getKey());
        }
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return mixed
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder, array_filter([
            //'numericFilters' => $this->filters($builder),
            'hitsPerPage' => $builder->limit,
        ]));
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  int  $perPage
     * @param  int  $page
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        return $this->performSearch($builder, array_filter([
            //'numericFilters' => $this->filters($builder),
            'hitsPerPage' => $builder->limit ?: $perPage,
            'page' => $page - 1,
        ]));
    }

    protected function performSearch(Builder $builder, array $options = [])
    {
        $search = $this->getXS($builder->model)->search;

        if (isset($options['hitsPerPage'])) {
            if (isset($options['page'])) {
                $search->setLimit($options['hitsPerPage'], $options['hitsPerPage'] * $options['page']);
            }else{
                $search->setLimit($options['hitsPerPage']);
            }
        }

        return ['docs' => $search->search($builder->query), 'total' => $search->getLastCount()];
        /*if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                $algolia,
                $builder->query,
                $options
            );
        }*/
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param  mixed  $results
     * @return \Illuminate\Support\Collection
     */
    public function mapIds($results)
    {
        return collect($results['docs'])->pluck('XSDocKey')->values();
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  mixed  $results
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map($results, $model)
    {
        if ($results['total'] === 0) {
            return Collection::make();
        }

        $keys = collect($results['docs'])
            ->pluck($model->getKeyName())->values()->all();

        $models = $model->whereIn(
            $model->getQualifiedKeyName(), $keys
        )->get()->keyBy($model->getKeyName());

        if (method_exists($model, 'scoutFieldsType') && method_exists($model, 'scoutBodyResultField')) {
            $types = $model->scoutFieldsType();
            foreach($types as $field=>$field_type) {
                if (in_array('body', $field_type)) $body_field = $field;
            }
        }

        return Collection::make($results['docs'])->map(function ($doc) use ($model, $models, $body_field) {
            $key = $doc[$model->getKeyName()];

            if (isset($models[$key])) {
                if ($body_field) {
                    $search = $this->getXS($model)->search;
                    if ($model->scoutBodyResultField()) {
                        $models[$key][$model->scoutBodyResultField()] = $search->highlight($doc[$body_field]);
                    } else {
                        $models[$key][$body_field] = $search->highlight($doc[$body_field]);
                    }
                }
                return $models[$key];
            }
            return false;
        })->filter();
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param  mixed  $results
     * @return int
     */
    public function getTotalCount($results)
    {
        return $results['total'];
    }

    /**
     * Get XS
     */
    protected function getXS(Model $model)
    {
        $app_name = $model->searchableAs();

        if (isset($this->xss[$app_name]))
            return $this->xss[$app_name];

        return $this->xss[$app_name] = new \XS($this->buildIni($app_name, $model));
    }

    /**
     * build ini
     */
    protected function buildIni($app_name, Model $model)
    {
        $array = $model->toSearchableArray();

        $str =
            'project.name = '.$app_name. "\n".
            'project.default_charset = utf-8'. "\n".
            'server.index = 8383' . "\n".
            'server.search = 8384'. "\n".
            '';

        $types = [];
        if (method_exists($model, 'scoutFieldsType')) {
            $types = $model->scoutFieldsType();
        }

        //$casts = getCasts();
        $str .= "\n[".$model->getKeyName()."]\ntype = id\n";

        foreach ($array as $key=>$value) {
            $str .= "\n[$key]\n";
            if (array_key_exists($key, $types)) $str .= 'type = ' . $types[$key]['type'] . "\n";
        }

        return $str;
    }

}
