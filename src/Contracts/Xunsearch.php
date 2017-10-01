<?php
namespace Taxusorg\XunsearchLaravel\Contracts;

interface Xunsearch
{
    const XUNSEARCH_TYPE_STRING = 'string';
    const XUNSEARCH_TYPE_NUMERIC = 'numeric';
    const XUNSEARCH_TYPE_DATE = 'date';
    const XUNSEARCH_TYPE_ID = 'id';
    const XUNSEARCH_TYPE_TITLE = 'title';
    const XUNSEARCH_TYPE_BODY = 'body';

    const XUNSEARCH_INDEX_NONE = 'none';
    const XUNSEARCH_INDEX_SELF = 'self';
    const XUNSEARCH_INDEX_MIXED = 'mixed';
    const XUNSEARCH_INDEX_BOTH = 'both';

    const XUNSEARCH_TOKENIZER_DEFAULT = 'default';
    const XUNSEARCH_TOKENIZER_NONE = 'none';
    const XUNSEARCH_TOKENIZER_FULL = 'full';
    const XUNSEARCH_TOKENIZER_XLEN = 'xlen';
    const XUNSEARCH_TOKENIZER_XSTEP = 'xstep';
    const XUNSEARCH_TOKENIZER_SCWS = 'scws';
}