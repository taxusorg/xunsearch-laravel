<?php

namespace Taxusorg\XunSearchLaravel;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use IteratorAggregate;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use Traversable;
use XSDocument;

class Results implements IteratorAggregate, ArrayAccess
{
    protected $engine;
    protected $builder;
    protected $total = 0;
    protected $docs = [];

    /**
     * @param Engine $engine
     * @param Builder $builder
     * @param int $total
     * @param XSDocument[] $docs
     */
    public function __construct(Engine $engine, Builder $builder, int $total, array $docs = [])
    {
        $this->engine = $engine;
        $this->builder = $builder;
        $this->total = $total;
        $this->docs = $docs;
    }

    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getIds(): \Illuminate\Support\Collection
    {
        return $this->engine->mapIds($this);
    }

    /**
     * @param Closure|null $callback
     * @return Collection
     */
    public function getModels(?Closure $callback = null): Collection
    {
        $bak = null;
        if ($callback) {
            $bak = $this->builder->queryCallback;

            $this->builder->query($callback);
        }

        return tap(
            $this->engine->map($this->builder, $this, $this->builder->model),
            function () use ($bak) {
                if ($bak !== null) $this->builder->query($bak);
            }
        );
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getArray(): array
    {
        return $this->docs;
    }

    /**
     * @return ArrayIterator<XSDocument>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->docs);
    }

    public function offsetExists($offset): bool
    {
        if ($offset == 'models') return true;
        if ($offset == 'ids') return true;

        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        if ($offset == 'models') return $this->getModels();
        if ($offset == 'ids') return $this->getIds();

        return $this->{$offset} ?? null;
    }

    /**
     * @throws Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception('disable');
    }

    /**
     * @throws Exception
     */
    public function offsetUnset($offset)
    {
        throw new Exception('disable');
    }
}