<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    'database' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => 'localhost',
        // 数据库名
        'database' => 'nfb_dqedu_net',
        // 用户名
        'username' => 'nfb_dqedu_net',
        // 密码
        'password' => 'K2mSRkYhpyKPTtFb',
        // 端口
        'hostport' => '3306',
        // 连接dsn
        'dsn' => '',
        // 数据库连接参数
        'params' => [],
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => 'bk_',
        // 数据库调试模式
        'debug' => true,
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy' => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate' => false,
        // 读写分离后 主服务器数量
        'master_num' => 1,
        // 指定从服务器序号
        'slave_no' => '',
        // 是否严格检查字段是否存在
        'fields_strict' => true,
        // 数据集返回类型
        'resultset_type' => 'collection',
        // 自动写入时间戳字段
        'auto_timestamp' => false,
        // 时间字段取出后的默认时间格式
        'datetime_format' => 'Y-m-d H:i:s',
        // 是否需要进行SQL性能分析
        'sql_explain' => false,
    ],
	
	// 视图输出字符串内容替换
    'view_replace_str' => [
	    '__PUBLIC__' => '/public/',
	    '__ROOT__'   => '/',
	    '__ASSETS__'  => think\Url::build('/').'public/assets',
	    '__PIC__'    => think\Url::build('/').'public',
	    '__APPURL__' => think\Url::build('/').'manage',
        '__MOB__' => think\Url::build('/').'public/static/mob',
        '__SCR__' => think\Url::build('/').'public/static/screen',
    ],

    'captcha' => [
        // 验证码字符集合
        'codeSet' => '1234',
        // 验证码字体大小(px)
        'fontSize' => 25,
        // 是否画混淆曲线
        'useCurve' => true,
        // 验证码位数
        'length' => 4,
        // 验证成功后是否重置
        'reset' => true,
    ],
	
	// +----------------------------------------------------------------------
	// | 会话设置
	// +----------------------------------------------------------------------

    'session' => [
	    'id' => '',
	    // SESSION_ID的提交变量,解决flash上传跨域
	    'var_session_id' => '',
	    // SESSION 前缀
	    'prefix' => 'think',
	    // 驱动方式 支持redis memcache memcached
	    'type' => 'redis',
	    // 是否自动开启 SESSION
	    'auto_start' => true,
	    'host' => '127.0.0.1',
	    'port' => 6379,
	    'password'   => '',
	    'expire'   =>  86400 * 30,

    ],
];
