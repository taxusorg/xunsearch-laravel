<?php

namespace Taxusorg\XunSearchLaravel\Libs;

use Taxusorg\XunSearchLaravel\Exceptions\ConfigError as Error;
use Taxusorg\XunSearchLaravel\FieldType;
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
     * @param XunSearchModelInterface|null $model
     * @param array $config
     * @return string
     */
    public static function buildIni(string $app_name, string $doc_key_name, array $config, ?XunSearchModelInterface $model)
    {
        $str = static::header($app_name, $config);
        $str .= static::keyField($doc_key_name);
        if ($model)
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
     * @throws Error
     */
    public static function keyField(string $doc_key_name)
    {
        return static::filed($doc_key_name, [
            'type' => FieldType::XUNSEARCH_TYPE_ID,
        ]);
    }

    /**
     * @param string $name
     * @return string
     * @throws Error
     */
    public static function softDeleteField(string $name)
    {
        return static::filed($name, [
            'type' => FieldType::XUNSEARCH_TYPE_NUMERIC,
            'index' => FieldType::XUNSEARCH_INDEX_SELF,
            'tokenizer' => FieldType::XUNSEARCH_TOKENIZER_FULL,
        ]);
    }

    /**
     * @param array $types
     * @param string $doc_key_name
     * @return string
     * @throws Error
     */
    public static function fields(array $types, string $doc_key_name)
    {
        $str = '';

        $count_title = $count_body = 0;
        foreach ($types as $key=>$type) {
            if ($key == $doc_key_name)
                throw new Error("Field '$key' as same as XunSearch document key name in Engine.
                You can change document key prefix in app->config['xunsearch']['doc_key_name']");

            if (isset($type['type'])) {
                if ($type['type'] == FieldType::XUNSEARCH_TYPE_ID)
                    throw new Error("Field '$key' must not be type '" . FieldType::XUNSEARCH_TYPE_ID . "'(FieldType::XUNSEARCH_TYPE_ID).
                    Type 'id' be set as default in engine.
                    Set the type as 'numeric', 'string' or something else in Model->xunSearchFieldsType()
                    if you want it to be use in Searchable");

                if ($type['type'] == FieldType::XUNSEARCH_TYPE_TITLE)
                    $count_title++;

                elseif ($type['type'] == FieldType::XUNSEARCH_TYPE_BODY)
                    $count_body++;
            }

            if ($count_title > 1 || $count_body > 1)
                throw new Error("Field type 'title'(FieldType::XUNSEARCH_TYPE_TITLE) or 'body'(FieldType::XUNSEARCH_TYPE_BODY) can only be set once.
                Fix it in Model->xunSearchFieldsType()");

            $str .= static::filed($key, $type);
        }

        return $str;
    }

    /**
     * @param $key
     * @param array $type
     * @return string
     * @throws Error
     */
    public static function filed($key, array $type)
    {
        try {
            $str = PHP_EOL . "[$key]" . PHP_EOL;

            if (isset($type['type']) && static::checkType($type['type'], true))
                $str .= 'type = ' . $type['type'] . PHP_EOL;

            if (isset($type['index']) && static::checkIndex($type['index'], true))
                $str .= 'index = ' . $type['index'] . PHP_EOL;

            if (isset($type['tokenizer']) && static::checkTokenizer($type['tokenizer'], true)) {
                if (static::isTokenizerWithoutValue($type['tokenizer'])) {
                    $str .= 'tokenizer = ' . $type['tokenizer'] . PHP_EOL;
                } elseif (static::checkTokenizerWithValue($type['tokenizer'], $type['tokenizer_value'] ?? null, true)) {
                    $str .= 'tokenizer = ' .$type['tokenizer'].
                        '('. ($type['tokenizer_value'] ?? null) .')' . PHP_EOL;
                }
            } elseif (isset($type['tokenizer_value']) && (int) $type['tokenizer_value'] > 0) {
                $str .= 'tokenizer = ' .FieldType::XUNSEARCH_TOKENIZER_SCWS.
                    '('. (int) $type['tokenizer_value'] .')' . PHP_EOL;
            }

            return $str;
        } catch (Error $error) {
            throw new Error("Config field [{$key}] error. " . $error->getMessage(), $error->getCode(), $error);
        }
    }

    /**
     * @param string $type
     * @param bool $throw
     * @return bool
     */
    public static function checkType(string $type, bool $throw = false)
    {
        if (in_array($type, [
            FieldType::XUNSEARCH_TYPE_ID,
            FieldType::XUNSEARCH_TYPE_TITLE,
            FieldType::XUNSEARCH_TYPE_BODY,
            FieldType::XUNSEARCH_TYPE_NUMERIC,
            FieldType::XUNSEARCH_TYPE_STRING,
            FieldType::XUNSEARCH_TYPE_DATE,
        ])) return true;


        if ($throw) throw new Error("Unknown field type [{$type}]");

        return false;
    }

    /**
     * @param string $index
     * @param bool $throw
     * @return bool
     */
    public static function checkIndex(string $index, bool $throw = false)
    {
        if (in_array($index, [
            FieldType::XUNSEARCH_INDEX_NONE,
            FieldType::XUNSEARCH_INDEX_SELF,
            FieldType::XUNSEARCH_INDEX_BOTH,
            FieldType::XUNSEARCH_INDEX_MIXED,
        ])) return true;


        if ($throw) throw new Error("Unknown field index [{$index}]");

        return false;
    }

    /**
     * @param string $tokenizer
     * @param bool $throw
     * @return bool
     */
    public static function checkTokenizer(string $tokenizer, bool $throw = false)
    {
        if (in_array($tokenizer, [
            FieldType::XUNSEARCH_TOKENIZER_DEFAULT,
            FieldType::XUNSEARCH_TOKENIZER_NONE,
            FieldType::XUNSEARCH_TOKENIZER_FULL,

            FieldType::XUNSEARCH_TOKENIZER_XLEN,
            FieldType::XUNSEARCH_TOKENIZER_XSTEP,
            FieldType::XUNSEARCH_TOKENIZER_SCWS,

            FieldType::XUNSEARCH_TOKENIZER_SPLIT,
        ])) return true;


        if ($throw) throw new Error("Unknown field tokenizer [{$tokenizer}]");

        return false;
    }

    /**
     * @param string $tokenizer
     * @return bool
     */
    public static function isTokenizerWithoutValue(string $tokenizer)
    {
        return in_array($tokenizer, [
            FieldType::XUNSEARCH_TOKENIZER_DEFAULT,
            FieldType::XUNSEARCH_TOKENIZER_NONE,
            FieldType::XUNSEARCH_TOKENIZER_FULL,
        ]);
    }

    /**
     * @param string $tokenizer
     * @param $value
     * @param bool $throw
     * @return bool
     */
    public static function checkTokenizerWithValue(string $tokenizer, $value = null, bool $throw = false)
    {
        return static::checkTokenizerWithNumeric($tokenizer, $value, $throw)
            || static::checkTokenizerWithString($tokenizer, $value, $throw);
    }

    /**
     * @param string $tokenizer
     * @param null $value
     * @param bool $throw
     * @return bool
     */
    public static function checkTokenizerWithNumeric(string $tokenizer, $value = null, bool $throw = false)
    {
        if ( in_array($tokenizer, [
            FieldType::XUNSEARCH_TOKENIZER_XLEN,
            FieldType::XUNSEARCH_TOKENIZER_XSTEP,
            FieldType::XUNSEARCH_TOKENIZER_SCWS,
        ]) && (is_numeric($value) || is_null($value))) {
            return true;
        }

        if ($throw) throw new Error("value of tokenizer [{$tokenizer}] mast be numeric or null.");

        return false;
    }

    /**
     * @param string $tokenizer
     * @param null $value
     * @param bool $throw
     * @return bool
     */
    public static function checkTokenizerWithString(string $tokenizer, $value = null, bool $throw = false)
    {
        if ( $tokenizer == FieldType::XUNSEARCH_TOKENIZER_SPLIT
            && (is_string($value) || is_null($value))) {
            return true;
        }

        if ($throw) throw new Error("value of tokenizer [{$tokenizer}] mast be string or null.");

        return false;
    }
}
