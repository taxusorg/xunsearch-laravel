Laravel Xunsearch
========
介绍
--------
这个包是在laravel/scout的服务中添加拓展，使用XunSearch搜索功能。
XunSearch的安装，具体查看XunSearch的[官方文档](http://www.xunsearch.com)。
laravel/scout的安装和使用，查看laravel/scout的[官方文档](https://laravel.com/docs/master/scout)。

安装
--------
使用composer
```
composer require taxusorg/xunsearch-laravel
```
在配置文件中添加服务提供者（Laravel5.5有自动添加）
```
'providers' => [
    //...
    Taxusorg\XunSearchLaravel\XunSearchServiceProvider::class,
    //...
],
```
复制配置文件到配置目录，配置文件内容不多，而且可以在.env文件中设置。
手动复制或者
```
php artisan vendor:publish --provider="Taxusorg\XunSearchLaravel\XunSearchServiceProvider"
```

修改scout配置文件config/scout.php，让scout使用XunSearch引擎
```
    'driver' => env('SCOUT_DRIVER', 'xunsearch'),
```
直接在.env文件中设置
```
SCOUT_DRIVER=xunsearch
```
修改XunSearch配置文件config/xunsearch.php，没有特殊情况默认即可
```
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
在Model中使用搜索功能，先引入Searchable，详见Scout使用文档。
```
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Blog extends Model
{
    use Searchable;
```
要使用XunSearch，还要实现指定接口，同时使用Trait（Trait中注册了范围检索方法range和清空所有数据方法cleanSearchable）。
实现接口需要添加searchableFieldsType方法进行字段类型设置
```
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\Contracts\XunSearch as XunSearchContract;
use Taxusorg\XunSearchLaravel\XunSearchTrait;

class Blog extends Model implements XunSearchContract
{
    use Searchable， XunSearchTrait;
    
    public function searchableFieldsType()
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
设置的字段类型的具体效果，查看XunSearch官方文档。

Model的主键，例如id，已被默认设为引擎的文档主键。
如果需要对id进行区间检索，把id的类型设为self::XUNSEARCH_TYPE_NUMERIC。如果不需要对id进行检索，可以不添加id字段。

self::XUNSEARCH_TYPE_TITLE和self::XUNSEARCH_TYPE_BODY只能分别设置一次。

XunSearchTrait中给Scout的Builder注册了range方法进行区间检索。
除了title和body特殊字段，XunSearch默认设定字段为string，需要进行区间检索的字段，要设为numeric或者date才能正常检索。
例如设定id字段为self::XUNSEARCH_TYPE_NUMERIC，在id大于20小于等于60的范围内搜索word
```
Blog::search('word')->range('id', 20, 60)->get();
```

字段的索引index和分词器tokenizer的设置效果，在官方为文档查看。

相关
--------
[laravel/scout](https://github.com/laravel/scout)<br />
[hightman/xs-sdk-php](https://github.com/hightman/xs-sdk-php)

![statistics](https://piwik.fairycat.cn/piwik.php?idsite=2&rec=1)

