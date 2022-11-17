Laravel XunSearch
========
介绍
--------
这个包是在 [laravel/scout][laravel_scout_url] 的服务中添加拓展，使用 [XunSearch 搜索][xun_search_sdk_url] 功能。

XunSearch 的安装，具体查看 [XunSearch 的官方文档][xun_search_index]。

laravel/scout 的安装和使用，查看 [laravel/scout 的官方文档][laravel_scout_doc]。

安装
--------

使用 composer
```shell script
composer require taxusorg/xunsearch-laravel
```

在配置文件中添加服务提供者（Laravel5.5 及以上 有自动添加）
```php
'providers' => [
    //...
    Taxusorg\XunSearchLaravel\XunSearchServiceProvider::class,
    //...
],
```

复制配置文件到配置目录，配置文件内容不多，而且可以在 `.env` 文件中设置。手动复制或者使用命令复制：
```shell script
php artisan vendor:publish --provider="Taxusorg\XunSearchLaravel\XunSearchServiceProvider"
```

修改 scout 配置文件 `config/scout.php`，让 scout 使用 XunSearch 引擎
```php
    'driver' => env('SCOUT_DRIVER', 'xunsearch'),
```

或者直接在 `.env` 文件中设置
```dotenv
SCOUT_DRIVER=xunsearch
```

修改 XunSearch 配置文件 `config/xunsearch.php`
```php
    'server_host' => env('XUNSEARCH_SERVER_HOST', '127.0.0.1'),
    'server_index_host' => env('XUNSEARCH_SERVER_INDEX_HOST', null),
    'server_index_port' => env('XUNSEARCH_SERVER_INDEX_PORT', '8383'),
    'server_search_host' => env('XUNSEARCH_SERVER_SEARCH_HOST', null),
    'server_search_port' => env('XUNSEARCH_SERVER_SEARCH_PORT', '8384'),
    'default_charset' => env('XUNSEARCH_DEFAULT_CHARSET', 'utf-8'),
```

或者直接在 `.env` 文件中设置需要修改的内容，没有特殊情况默认即可
```dotenv
XUNSEARCH_SERVER_HOST=127.0.0.1
```

使用
--------
在 `Model` 中使用搜索功能，先引入 `Searchable` Trait，详见 [Scout 使用文档][laravel_scout_doc]。
```php
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Blog extends Model
{
    use Searchable;
```

要使用 XunSearch， `Model` 还要实现指定接口。实现接口需要添加 `xunSearchFieldsType` 方法进行字段类型设置
```php
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Taxusorg\XunSearchLaravel\XunSearchModelInterface;

class Blog extends Model implements XunSearchModelInterface
{
    use Searchable;
    
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
            'created_at' => [
                'type'=>self::XUNSEARCH_TYPE_DATE,
                'index'=>self::XUNSEARCH_INDEX_NONE,
            ],
        ];
    }
}
```

设置的字段类型的具体效果，查看 [XunSearch 官方文档][xun_search_index]。

`Model` 的主键，例如 `id`，已被默认设为引擎的文档主键。
如果需要对 id 进行区间检索，把 id 的类型设为 `self::XUNSEARCH_TYPE_NUMERIC`。如果不需要对 `id` 进行检索，可以不添加 `id` 字段。

字段类型 `self::XUNSEARCH_TYPE_TITLE` 和 `self::XUNSEARCH_TYPE_BODY` 只能分别设置一次。

检索
------

查询方法和 scout 相同。简单查询可以按照 [laravel/scout 的官方文档][laravel_scout_doc] 进行查询即可。

`Model::search` 方法返回 `Builder` 对象，在进行服务拓展时，已经对该对象注册了宏，可以获取到 `XS` 对象，自行调用查询功能。

已注册的宏

| 方法 | 描述 |
|---|---|
| getXSTotal | 获取该库中的总文档数 |
| getXS | 获取 Client 对象 |
| getXSSearch | 获取 XSSearch 对象 |
| getXSIndex | 获取 XSIndex 对象 |

Client 对象是对 XS 对象的包装，包含 XS 对象的属性和行为，可以当作 XS 对象使用。

注意：
通过 Builder 获取的 Client 对象是和 Builder 一对一绑定的。
如果 Client 没有被另外引用，当 Builder 被释放时 Client 和其中的 XS 对象也被释放。
如果同时存在多个 Builder，对分别获取的 Client 和 XS 对象的设置互不干扰。

例
```php
$builder = Blog::search();
$builder->getXSSearch()->addRange('id', 1, 50);
$blogs = $builder->get();
```

检索结果
------

检索时使用 `get` 方法返回 Model 对象的集合，scout 已经对检索结果转换成 Model，而 `raw` 方法返回的是原始数据，为了灵活和方便，原始数据返回的是 Results 对象。

