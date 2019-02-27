<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------

return [
    'type'  =>  'complex',
    //默认挂载
    'default' => [
        'type'      => 'redis',
        'host'      => '127.0.0.1',
        'port'      => 6379,
        'password'  => '',
        'select'    => '',
        'persistent'=> false,
        'prefix'    => '',
        // 缓存有效期 0表示永久缓存,设置为1天
        'expire' => 86400,
    ],
    'redis' => [
        'type'      => 'redis',
        'host'      => '127.0.0.1',
        'port'      => 6379,
        'password'  => '',
        'select'    => '',
        'prefix'    => '',
        'persistent'=> false,
        // 缓存有效期 0表示永久缓存,设置为1天
        'expire' => 86400,
    ],
    'file' => [
        'type'  => 'file',
        // 缓存前缀
        'prefix' => '',
        //缓存目录
        'path'   => '../runtime/file/',
        // 缓存有效期 0表示永久缓存,设置为1天
        'expire' => 86400,
    ],

];
