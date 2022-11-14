<?php

namespace Taxusorg\XunSearchLaravel;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\Exceptions\ConfigError;
use Taxusorg\XunSearchLaravel\Libs\IniBuilder;
use Taxusorg\XunSearchLaravel\Libs\CheckSoftDeletes;
use XS as XunSearch;

class ClientFactory
{
    use CheckSoftDeletes;

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
     * @return mixed|string
     */
    public function getKeyName()
    {
        return $this->doc_key_name;
    }

    /**
     * @param XunSearchModelInterface|Searchable|Model $model
     * @return Client
     */
    public function buildClient(XunSearchModelInterface $model): Client
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
     * @param XunSearchModelInterface|Searchable|Model $model
     * @return string
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
        return IniBuilder::softDeleteField('__soft_deleted');
    }
}