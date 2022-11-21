<?php
namespace Taxusorg\XunSearchLaravel;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\LazyCollection;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\Builder as XSBuilder;
use Taxusorg\XunSearchLaravel\Libs\CheckSoftDeletes;
use XSSearch;
use XSDocument as XunSearchDocument;
use XSException;

class XunSearchEngine extends Engine
{
    use CheckSoftDeletes;

    protected $clientFactory;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
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
        $index->openBuffer();

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
     * @return Results
     * @throws XSException
     */
    public function search(Builder $builder): Results
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
     * @return Results
     * @throws XSException
     */
    public function paginate(Builder $builder, $perPage, $page): Results
    {
        return $this->performSearch($builder, array_filter([
            'hitsPerPage' => $perPage,
            'page' => $page - 1,
        ]));
    }

    /**
     * @param Builder|XSBuilder $builder
     * @param array $options
     * @return Results
     * @throws XSException
     */
    protected function performSearch(Builder $builder, array $options = []): Results
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

        $docs = $search->search();
        $total = $search->getLastCount();

        return new Results($this, tap(clone $builder, function ($builder) {
            unset($builder->XSClient);
        }), $total, $docs);
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
        } else {
            if (count($builder->orders) == 1) {
                $search->setSort($builder->orders[0]['column'], $builder->orders[0]['direction'] == 'asc');
            } else {
                $search->setMultiSort(array_map(function ($item) {
                    return [$item['column'], $item['direction'] == 'asc'];
                }, $builder->orders));
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
     * @param Results $results
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
     * @param Results $results
     * @param Model|Searchable $model
     * @return Collection
     */
    public function map(Builder $builder, $results, $model): Collection
    {
        if (count($results['docs']) === 0) {
            return $model->newCollection();
        }

        $keys = $this->mapIds($results)->all();
        $keyPositions = array_flip($keys);

        return $model->getScoutModelsByIds(
            $builder, $keys
        )->filter(function ($model) use ($keys) {
            return in_array($model->getScoutKey(), $keys);
        })->sortBy(function ($model) use ($keyPositions) {
            return $keyPositions[$model->getScoutKey()];
        })->values();
    }

    /**
     * @param Builder $builder
     * @param Results $results
     * @param Model|Searchable $model
     * @return LazyCollection
     */
    public function lazyMap(Builder $builder, $results, $model): LazyCollection
    {
        if (count($results['docs']) === 0) {
            return LazyCollection::make($model->newCollection());
        }

        $keys = $this->mapIds($results)->all();
        $keyPositions = array_flip($keys);

        return $model->queryScoutModelsByIds(
            $builder, $keys
        )->cursor()->filter(function ($model) use ($keys) {
            return in_array($model->getScoutKey(), $keys);
        })->sortBy(function ($model) use ($keyPositions) {
            return $keyPositions[$model->getScoutKey()];
        })->values();
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
     * @throws Exception
     */
    public function createIndex($name, array $options = [])
    {
        throw new Exception('XunSearch indexes are created automatically upon adding objects.');
    }

    public function deleteIndex($name): bool
    {
        $client = $this->clientFactory->buildClientWithoutModel($name);

        $client->index->clean();

        return true;
    }

    /**
     * @return mixed|string
     */
    public function getKeyName()
    {
        return $this->clientFactory->getKeyName();
    }

    /**
     * @param Model $model
     * @return Client
     */
    public function buildClient(Model $model): Client
    {
        return $this->clientFactory->buildClient($model);
    }
}
