<?php
namespace app\admin\controller;

use mikkle\tp_wechat\Wechat;
use mikkle\tp_wechat\WechatApi;
use think\Controller;

class Wxpush extends WechatApi
{
    protected $options;

    // protected $options = [
    //     'token' => 'VmtT4C057XDpDM5XQpcdmbxqq880Q58W',
    //     'appid' => 'wx1ecad82a5dbdcf68',
    //     'appsecret' => 'b485f149cb22831b99cfbe91def327ad',
    //     'encodingaeskey' => 'V30J34j497tPF72s9zEj03T43t0ZJ34j3m9sjzm3S7o',
    // ];

    protected $valid = 1; //网站第一次匹配 true 1为匹配
    protected $isHook = false; //是否开启钩子

    public function __construct($options = [])
    {
        $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $urlarr = parse_url($url);
        parse_str($urlarr['query'], $parr);
        $sid = $parr['id'];

        $this->options = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', $sid)->find();
        $this->weObj = Wechat::receive($this->options);
    }

    protected function returnEventSubscribe()
    {
        // Db::name('WeFans')->where('openid', $this->openid)->update(['subscribe' => 0, 'unsubscribe_time' => time()]);
        return ['type' => 'text', 'message' => $this->openid];
    }

    protected function returnEventClick()
    {
        $message = $this->weObj->getRevEvent();
        if ($message["key"] == "查询分班") {
            return ['type' => 'text', 'message' => "哈哈哈哈哈"];
        }
        return ['type' => 'text', 'message' => '你的点击成功'];
    }

    protected function returnMessageText()
    {
        $re = $this->weObj->getRevContent();
        if ($re == "查询分班") {
            return '查询结果通过啦！您的孩子被分配到2班了！';
        }
        return '发送的是778899' . $re;
    }
/**
 * 消息逐条推送接口
 * @param  [sid] $sid [传过来的学校id]
 * @return [type]      [暂时未定义返货类型]
 */
    public function api($sid)
    {
        $options = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', $sid)->find();
        $content = "第三次测试回复889900";
        $data = array(
            "touser" => array(
                "oKIYi0lgzlVd9R0M1rNDlf5iAIS4",
                "oKIYi0uoa2VUtJbhAAmYRKyszKg4",
            ),
            "msgtype" => "text",
            "text" => array("content" => $content),

        );

        $re = Wechat::message($options)->sendMassMessage($data);

        dump($re);die;

    }
/**
 * 消息群发群组推送
 * @param  [sid] $sid [传过来的学校id]
 * @return [type]      [未定义返回结果]
 */
    public function sendall($sid)
    {
        $options = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', $sid)->find();
        $content = "20171225分班结果群发内容测试";
        $data = array(
            "filter" => array(
                "is_to_all" => true, //是否群发给所有用户.True不用分组id，False需填写分组id
            ),
            "msgtype" => "text",
            "text" => array("content" => $content),

        );
        $re = Wechat::message($options)->sendGroupMassMessage($data);

        dump($re);die;

    }

}
