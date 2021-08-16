<?php
namespace Taxusorg\XunSearchLaravel\Engines;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Builder;
use XS as XunSearch;
use XSDocument as XunSearchDocument;
use Taxusorg\XunSearchLaravel\XunSearchModelInterface;
use Taxusorg\XunSearchLaravel\Libs\IniBuilder;

class XunSearchEngine extends Engine
{
    protected $config = [
        'server_host' => 'localhost',
        'server_index_host' => null,
        'server_index_port' => 8383,
        'server_search_host' => null,
        'server_search_port' => 8384,
        'default_charset' => 'utf-8'
    ];

    protected $doc_key_name = 'xun_search_object_id';

    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);

        if (isset($config['doc_key_name']) && $config['doc_key_name']) {
            $this->doc_key_name = $config['doc_key_name'];
        }
    }

    /**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     * @throws
     */
    public function update($models)
    {
        if ($models->isEmpty()) return;

        if ($this->checkUsesSoftDelete($models->first()))
            $models = $this->addSoftDeleteData($models);

        $index = $this->buildXS($models->first())->getIndex();
        $index->openBuffer();
        foreach ($models as $model) {
            $doc = new XunSearchDocument();
            $doc->setField($this->doc_key_name, $model->getScoutKey());
            $doc->setFields(array_merge(
                $model->toSearchableArray(), $model->scoutMetadata()
            ));
            $index->update($doc);
        }
        $index->closeBuffer();
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
        if ($models->isEmpty()) return;

        $this->buildXS($models->first())->index->openBuffer()->del(
            $models->map(function ($model) {
                return $model->getScoutKey();
            })->values()->all()
        )->closeBuffer();
    }

    /**
     * Delete all data.
     *
     * @param Model $model
     */
    public function flush($model)
    {
        $this->buildXS($model)->index->clean();
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
            'hitsPerPage' => $perPage,
            'page' => $page - 1,
        ]));
    }

    protected function performSearch(Builder $builder, array $options = [])
    {
        $search = $this->getXSServer($builder)->search;

        if (isset($options['hitsPerPage'])) {
            if (isset($options['page']) && $options['page'] > 0) {
                $search->setLimit($options['hitsPerPage'], $options['hitsPerPage'] * $options['page']);
            }else{
                $search->setLimit($options['hitsPerPage']);
            }
        }

        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                $search,
                $builder->query,
                $options
            );
        }

        $search->setQuery($this->buildQuery($builder));

        return ['docs' => $search->search(), 'total' => $search->getLastCount()];
    }

    protected function buildQuery(Builder $builder)
    {
        $query = $builder->query;

        collect($builder->wheres)->map(function ($value, $key) use (&$query) {
            $query .= ' ' . $key.':'.$value;
        });

        return $query;
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param  mixed  $results
     * @return \Illuminate\Support\Collection
     */
    public function mapIds($results)
    {
        return collect($results['docs'])->pluck($this->doc_key_name)->values();
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  mixed  $results
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map(Builder $builder, $results, $model)
    {
        if (count($results['docs']) === 0) {
            return Collection::make();
        }

        $keys = collect($results['docs'])->pluck($this->doc_key_name)->values()->all();
        $objectIdPositions = array_flip($keys);

        return $model->getScoutModelsByIds(
            $builder, $keys
        )->filter(function ($model) use ($keys) {
            return in_array($model->getScoutKey(), $keys);
        })->sortBy(function ($model) use ($objectIdPositions) {
            return $objectIdPositions[$model->getScoutKey()];
        })->values();
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
     * @param Builder $builder
     * @return XunSearch
     */
    public function getXSServer(Builder $builder)
    {
        if (! isset($builder->xunSearchServer) || ! $builder->xunSearchServer instanceof XunSearch) {
            $builder->xunSearchServer = $this->buildXS($builder->model);
        }

        return $builder->xunSearchServer;
    }

    /**
     * Get Xun Search Object.
     *
     * @param Model $model
     * @return XunSearch
     * @throws
     */
    protected function buildXS(Model $model)
    {
        return new XunSearch($this->buildIni($model->searchableAs(), $model));
    }

    /**
     * Build ini.
     *
     * @param string $app_name
     * @param XunSearchModelInterface|Model $model
     * @return string
     * @throws \Error
     */
    protected function buildIni(string $app_name, XunSearchModelInterface $model)
    {
        $ini = IniBuilder::buildIni($app_name, $this->doc_key_name, $model, $this->config);

        if ($this->checkUsesSoftDelete($model))
            $ini .= $this->softDeleteFieldIni();

        return $ini;
    }

    /**
     * @return string
     * @throws \Error
     */
    protected function softDeleteFieldIni()
    {
        // soft delete field named '__soft_deleted'. see \Laravel\Scout\Builder
        return IniBuilder::softDeleteField('__soft_deleted');
    }

    protected function addSoftDeleteData($models)
    {
        $models->each->pushSoftDeleteMetadata();

        return $models;
    }

    /**
     * @param $model
     * @return bool
     */
    protected function checkUsesSoftDelete($model)
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model))
             && config('scout.soft_delete', false);
    }
}
