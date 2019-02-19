<?php
namespace app\admin\controller;
header("content-type:text/html;charset=utf-8");
// namespace Aliyun\DySDKLite\Sms;
// require_once "../SignatureHelper.php";
// use Aliyun\DySDKLite\SignatureHelper;

// namespace Aliyun\DySDKLite\Sms;
// require_once "alidayu/SignatureHelper.php";
require_once('SignatureHelper.php');
use SignHelper;
use think\Controller;

class Message extends Common
{
    public function index(){

        echo '<style>li {font-size: 16px;} li.fail {color:red} li.success {color: green} li label{ display:inline-block; width: 15em}</style>';
        echo '<h1>执行环境检测</h1>';

        function success($title) {
            print_r("<li class=\"success\"><label>{$title}</label>[成功]</li>");
        }
        function fail($title, $description) {
            print_r("<li class=\"fail\"><label>{$title}</label>[失败] {$description}</li>");
        }

        if(preg_match("/^\d+\.\d+/", PHP_VERSION, $matches)) {
            $version = $matches[0];
            if($version >= 5.3) {
                success("PHP $version");
            } else {
                fail("PHP $version", "需要PHP>=5.3版本");
                exit(1);
            }
        }


        try {
            set_error_handler(function () { throw new Exception(); });
            date_default_timezone_get();
            restore_error_handler();
        } catch(Exception $e) {
            fail('默认时区设置', '请设置默认时区，如：date_default_timezone_set("Asia/Shanghai")');
        }

        echo '<h2>依赖扩展检测，如失败请安装相应扩展</h2>';

        $dependencies = array (
            'json_encode' => null,
            'curl_init' => null,
            'hash_hmac' => null,
            'simplexml_load_string' => '如果是php7.x + ubuntu环境，请确认php7.x-libxml是否安装，x为子版本号',
        );

        foreach($dependencies as $funcName => $description) {
            if(!function_exists($funcName)) {
                fail($funcName, $description || '');
            } else {
                success($funcName);
            }
        }

    }

    public function send() {
        
        $person_info = db('person_info')->field('name,card,tel')->where('schoolid',session("schoolid"))->select();

        // echo "<pre>";
        // print_r($person_info);

        foreach ($person_info as $k => $v) {

            $stid = db('students'.session('schoolid'))->field('id,taskid')->where('id_num',$v['card'])->find();

            // 班级号
            $clid = db('classes'.session('schoolid'))->field('name,cno,teacherid')->where('id',$stid['id'])->find();

            $person_info[$k]['classnum'] = $clid['cno'];

            $r = $this->sendSms($person_info[$k]);
        }

        if ($r) {

            $taskid = session('taskid');
            $this->success('发送信息成功',url('result/lst?taskid='.$taskid));

            // $this->success('绑定成功',url('http://xzedu.rmnsw58.com/wap/index/checkresult?id='.session('id')));

        } else {

            $this->error('发送信息失败');
        }
    
    }

    /**
     * 发送短信
     */
    public function sendSms($v) {

        $params = array ();

        // *** 需用户填写部分 ***
        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAIC0QLosvY1Cga";
        $accessKeySecret = "R4MB2hmsxW0RSyRaXsx8hIkw47nLsZ";

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $v['tel'];

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "七月的小仙境";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_119093330";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = Array (
            "name" => $v['name'],
            "class" => $v['classnum'],
            "names" => $v['name'],
            // "product" => "阿里通信"
        );

        // // fixme 可选: 设置发送短信流水号
        // $params['OutId'] = "12345";

        // // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        // $params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );

        if ($content) {
            return $content;
            // $this->success('发送信息成功', url('result/lst'));
        } else {
            // $this->error('发送信息失败');
        }

        // return $content;
    }

    // ini_set("display_errors", "on"); // 显示错误提示，仅用于测试时排查问题
    // set_time_limit(0); // 防止脚本超时，仅用于测试使用，生产环境请按实际情况设置
    // header("Content-Type: text/html; charset=utf-8"); // 输出为utf-8的文本格式，仅用于测试

    // 验证发送短信(SendSms)接口
    // print_r(sendSms());


}

