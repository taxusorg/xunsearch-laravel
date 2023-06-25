<?php
/**
 * @noinspection PhpUnused
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */

namespace Taxusorg\XunSearchLaravel;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\LazyCollection;
use IteratorAggregate;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use XSDocument;

class Results implements IteratorAggregate, ArrayAccess, Arrayable
{
    protected $engine;
    protected $builder;
    protected $total = 0;
    protected $docs = [];

    /**
     * @param  Engine  $engine
     * @param  Builder  $builder
     * @param  int  $total
     * @param  XSDocument[]  $docs
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

    public function getQueryCallback(): ?Closure
    {
        return $this->builder->queryCallback;
    }

    public function query(?Closure $closure = null): Results
    {
        $this->builder->query($closure);

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getIds(): \Illuminate\Support\Collection
    {
        return $this->engine->mapIds($this);
    }

    /**
     * @param  Closure|null  $callback
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
                $this->builder->query($bak);
            }
        );
    }

    public function getLazyModels(?Closure $callback = null): LazyCollection
    {
        $bak = null;
        if ($callback) {
            $bak = $this->builder->queryCallback;

            $this->builder->query($callback);
        }

        return tap(
            $this->engine->lazyMap($this->builder, $this, $this->builder->model),
            function () use ($bak) {
                $this->builder->query($bak);
            }
        );
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function toArray(): array
    {
        return $this->docs;
    }

    /**
     * @deprecated use toArray
     */
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
        if ($offset == 'models') {
            return true;
        }
        if ($offset == 'ids') {
            return true;
        }
        if ($offset == 'queryCallback') {
            return (bool) $this->getQueryCallback();
        }

        return isset($this->{$offset});
    }

    /**
     * @noinspection PhpLanguageLevelInspection
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if ($offset == 'models') {
            return $this->getModels();
        }
        if ($offset == 'ids') {
            return $this->getIds();
        }
        if ($offset == 'queryCallback') {
            return $this->getQueryCallback();
        }

        return $this->{$offset} ?? null;
    }

    /**
     * @throws Exception
     * @noinspection PhpLanguageLevelInspection
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new Exception('disable');
    }

    /**
     * @throws Exception
     * @noinspection PhpLanguageLevelInspection
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        throw new Exception('disable');
    }
}