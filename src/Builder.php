<?php

namespace Taxusorg\XunSearchLaravel;

use Laravel\Scout\Builder as BaseBuilder;
use XS;
use XSIndex;
use XSSearch;

/**
 * @method XS|Client getXS
 * @method XSIndex getXSIndex
 * @method XSSearch getXSSearch
 * @method int getXSTotal
 * @method Results raw
 */
class Builder extends BaseBuilder
{
    public $fuzzy = null;
    public $cut_off = null;
    public $require_matched_term = null;
    public $weighting_scheme = null;
    public $auto_synonyms = null;
    public $synonym_scale = null;
    public $multi_sort = null;
    public $doc_order = null;
    public $collapse = null;
    public $range = [];
    public $weight = [];
    public $scws_multi = null;

    /**
     * 开启模糊搜索
     *
     * @param bool $fuzzy
     * @return Builder
     * @see XSSearch::setFuzzy
     */
    public function setFuzzy(bool $fuzzy = true): self
    {
        $this->fuzzy = $fuzzy;

        return $this;
    }

    /**
     * 设置”百分比“和”权重“剔除参数
     *
     * @param int $percent 剔除匹配百分比低于此值的文档, 值范围 0-100
     * @param float $weight 剔除权重低于此值的文档, 值范围 0.1-25.5, 0 表示不剔除
     * @return Builder
     * @see XSSearch::setCutOff()
     */
    public function setCutOff(int $percent, $weight = 0): self
    {
        $this->cut_off = compact('percent', 'weight');

        return $this;
    }

    /**
     * 是否在搜索结果文档中返回匹配词表
     * 使用 {@link XSDocument::matched} 获取
     *
     * @param bool $require_matched_term
     * @return Builder
     * @see XSSearch::setRequireMatchedTerm()
     */
    public function setRequireMatchedTerm(bool $require_matched_term = true): self
    {
        $this->require_matched_term = $require_matched_term;

        return $this;
    }

    /**
     * 设置检索匹配的权重方案
     * 支持三种权重方案: 0=BM25/1=Bool/2=Trad
     *
     * @param int $weighting_scheme 0 | 1 | 2
     * @return Builder
     */
    public function setWeightingScheme(int $weighting_scheme): self
    {
        $this->weighting_scheme = $weighting_scheme;

        return $this;
    }

    /**
     * 开启自动同义词搜索功能
     *
     * @param bool $auto_synonyms
     * @return $this
     */
    public function setAutoSynonyms(bool $auto_synonyms = true): self
    {
        $this->auto_synonyms = $auto_synonyms;

        return $this;
    }

    /**
     * 设置同义词搜索的权重比例
     *
     * @param float $synonym_scale 取值范围 0.01-2.55, 1 表示不调整
     * @return $this
     */
    public function setSynonymScale(float $synonym_scale): self
    {
        $this->synonym_scale = $synonym_scale;

        return $this;
    }

    /**
     * 设置多字段组合排序方式。该方法会覆盖 orderBy 方法
     * 当您需要根据多个字段的值按不同的方式综合排序时, 请使用这项
     *
     * @param bool[]|string[]|string $fields 序依据的字段数组, 以字段名称为键, true/false 为值表示正序或逆序
     * @param bool $reverse
     * @param bool $relevance_first
     * @return $this
     */
    public function setSort($fields, bool $reverse = false, bool $relevance_first = false): self
    {
        $this->multi_sort = compact('fields', 'reverse', 'relevance_first');

        return $this;
    }


    /**
     * 设置结果按索引入库先后排序
     *
     * @param bool $asc
     * @return $this
     */
    public function setDocOrder(bool $asc = false): self
    {
        $this->doc_order = $asc;

        return $this;
    }

    /**
     * 设置折叠搜索结果
     *
     * @param string|null $field
     * @param int $num
     * @return $this
     */
    public function setCollapse(?string $field, int $num = 1): self
    {
        $this->collapse = compact('field', 'num');

        return $this;
    }

    /**
     * 添加搜索过滤区间或范围
     *
     * @param string $field
     * @param $from
     * @param $to
     * @return $this
     */
    public function addRange(string $field, $from, $to): self
    {
        $this->range[$field] = compact('from', 'to');

        return $this;
    }

    /**
     * 添加权重索引词
     *
     * @param string $field 索引词所属的字段
     * @param string $term 索引词
     * @param float $weight 权重计算缩放比例
     * @return $this
     */
    public function addWeight(string $field, string $term, float $weight = 1): self
    {
        $this->weight[$field] = compact('term', 'weight');

        return $this;
    }

    /**
     * 设置当前搜索语句的分词复合等级
     *
     * @param int $level 要设置的分词复合等级。默认为 3, 值范围 0~15
     * @return $this
     */
    public function setScwsMulti(int $level): self
    {
        $this->scws_multi = $level;

        return $this;
    }
}