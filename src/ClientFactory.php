<?php

namespace Taxusorg\XunSearchLaravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\Exceptions\ConfigError;
use Taxusorg\XunSearchLaravel\Libs\IniBuilder;
use XS as XunSearch;

class ClientFactory
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
     * @return mixed|string
     */
    public function getKeyName()
    {
        return $this->doc_key_name;
    }

    /**
     * @param XunSearchModelInterface|Searchable|Model $model
     * @param bool $soft_delete
     * @return Client
     */
    public function buildClient(XunSearchModelInterface $model, bool $soft_delete = false): Client
    {
        return new Client($this->buildXS($model, $soft_delete));
    }

    /**
     * @param Searchable|Model $model
     */
    protected function buildXS(Model $model, bool $soft_delete): XunSearch
    {
        return new XunSearch($this->buildIni($model->searchableAs(), $model, $soft_delete));
    }

    /**
     * Build ini.
     *
     * @param string $app_name
     * @param XunSearchModelInterface|Searchable|Model $model
     * @param bool $soft_delete
     * @return string
     */
    protected function buildIni(string $app_name, XunSearchModelInterface $model, bool $soft_delete): string
    {
        $ini = IniBuilder::buildIni($app_name, $this->getKeyName(), $model, $this->config);

        if ($soft_delete)
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