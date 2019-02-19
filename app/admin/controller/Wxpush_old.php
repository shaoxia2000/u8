<?php
namespace app\admin\controller;

use think\Controller;
use mikkle\tp_wechat\WechatApi;
use think\session;

class Wxpush extends WechatApi
{
    protected $options=[
        'token'=>'TOKEN',
        'appid'=>'wx07bf5b7de3dc60a2',
        'appsecret'=>'e45e43074c516a3918ac6d2c7b062348',
        'encodingaeskey'=>'T8ZH6KNO49o6241N2o6VJ6gC2Gn2od1gH4fOOv466h7',
    ];
    protected $valid = true;  //网站第一次匹配 true 1为匹配
    protected $isHook = false; //是否开启钩子


    protected function returnEventSubscribe(){
        // Db::name('WeFans')->where('openid', $this->openid)->update(['subscribe' => 0, 'unsubscribe_time' => time()]);
        return ['type' => 'text', 'message' => '测试关注回复了嘿嘿嘿'];
    }

}