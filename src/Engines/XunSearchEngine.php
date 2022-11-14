<?php
namespace Taxusorg\XunSearchLaravel\Engines;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\Builder as XSBuilder;
use Taxusorg\XunSearchLaravel\Client;
use Taxusorg\XunSearchLaravel\Exceptions\ConfigError;
use Taxusorg\XunSearchLaravel\XunSearchModelInterface;
use XS as XunSearch;
use XSSearch;
use XSDocument as XunSearchDocument;
use Taxusorg\XunSearchLaravel\Libs\IniBuilder;
use XSException;

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
     * @param Collection $models
     * @return void
     * @throws
     */
    public function update($models)
    {
        if ($models->isEmpty()) return;

        if ($this->checkUsesSoftDelete($models->first()))
            $models = $this->addSoftDeleteData($models);

        $index = $this->buildClient($models->first())->index;

        foreach ($models as $model) {
            $doc = new XunSearchDocument();
            $doc->setField($this->getKeyName(), $model->getScoutKey());
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
     * @param Collection $models
     * @return void
     */
    public function delete($models)
    {
        if (!$models->isEmpty())
            $this->buildClient($models->first())->index->del(
                $models->map(function ($model) {
                    return $model->getScoutKey();
                })->values()->all()
            );
    }

    /**
     * Delete all data.
     *
     * @param Model $model
     */
    public function flush($model)
    {
        $this->buildClient($model)->index->clean();
    }

    /**
     * Perform the given search on the engine.
     *
     * @param Builder|XSBuilder $builder
     * @return mixed
     * @throws XSException
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
     * @param Builder|XSBuilder $builder
     * @param int $perPage
     * @param int $page
     * @return mixed
     * @throws XSException
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        return $this->performSearch($builder, array_filter([
            'hitsPerPage' => $perPage,
            'page' => $page - 1,
        ]));
    }

    /**
     * @param Builder|XSBuilder $builder
     * @param array $options
     * @return array|mixed
     * @throws XSException
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        $search = $builder->getXSSearch();

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

        $this->setSearchParams($builder, $search);
        $search->setQuery($this->buildQuery($builder));

        return [
            'docs' => $search->search(),
            'total' => $search->getLastCount(),
        ];
    }

    /**
     * @param Builder|XSBuilder $builder
     * @param XSSearch $search
     * @throws XSException
     */
    protected function setSearchParams(Builder $builder, XSSearch $search)
    {
        isset($builder->fuzzy) && $search->setFuzzy(!! $builder->fuzzy);
        isset($builder->require_matched_term) && $search->setRequireMatchedTerm(!! $builder->require_matched_term);
        isset($builder->weighting_scheme) && $search->setWeightingScheme($builder->weighting_scheme);
        isset($builder->auto_synonyms) && $search->setAutoSynonyms(!! $builder->auto_synonyms);
        isset($builder->synonym_scale) && $search->setSynonymScale($builder->synonym_scale);
        isset($builder->doc_order) && $search->setDocOrder(!! $builder->doc_order);
        isset($builder->scws_multi) && $search->setScwsMulti($builder->scws_multi);

        isset($builder->cut_off) && is_array($builder->cut_off) &&
        $search->setCutOff($builder->cut_off['percent'], $builder->cut_off['weight']);

        isset($builder->collapse) && is_array($builder->collapse) &&
        $search->setCollapse($builder->collapse['field'], $builder->collapse['num']);

        if (isset($builder->multi_sort) && is_array($builder->multi_sort)) {
            if (is_string($builder->multi_sort['fields'])) {
                $search->setSort(
                    $builder->multi_sort['fields'],
                    ! $builder->multi_sort['reverse'],
                    $builder->multi_sort['relevance_first']
                );
            } else {
                $search->setMultiSort(
                    $builder->multi_sort['fields'],
                    $builder->multi_sort['reverse'],
                    $builder->multi_sort['relevance_first']
                );
            }
        }

        isset($builder->range) && is_array($builder->range) &&
        array_walk($builder->range, function ($values, $field) use ($search) {
            $search->addRange($field, $values['from'], $values['to']);
        });

        isset($builder->weight) && is_array($builder->weight) &&
        array_walk($builder->weight, function ($values, $field) use ($search) {
            $search->addWeight($field, $values['term'], $values['weight']);
        });
    }

    protected function buildQuery(Builder $builder): string
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
    public function mapIds($results): \Illuminate\Support\Collection
    {
        return collect($results['docs'])->pluck($this->getKeyName())->values();
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param Builder $builder
     * @param  mixed  $results
     * @param Model|Searchable  $model
     * @return Collection
     */
    public function map(Builder $builder, $results, $model): Collection
    {
        if (count($results['docs']) === 0) {
            return Collection::make();
        }

        $keys = collect($results['docs'])->pluck($this->getKeyName())->values()->all();

        return $model->getScoutModelsByIds(
            $builder, $keys
        )->keyBy(function ($model) {
            /**
             * @var Searchable $model
             */
            return $model->getScoutKey();
        });

        return Collection::make($results['docs'])->map(function ($doc) use ($models, $model) {
            $key = $doc[$this->getKeyName()];

            if (isset($models[$key])) {
                return $models[$key];
            }

            return false;
        })->filter()->values();
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param  mixed  $results
     * @return int
     */
    public function getTotalCount($results): int
    {
        return $results['total'];
    }

    /**
     * @return mixed|string
     */
    public function getKeyName()
    {
        return $this->doc_key_name;
    }

    /**
     * @param Model $model
     * @return Client
     */
    public function buildClient(Model $model): Client
    {
        return new Client($this->buildXS($model));
    }

    /**
     * @param Searchable|Model $model
     */
    protected function buildXS(Model $model): XunSearch
    {
        return new XunSearch($this->buildIni($model->searchableAs(), $model));
    }

    /**
     * Build ini.
     *
     * @param string $app_name
     * @param XunSearchModelInterface|Model|Searchable $model
     * @return string
     * @throws ConfigError
     */
    protected function buildIni(string $app_name, XunSearchModelInterface $model): string
    {
        $ini = IniBuilder::buildIni($app_name, $this->getKeyName(), $model, $this->config);

        if ($this->checkUsesSoftDelete($model))
            $ini .= $this->softDeleteFieldIni();

        return $ini;
    }

    /**
     * @return string
     * @throws ConfigError
     */
    protected function softDeleteFieldIni(): string
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
    protected function checkUsesSoftDelete($model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model))
             && config('scout.soft_delete', false);
    }
}
