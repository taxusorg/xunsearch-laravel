<?php
namespace Taxusorg\XunSearchLaravel;

use Laravel\Scout\Builder;

trait XunSearchTrait
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootXunSearchTrait()
    {
        (new static)->registerXunSearchBuilderMacros();
    }

    public function registerXunSearchBuilderMacros()
    {
        Builder::mixin(new BuilderMixin());
    }
}
