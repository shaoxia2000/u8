<?php

namespace app\mob\controller;
use think\Cache;
use think\Controller;
use think\session;
use think\Request;
class Enter extends Controller
{
    public function index($areaid,$type)
    {
        $wxinfo = db('areawx')->field('appid,appsecret')->where('areaid',$areaid)->find();
        $appid = $wxinfo['appid'];
        $appsecret = $wxinfo['appsecret'];
        if($type=='bb')
        {
            $playurl='http://'.$_SERVER['HTTP_HOST'].'/mob/enter/index/areaid/'.$areaid.'/type/bb';//学生查询
        }else
        {
            return view('xt');
        }

        //微信登陆
        // $wxlogin = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($playurl)."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
      
        // $code =input('get.code');
        // if(!$code)
        // {
        //     header("Location:$wxlogin");
        //     return;
        // }

        // $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
        // $ch = curl_init();  
        // curl_setopt($ch, CURLOPT_URL,$url);  
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
        // $html = curl_exec($ch);
        // if($html!="")
        // {  
        //     $htmls = json_decode($html,true);
        //     $openid = isset($htmls["openid"]) ? $htmls['openid'] : '';
        // }
        // $access_token = $this->access_token($areaid);
        // $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        // $html=$this->togeturl($url);
        // $htmls = json_decode($html,true);
        // if(!$htmls['openid'])
        // {
	       // $htmls['subscribe'] = '1';
        // }
        // $openid = $htmls['openid'];
        $htmls['subscribe'] = '1';

        $linkrt = db('link')->where('switch',1)->where('areaid',$areaid)->find();


        if(!empty($linkrt))
        {
            $localhostHttp = $_SERVER['HTTP_HOST']."/mob/Enter/index/areaid/".$areaid."/type/bb";

            if($localhostHttp==$linkrt['link'])
            {
                
                if($type == 'bb')
                {
                    $this->redirect('Result/status',array('areaid' =>$areaid ,'subscribe'=>$htmls['subscribe'],'type'=>$type ));
                }
            }else
            {
                $linkurl = "http://".$linkrt['link'];
                $this->redirect($linkurl);
            }
        }

        if($type == 'bb')
        {
            $this->redirect('Result/status',array('areaid' =>$areaid ,'subscribe'=>$htmls['subscribe'],'type'=>$type ));
        }
    }
   
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
        if($a === false)
        {
            $errno = curl_errno($ch);
            $info = curl_getinfo($ch);
            $info['errno'] = $errno;
            $info['error'] = curl_error($ch);
            file_put_contents(LOG_PATH . 'curl_log.'.date("Ymdhis").'.log', json_encode($info) . PHP_EOF, FILE_APPEND);
        } 
        return $a;
    }
}
