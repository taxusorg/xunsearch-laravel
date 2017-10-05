<?php
return [
    'server_host' => env('XUNSEARCH_SERVER_HOST', '127.0.0.1'),
    'server_index_host' => env('XUNSEARCH_SERVER_INDEX_HOST', null),
    'server_index_port' => env('XUNSEARCH_SERVER_INDEX_PORT', '8383'),
    'server_search_host' => env('XUNSEARCH_SERVER_SEARCH_HOST', null),
    'server_search_port' => env('XUNSEARCH_SERVER_SEARCH_PORT', '8384'),
    'default_charset' => env('XUNSEARCH_DEFAULT_CHARSET', 'utf-8'),
    'doc_key_name' => env('XUNSEARCH_DOC_KEY_NAME', null),
];