<?php


namespace Taxusorg\XunSearchLaravel;


use Illuminate\Database\Eloquent\Model;
use XSDocument as XunSearchDocument;

/**
 * search result Document
 *
 * @package Taxusorg\XunSearchLaravel
 */
class Document
{
    /**
     * @var Model|null
     */
    protected $model;

    /**
     * @var XunSearchDocument
     */
    protected $xsDocument;

    public function __construct(XunSearchDocument $document, $model = null)
    {
        $this->xsDocument = $document;

        $this->model = $model;
    }
}