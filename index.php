<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/app/');
define('CONF_PATH', __DIR__ . '/config/');
define('EXTEND_PATH', __DIR__ . '/extend/');
define('RUNTIME_PATH', __DIR__ . '/runtime/');
define('LOG_PATH', __DIR__ . '/log/');
define('APP_DEBUG',false); // 开启调试模式
define('DB_FIELD_CACHE',false);
define('HTML_CACHE_ON',false);




// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
