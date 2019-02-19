<?php
namespace app\mob\controller;
use think\Cache;
use think\Controller;
use think\session;
use think\Request;
class Wx extends Controller{
    public function index($areaid)
    {
        return view();
    }
    public function wxapi($areaid)
    {
        $type = $this->alltextmesses($areaid);  //测试预览推送文本接口 一月4次
        // $type = $this->textmesses($areaid);  //测试预览推送文本接口 一月4次
		
    }
    //预览推送给群接口
    public function alltextmesses($areaid)
    {   
        $aname = db('area')->field('area_name')->where('area_id',$areaid)->find();
        $token = $this->access_token($areaid);
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=".$token."";
        $data['filter'] = ['is_to_all'=>true];
        //测试数据
        $data['text'] = ['content'=>'尊敬的学生家长：您好！2018年'.$aname['area_name'].'义务教育学校阳光分班，在纪检、家长、教师、新闻媒体等各界代表监督下，学生分组已分配完成。请点击 <a href="http://bm.dqedu.net/m.php?aid='.$areaid.'">查看学生分组结果</a>'];                 
        $data['msgtype'] = 'text';
        //推送请求
        return  $this->https_request($url,json_encode($data, JSON_UNESCAPED_UNICODE));
    }
    //获取微信access_token
    public function access_token($areaid)
    {   
        $wxinfo = db('areawx')->field('appid,appsecret')->where('areaid',$areaid)->find();
        $appid = $wxinfo['appid'];
        $appsecret = $wxinfo['appsecret'];
        $list = db('token')->where('appid',$appid)->find();
        $nowtime = time();
        if($nowtime>$list['time'])
        {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret."";
            $data = $this->togeturl($url);
            $token = json_decode($data, true);
            $access_token = $token['access_token'];
            $newtime = $token['expires_in'] - 200;
            $expire = $nowtime + $newtime;
            $ini['access_token'] = $access_token;
            $ini['time'] =  $expire;
            db('token')->where('appid',$appid)->update($ini);
        }else
        {
            $access_token = $list['access_token'];
              
        }
        return $access_token;

    }
    //get请求
    function togeturl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $a = curl_exec($ch);
        return $a;
    }

    //post请求
    function https_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    //预览推送给个人接口
    public function textmesses($areaid)
    {
        $token = $this->access_token($areaid);//调用接口需要获取token,这里使用一个封装好的调取access_token的函数
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=".$token."";
        $data['towxname'] = 'majipeng';
        $data['text'] = ['content'=>'尊敬的学生家长：您好！2018年'.$aname['area_name'].'义务教育学校阳光分班，在纪检、家长、教师、新闻媒体等各界代表监督下，学生分组已分配完成。请点击 <a href="http://bm.dqedu.net/m.php?aid='.$areaid.'">查看学生分组结果</a>'];
        $data['msgtype'] = 'text';
       return  $this->https_request($url,json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
