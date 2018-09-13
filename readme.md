Laravel Xunsearch
========
介绍
--------
这个包是在[laravel/scout][laravel_scout_url]的服务中添加拓展，使用[XunSearch搜索][xun_search_sdk_url]功能。
XunSearch的安装，具体查看[XunSearch的官方文档][xun_search_index]。
laravel/scout的安装和使用，查看[laravel/scout的官方文档][laravel_scout_doc]。

安装
--------
使用composer
```shell
composer require taxusorg/xunsearch-laravel
```
在配置文件中添加服务提供者（Laravel5.5有自动添加）
```php
'providers' => [
    //...
    Taxusorg\XunSearchLaravel\XunSearchServiceProvider::class,
    //...
],
```
复制配置文件到配置目录，配置文件内容不多，而且可以在.env文件中设置。
手动复制或者
```shell
php artisan vendor:publish --provider="Taxusorg\XunSearchLaravel\XunSearchServiceProvider"
```

修改scout配置文件config/scout.php，让scout使用XunSearch引擎
```php
    'driver' => env('SCOUT_DRIVER', 'xunsearch'),
```
直接在.env文件中设置
```
SCOUT_DRIVER=xunsearch
```
修改XunSearch配置文件config/xunsearch.php，没有特殊情况默认即可
```php
    'server_host' => env('XUNSEARCH_SERVER_HOST', '127.0.0.1'),
    'server_index_port' => env('XUNSEARCH_SERVER_INDEX_PORT', '8383'),
    'server_search_port' => env('XUNSEARCH_SERVER_SEARCH_PORT', '8384'),
    'default_charset' => env('XUNSEARCH_DEFAULT_CHARSET', 'utf-8'),
```
直接在.env文件中设置需要修改的内容，不需要修改的使用默认即可
```
XUNSEARCH_SERVER_HOST=127.0.0.1
```

使用
--------
在Model中使用搜索功能，先引入Searchable，详见[Scout使用文档][laravel_scout_doc]。
```php
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Blog extends Model
{
    use Searchable;
```
要使用XunSearch，还要实现指定接口，同时使用Trait（Trait中注册了范围检索方法``range``和清空所有数据方法``cleanSearchable``）。
实现接口需要添加``xunSearchFieldsType``方法进行字段类型设置
```
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\Contracts\XunSearch as XunSearchContract;
use Taxusorg\XunSearchLaravel\XunSearchTrait;

class Blog extends Model implements XunSearchContract
{
    use Searchable， XunSearchTrait;
    
    public function xunSearchFieldsType()
    {
        return [
            'id' => [
                'type'=>self::XUNSEARCH_TYPE_NUMERIC,
            ],
            'title' => [
                'type'=>self::XUNSEARCH_TYPE_TITLE,
            ],
            'body' => [
                'type'=>self::XUNSEARCH_TYPE_BODY,
            ],
            'field' => [
                'tokenizer'=>self::XUNSEARCH_TOKENIZER_XLEN,
                'tokenizer_value'=>2,
            ],
            'data' => [
                'type'=>self::XUNSEARCH_TYPE_DATE,
                'index'=>self::XUNSEARCH_INDEX_NONE,
            ],
        ];
    }
```
设置的字段类型的具体效果，查看[XunSearch官方文档][xun_search_index]。

Model 的主键，例如 id，已被默认设为引擎的文档主键。
如果需要对 id 进行区间检索，把 id 的类型设为``self::XUNSEARCH_TYPE_NUMERIC``。如果不需要对 id 进行检索，可以不添加 id 字段。

字段类型``self::XUNSEARCH_TYPE_TITLE``和``self::XUNSEARCH_TYPE_BODY``只能分别设置一次。

XunSearchTrait 中给 Scout 的 Builder 注册了``range``方法进行区间检索。
除了 title 和 body 特殊字段，XunSearch 默认设定字段为 string，需要进行区间检索的字段，要设为 numeric 或者 date 才能正常检索。
例如设定 id 字段为``self::XUNSEARCH_TYPE_NUMERIC``，在 id 大于``20``小于等于``60``的范围内搜索``word``
```
Blog::search('word')->range('id', 20, 60)->get();
```

字段的索引 index 和分词器 tokenizer 的设置效果，在[官方为文档][xun_search_index]查看。

相关
--------
[laravel/scout][laravel_scout_url]

[laravel/scout文档][laravel_scout_doc]

[hightman/xs-sdk-php][xun_search_sdk_url]

[XunSearch文档][xun_search_index]

[laravel_scout_url]: https://github.com/laravel/scout
[laravel_scout_doc]: https://laravel.com/docs/master/scout
[xun_search_sdk_url]: https://github.com/hightman/xs-sdk-php
[xun_search_index]: http://www.xunsearch.com

![statistics](https://piwik.fairycat.cn/piwik.php?idsite=2&rec=1)