Results 对象是可遍历对象，遍历的内容为 XSDocument 对象。
```php
$results = Blog::search('test')->raw();
foreach ($results as $document) {
    // XSDocument $document
}
```

同时，Results 对象可以调用 `getModels` 方法获取和 `Builder::get` 方法相同的内容。
```php
$blogs = Blog::search('test')->get();
//------
$results = Blog::search('test')->raw();
$blogs = $results->getModels();
```

Results 对象的方法

| 方法 | 描述 |
|---|---|
| getIds | 获取检索结果的主键集合 |
| getModels | 获取检索结果的 Model 集合 |
| getTotal | 获取检索的总数 |
| getArray | 获取检索结果的 XSDocument 数组 |

拓展查询
------

Model 可以引入 `XunSearchTrait` 增加查询方法。`XunSearchTrait` 中已经引入了 `Searchable` 且重写了 `search` 方法，所以不必同时引入 `Searchable`。

```php
use Illuminate\Database\Eloquent\Model;
use Taxusorg\XunSearchLaravel\XunSearchModelInterface;
use Taxusorg\XunSearchLaravel\XunSearchTrait;

class Blog extends Model implements XunSearchModelInterface
{
    use XunSearchTrait;
    
    public function xunSearchFieldsType()
    {
        // ...
    }
}
```

在使用 `XunSearchTrait` 之后，`search` 返回的是拓展之后的 Builder，可以使用 `range` 等方法。
例如设定 `id` 字段为 `self::XUNSEARCH_TYPE_NUMERIC`，在 `id` 大于 `20` 小于等于 `60` 的范围内搜索 `word`

```php
Blog::search('word')->range('id', 20, 60)->get();
```

除了 `title` 和 `body` 特殊字段， XunSearch 默认设定字段为 `string`，需要进行区间检索的字段，要设为 `numeric` 或者 `date` 才能正常检索。

Builder 拓展的方法

| 方法 | 描述 |
|---|---|
| setFuzzy | 开启模糊搜索 |
| setCutOff | 设置”百分比“和”权重“剔除参数 |
| setRequireMatchedTerm | 是否在搜索结果文档中返回匹配词表 |
| setWeightingScheme | 设置检索匹配的权重方案 |
| setAutoSynonyms | 开启自动同义词搜索功能 |
| setSynonymScale | 设置同义词搜索的权重比例 |
| setSort | 设置多字段组合排序方式。该方法会覆盖 orderBy 方法。若无必要使用 orderBy 就行 |
| setDocOrder | 设置结果按索引入库先后排序 |
| setCollapse | 设置折叠搜索结果 |
| addRange | 添加搜索过滤区间或范围 |
| addWeight | 添加权重索引词 |
| setScwsMulti | 设置当前搜索语句的分词复合等级 |

`XunSearchTrait` 中包含一些静态方法，可以获取 Client 对象等。

注意：
通过静态方法获取 Client 对象时，同等于通过 search 获取 Builder 对象，再通过 Builder::getXS 获取 Client。
获取到 Client 后，Builder 对象已经被丢弃。所以该 Client 不属于任何 Builder。
可以通过该方法获取 XS 对象进行与 Model、Scout 等无关的原始操作。

| 方法 | 描述 |
|---|---|
| XS | 获取 Client 对象 |
| XSSearch | 获取 XSSearch 对象 |
| XSIndex | 获取 XSIndex 对象 |
| XSTotal | 获取该库中的总文档数 |
| search | 返回 Builder 对象 |
| searchableAllSynonyms | 获取当前库内的全部同义词列表 |
| searchableSynonyms | 获取指定词汇的同义词列表 |
| searchableHotQuery | 获取热门搜索词列表 |
| searchableRelatedQuery | 获取相关搜索词列表 |
| searchableExpandedQuery | 获取展开的搜索词列表 |
| searchableCorrectedQuery | 获取修正后的搜索词列表 |

更新
--------

4.0.x
* raw 方法返回 Results 对象
* 拓展 Builder
* 重写 search 方法，引入 `XunSearchTrait` 时不必引入 `Searchable`
* 增加 Client 对象

3.0.x
* 修改接口名称和路径
* 每一个 builder 对应一个 XS 对象，可以通过 Trait 添加的方法获取或者调用 XS 对象的方法

2.1.x
* `XunSearchTrait` 中移除 `cleanSearchable` 方法，请使用 Scout 中的 `removeAllFromSearch` 方法。

相关链接
--------
[laravel/scout][laravel_scout_url]

[laravel/scout 文档][laravel_scout_doc]

[hightman/xs-sdk-php][xun_search_sdk_url]

[XunSearch 文档][xun_search_index]

[laravel_scout_url]: https://github.com/laravel/scout
[laravel_scout_doc]: https://laravel.com/docs/master/scout
[xun_search_sdk_url]: https://github.com/hightman/xs-sdk-php
[xun_search_index]: http://www.xunsearch.com
