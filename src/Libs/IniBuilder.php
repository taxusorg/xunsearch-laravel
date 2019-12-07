<?php

namespace Taxusorg\XunSearchLaravel\Libs;

use Taxusorg\XunSearchLaravel\XunSearchModelInterface;

/**
 * @internal
 * @package \Taxusorg\XunSearchLaravel
 */
class IniBuilder
{
    /**
     * @param string $app_name
     * @param string $doc_key_name
     * @param array $config
     * @param XunSearchModelInterface $model
     * @return string
     * @throws \Error
     */
    public static function buildIni(string $app_name, string $doc_key_name, XunSearchModelInterface $model, array $config)
    {
        $str = static::header($app_name, $config);
        $str .= static::keyField($doc_key_name);
        $str .= static::fields($model->xunSearchFieldsType(), $doc_key_name);

        return $str;
    }

    /**
     * @param string $app_name
     * @param array $config
     * @return string
     */
    public static function header(string $app_name, array $config)
    {
        $str =
            'project.name = ' . $app_name . PHP_EOL.
            'project.default_charset = ' . $config['default_charset'] . PHP_EOL.
            'server.index = ' . ($config['server_index_host'] ? $config['server_index_host'] . ':' :
                ($config['server_host'] ? $config['server_host'] . ':' : '')) . $config['server_index_port'] . PHP_EOL.
            'server.search = ' . ($config['server_search_host'] ? $config['server_search_host'] . ':' :
                ($config['server_host'] ? $config['server_host'] . ':' : '')) . $config['server_search_port'] . PHP_EOL.
            '';

        return $str;
    }

    /**
     * @param string $doc_key_name
     * @return string
     * @throws \Error
     */
    public static function keyField(string $doc_key_name)
    {
        return static::filed($doc_key_name, [
            'type' => XunSearchModelInterface::XUNSEARCH_TYPE_ID,
        ]);
    }

    /**
     * @param string $name
     * @return string
     * @throws \Error
     */
    public static function softDeleteField(string $name)
    {
        return static::filed($name, [
            'type' => XunSearchModelInterface::XUNSEARCH_TYPE_NUMERIC,
            'index' => XunSearchModelInterface::XUNSEARCH_INDEX_SELF,
            'tokenizer' => XunSearchModelInterface::XUNSEARCH_TOKENIZER_FULL,
        ]);
    }

