<?php
namespace Taxusorg\XunSearchLaravel;

interface XunSearchModelInterface extends FieldType
{
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
     *      'date' => [
     *          'type'=>self::XUNSEARCH_TYPE_DATE,
     *          'index'=>self::XUNSEARCH_INDEX_NONE,
     *      ],
     * ]
     */
    public function xunSearchFieldsType();
}
