<?php
namespace Taxusorg\XunSearchLaravel;

interface XunSearchModelInterface
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
    const XUNSEARCH_TOKENIZER_SPLIT = 'split';
    const XUNSEARCH_TOKENIZER_XLEN = 'xlen';
    const XUNSEARCH_TOKENIZER_XSTEP = 'xstep';
    const XUNSEARCH_TOKENIZER_SCWS = 'scws';

    /**
     * Setting Fields.
     *
     * @see http://www.xunsearch.com/doc/php/guide/ini.guide
     * @return array
     * @example
     * return [
     *      'id' => [
     *          'type'=>self::XUNSEARCH_TYPE_NUMERIC,
     *      ],
     *      'title' => [
     *          'type'=>self::XUNSEARCH_TYPE_TITLE,
     *      ],
     *      'body' => [
     *          'type'=>self::XUNSEARCH_TYPE_BODY,
     *      ],
     *      'field' => [
     *          'tokenizer'=>self::XUNSEARCH_TOKENIZER_XLEN,
     *          'tokenizer_value'=>2,
     *      ],
     *      'data' => [
     *          'type'=>self::XUNSEARCH_TYPE_DATE,
     *          'index'=>self::XUNSEARCH_INDEX_NONE,
     *      ],
     * ]
     */
    public function xunSearchFieldsType();
}