    /**
     * @param array $types
     * @param string $doc_key_name
     * @return string
     * @throws \Error
     */
    public static function fields(array $types, string $doc_key_name)
    {
        $str = '';

        $count_title = $count_body = 0;
        foreach ($types as $key=>$type) {
            if ($key == $doc_key_name)
                throw new \Error("The field '$key' as same as XunSearch doc_key_name in Engine.
                You can change XunSearch doc_key_name in app->config['xunsearch']['doc_key_name']");

            if (isset($type['type'])) {
                if ($type['type'] == XunSearchModelInterface::XUNSEARCH_TYPE_ID)
                    throw new \Error("The field '$key' must not be 'id'.
                    Type 'id' has be setting as default in engine.
                    Set the type as 'numeric' or 'string' in Model->xunSearchFieldsType(),
                    if you want it to be use in Searchable");

                if ($type['type'] == XunSearchModelInterface::XUNSEARCH_TYPE_TITLE)
                    $count_title++;

                elseif ($type['type'] == XunSearchModelInterface::XUNSEARCH_TYPE_BODY)
                    $count_body++;
            }

            if ($count_title > 1 || $count_body > 1)
                throw new \Error("'title' or 'body' can only be set once.
                Fix it in Model->xunSearchFieldsType()");

            $str .= static::filed($key, $type);
        }

        return $str;
    }

    /**
     * @param $key
     * @param array $type
     * @return string
     * @throws \Error
     */
    public static function filed($key, array $type)
    {
        $str = PHP_EOL . "[$key]" . PHP_EOL;

        if (isset($type['type']) && static::checkType($type['type']))
            $str .= 'type = ' . $type['type'] . PHP_EOL;

        if (isset($type['index']) && static::checkIndex($type['index']))
            $str .= 'index = ' . $type['index'] . PHP_EOL;

        if (isset($type['tokenizer'])) {
            if (static::checkTokenizerWithoutValue($type['tokenizer'])) {
                $str .= 'tokenizer = ' . $type['tokenizer'] . PHP_EOL;
            } elseif (static::checkTokenizerWithValue($type['tokenizer'], $type['tokenizer_value'] ?? null)) {
                $str .= 'tokenizer = ' .$type['tokenizer'].
                    '('. ($type['tokenizer_value'] ?? null) .')' . PHP_EOL;
            } else {
                throw new \Error("The field '$key' has wrong tokenizer or tokenizer_value.");
            }
        } elseif (isset($type['tokenizer_value']) && (int) $type['tokenizer_value'] > 0) {
            $str .= 'tokenizer = ' .XunSearchModelInterface::XUNSEARCH_TOKENIZER_SCWS.
                '('. (int) $type['tokenizer_value'] .')' . PHP_EOL;
        }

        return $str;
    }

    /**
     * @param string $type
     * @return bool
     */
    public static function checkType(string $type)
    {
        return in_array($type, [
            XunSearchModelInterface::XUNSEARCH_TYPE_ID,
            XunSearchModelInterface::XUNSEARCH_TYPE_TITLE,
            XunSearchModelInterface::XUNSEARCH_TYPE_BODY,
            XunSearchModelInterface::XUNSEARCH_TYPE_NUMERIC,
            XunSearchModelInterface::XUNSEARCH_TYPE_STRING,
            XunSearchModelInterface::XUNSEARCH_TYPE_DATE,
        ]);
    }

    /**
     * @param string $index
     * @return bool
     */
    public static function checkIndex(string $index)
    {
        return in_array($index, [
            XunSearchModelInterface::XUNSEARCH_INDEX_NONE,
            XunSearchModelInterface::XUNSEARCH_INDEX_SELF,
            XunSearchModelInterface::XUNSEARCH_INDEX_BOTH,
            XunSearchModelInterface::XUNSEARCH_INDEX_MIXED,
        ]);
    }

    /**
     * @param string $tokenizer
     * @return bool
     */
    public static function checkTokenizerWithoutValue(string $tokenizer)
    {
        return in_array($tokenizer, [
            XunSearchModelInterface::XUNSEARCH_TOKENIZER_DEFAULT,
            XunSearchModelInterface::XUNSEARCH_TOKENIZER_NONE,
            XunSearchModelInterface::XUNSEARCH_TOKENIZER_FULL,
        ]);
    }

    /**
     * @param string $tokenizer
     * @param $value
     * @return bool
     */
    public static function checkTokenizerWithValue(string $tokenizer, $value = null)
    {
        return static::checkTokenizerWithNumeric($tokenizer, $value)
            || static::checkTokenizerWithString($tokenizer, $value);
    }

    /**
     * @param string $tokenizer
     * @param null $value
     * @return bool
     */
    public static function checkTokenizerWithNumeric(string $tokenizer, $value = null)
    {
        return in_array($tokenizer, [
            XunSearchModelInterface::XUNSEARCH_TOKENIZER_XLEN,
            XunSearchModelInterface::XUNSEARCH_TOKENIZER_XSTEP,
            XunSearchModelInterface::XUNSEARCH_TOKENIZER_SCWS,
        ]) && (is_numeric($value) || is_null($value));
    }

    /**
     * @param string $tokenizer
     * @param null $value
     * @return bool
     */
    public static function checkTokenizerWithString(string $tokenizer, $value = null)
    {
        return $tokenizer == XunSearchModelInterface::XUNSEARCH_TOKENIZER_SPLIT
            && (is_string($value) || is_null($value));
    }
}
