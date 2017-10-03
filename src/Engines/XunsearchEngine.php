<?php
namespace Taxusorg\XunSearchLaravel\Engines;

use \XS as XunSearch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Builder;
use Taxusorg\XunSearchLaravel\Contracts\XunSearch as XunSearchContract;

class XunSearchEngine extends Engine
{
    private $server_host = '127.0.0.1';
    private $server_index_port = 8383;
    private $server_search_port = 8384;
    private $default_charset = 'utf-8';

    protected $doc_key_name = 'XSDocKey';

    protected $xss = [];

    public function __construct($config = [])
    {
        if (isset($config['server_host'])) {
            $this->server_host = $config['server_host'];
        }
        if (isset($config['server_index_port'])) {
            $this->server_index_port = $config['server_index_port'];
        }
        if (isset($config['server_search_port'])) {
            $this->server_search_port = $config['server_search_port'];
        }
    }

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
            $doc->setField($this->doc_key_name, $model->getKey());
            $doc->setField($model->getKeyName(), $model->getKey());
            $doc->setFields($model->toSearchableArray());
            $this->getXS($model)->index->update($doc);
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
        if (!$models->isEmpty())
            $this->getXS($models->first())->index->del($models->pluck($models->first()->getKeyName())->toArray());
    }

    /**
     * Delete all data.
     *
     * @param Model $model
     */
    public function clean(Model $model)
    {
        $this->getXS($model)->index->clean();
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
        $search = $this->getXS($builder->model)->search;

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
                $this->buildQuery($builder),
                $options
            );
        }

        return ['docs' => $search->search($this->buildQuery($builder)), 'total' => $search->getLastCount()];
    }

    protected function buildQuery(Builder $builder)
    {
        $wheres = collect($builder->wheres)->map(function ($value, $key) {
            return $key.':'.$value;
        })->values();

        return trim($builder->query) . ' ' . $wheres->implode(' ');
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

        return Collection::make($results['docs'])->map(function ($doc) use ($model, $models) {
            $key = $doc[$model->getKeyName()];

            if (isset($models[$key])) {
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

        return $this->xss[$app_name] = new XunSearch($this->buildIni($app_name, $model));
    }

    /**
     * Build ini.
     */
    protected function buildIni($app_name, Model $model)
    {
        $str =
        'project.name = '.$app_name. "\n".
        'project.default_charset = ' . $this->default_charset . "\n".
        'server.index = ' . ($this->server_host ? $this->server_host . ':' : '') . $this->server_index_port . "\n".
        'server.search = ' . ($this->server_host ? $this->server_host . ':' : '') . $this->server_search_port . "\n".
        '';

        $str .= "\n[".$model->getKeyName()."]\ntype = id\n";

        if ($model instanceof XunSearchContract) {
            $types = $model->scoutFieldsType();

            foreach ($types as $key=>$value) {
                $str .= "\n[$key]\n";
                if (isset($types[$key]['type'])) $str .= 'type = ' . $types[$key]['type'] . "\n";
                if (isset($types[$key]['index'])) $str .= 'index = ' . $types[$key]['index'] . "\n";
                if (isset($types[$key]['tokenizer'])) {
                    if (in_array($types[$key]['tokenizer'], [
                        XunSearchContract::XUNSEARCH_TOKENIZER_FULL,
                        XunSearchContract::XUNSEARCH_TOKENIZER_NONE,
                    ])) {
                        $str .= 'tokenizer = ' . $types[$key]['tokenizer'] . "\n";
                    } else {
                        $str .= 'tokenizer = ' .$types[$key]['tokenizer'].
                            '('.$types[$key]['tokenizer_value'].')' . "\n";
                    }
                } elseif (isset($types[$key]['tokenizer_value'])) {
                    $str .= 'tokenizer = ' .XunSearchContract::XUNSEARCH_TOKENIZER_SCWS.
                        '('.$types[$key]['tokenizer_value'].')' . "\n";
                }
            }
        }

        return $str;
    }
}
