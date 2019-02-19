<?php

namespace app\mob\controller;
use think\Cache;
use think\Controller;
use think\session;
use think\Request;
class Management extends Controller
{
    //主页
    public function appindex()
    {
        $this->assign('host', $_SERVER['HTTP_HOST']."/mob/management");
        return view();
    }
    //学校主页
    public function index()
    {
        $alist = db('area')->select()->toArray();

        $this->assign('alist',$alist);
        return view();
    }
    //学校列表
    public function show_school()
    {
        $aid = input('post.aid');
        $slist = db('school')->where('areaid',$aid)->select()->toArray();
        foreach ($slist as $key => $value) {
            if($value['schtype']==1)
            {
                $slist[$key]['schtype'] ='小学';
            }
            if($value['schtype']==2)
            {
                $slist[$key]['schtype'] ='中学';
            }
            if($value['schtype']==3)
            {
                $slist[$key]['schtype'] ='高中';
            }
        }
        return $slist;
    }
    //学校操作页面
    public  function operating($schid)
    {
        $statu = db('schoolset')->where('schid',$schid)->find();
        $this->assign('statuhide',$statu['hide']);
        $this->assign('statustart',$statu['start']);
        $this->assign('statuerro',$statu['erro']);
        $this->assign('schid',$schid);
        return view();
    }
    //学校开启关闭隐藏功能
    public  function hide()
    {
        $sid = input('post.sid');
      
      	$has = db('schoolset')->where('schid',$sid)->find();
      	if(empty($has)){
        	$school = db('school')->where('schid',$sid)->find();
          	db('schoolset')->insert(array('schid'=>$school['schid'], 'schname'=>$school['schname'],'schtype'=>$school['schtype'], 'areaid'=>$school['areaid']));
        }
                                           
        $statu = db('schoolset')->field('hide')->where('schid',$sid)->find();
        if($statu['hide'] == 0)
        {
            $ini['hide'] = 1;
        }else
        {
            $ini['hide'] = 0;
        }
        $rt = db('schoolset')->where('schid',$sid)->update($ini);
        if($rt)
        {
            return 1;   //成功
        }else
        {
            return 2;   //失败
        }
    }
    //学校开启停止查询功能
    public  function start()
    {
        $sid = input('post.sid');
      	$has = db('schoolset')->where('schid',$sid)->find();
      	if(empty($has)){
        	$school = db('school')->where('schid',$sid)->find();
          	db('schoolset')->insert(array('schid'=>$school['schid'], 'schname'=>$school['schname'],'schtype'=>$school['schtype'], 'areaid'=>$school['areaid']));
        }
        $statu = db('schoolset')->field('start')->where('schid',$sid)->find();
        if($statu['start'] == 0)
        {
            $ini['start'] = 1;
        }else
        {
            $ini['start'] = 0;
        }
        $rt = db('schoolset')->where('schid',$sid)->update($ini);
        if($rt)
        {
            return 1;   //成功
        }else
        {
            return 2;   //失败
        }
    }
    //学校开启停止报错页面
    public  function erro()
    {
        $sid = input('post.sid');
      	$has = db('schoolset')->where('schid',$sid)->find();
      	if(empty($has)){
        	$school = db('school')->where('schid',$sid)->find();
          	db('schoolset')->insert(array('schid'=>$school['schid'], 'schname'=>$school['schname'],'schtype'=>$school['schtype'], 'areaid'=>$school['areaid']));
        }
        $errotext = input('post.errotext');
        if($errotext=='')
        {
            $ini['errotext'] = '系统维护中...';
        }else
        {
            $ini['errotext'] = $errotext;
        }
        $statu = db('schoolset')->field('erro')->where('schid',$sid)->find();
        if($statu['erro'] == 0)
        {
            $ini['erro'] = 1;
        }else
        {
            $ini['erro'] = 0;
        }
        $rt = db('schoolset')->where('schid',$sid)->update($ini);
        if($rt)
        {
            return 1;   //成功
        }else
        {
            return 2;   //失败
        }
    }
    //区设置
    public function areaindex()
    {
        $alist = db('area')->select()->toArray();
        $this->assign('alist',$alist);
        return view();
    }
    //区操作页面
    public  function areaoperating($areaid)
    {
        $statu = db('areaset')->where('area_id',$areaid)->find();

        $wxset = db('areawx')->where('areaid',$areaid)->find();

        $this->assign('appid',$wxset['appid']);
        $this->assign('appsecret',$wxset['appsecret']);
        $this->assign('statustart',$statu['start']);
        $this->assign('statuerro',$statu['erro']);
        $this->assign('areaid',$areaid);
        return view();
    }
     //区开启停止查询功能
    public  function areastart()
    {
        $aid = input('post.aid');
        $statu = db('areaset')->field('start')->where('area_id',$aid)->find();
        if($statu['start'] == 0)
        {
            $ini['start'] = 1;
        }else
        {
            $ini['start'] = 0;
        }
        $rt = db('areaset')->where('area_id',$aid)->update($ini);
        if($rt)
        {
            return 1;   //成功
        }else
        {
            return 2;   //失败
        }
    }
    //区开启停止报错页面
    public  function areaerro()
    {
        $aid = input('post.aid');
        $errotext = input('post.errotext');
        if($errotext=='')
        {
            $ini['errotext'] = '系统维护中...';
        }else
        {
            $ini['errotext'] = $errotext;
        }
        $statu = db('areaset')->field('erro')->where('area_id',$aid)->find();
        if($statu['erro'] == 0)
        {
            $ini['erro'] = 1;
        }else
        {
            $ini['erro'] = 0;
        }
        $rt = db('areaset')->where('area_id',$aid)->update($ini);
        if($rt)
        {
            return 1;   //成功
        }else
        {
            return 2;   //失败
        }
    }
    //获取access_token验证
    public  function access_token()
    {
        $appid = input('post.appid');
        $appsecret = input('post.appsecret');
        $list = db('token')->where('appid',$appid)->find();
        $nowtime = time();
    
        if($nowtime>$list['time'])
        {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret."";
            $data = $this->togeturl($url);
            $token = json_decode($data, true);

            if($token['errcode']!='')
            {
                $access_token = "errcode:".$token['errcode']."   "."errmsg:".$token['errmsg'];
                return $access_token;
            }
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
    //推送链接页面
    public function linkshow()
    {
        $alist = db('area')->select()->toArray();
        $this->assign('alist',$alist);
        return view();
    }
    //推送链接设置
    public function link($areaid)
    {
        $listlink = db('link')->where('areaid',$areaid)->select();
        $this->assign('areaid',$areaid);
        $this->assign('listlink',$listlink);
        return view();
    }
    //开关推送链接
    public function changelink()
    {
        $id = input('post.id');
        $ini['link'] = input('post.link');
        $ini['areaid'] = input('post.areaid');
        $stuat = input('post.stuat');

        if($stuat==0)
        {
            //开启
            $queren = db('link')->where('switch',1)->where('areaid',$areaid)->find();
            if(!empty($queren))
            {
                return 3;//已经有备用地址开启,需要关闭
            }else
            {
                $ini['switch'] = 1;
                $rt = db('link')->where('id',$id)->update($ini);
                if($rt)
                {
                    return 1;//成功
                }else
                {
                    return 2;//失败
                }
            }
        }else
        {
            //关闭
            $ini['switch'] = 0;
            $rt = db('link')->where('id',$id)->update($ini);
            if($rt)
            {
                return 1;//成功
            }else
            {
                return 4;//失败
            }
        }
        
    }
    //手动推送页面
    public  function push()
    {
        $alist = db('area')->select()->toArray();
        $this->assign('alist',$alist);
        return view();
    }
    //获取手动推送确认
    public  function confirmpusn()
    {
        $password = md5('xzjy_20180830_ts');
        if($password!=md5(input('post.password')))
        {
            return 3;//密码不正确
        }
        $areaid = input('post.areaid');
        // $type = $this->alltextmesses(11);  //测试预览推送文本接口 一月4次
        $type = $this->textmesses($areaid);  //给个人发推送
        return 1;
    }
    //给个人发送推送的接口
    public function textmesses($areaid)
    {
        $aname = db('area')->field('area_name')->where('area_id',$areaid)->find();
        $token = $this->push_access_token($areaid);//调用接口需要获取token,这里使用一个封装好的调取access_token的函数
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=".$token."";
        $data['towxname'] = 'majipeng';
        $data['text'] = ['content'=>'尊敬的学生家长：您好！2018年'.$aname['area_name'].'普通高中阳光分班，在纪检、家长、教师、新闻媒体等各界代表监督下，学生分组已分配完成。请点击 <a href="http://'.$_SERVER['HTTP_HOST'].'/mob/Enter/index/areaid/'.$areaid.'/type/bb">查看学生分组结果</a>'];
        $data['msgtype'] = 'text';
       return  $this->https_request($url,json_encode($data, JSON_UNESCAPED_UNICODE));
    }
    //正式推送接口
    public function alltextmesses($areaid)
    {   
        $aname = db('area')->field('area_name')->where('area_id',$areaid)->find();
        $token = $this->push_access_token($areaid);
        $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=".$token."";
        $data['filter'] = ['is_to_all'=>true];
        //测试数据
        $data['text'] = ['content'=>'尊敬的学生家长：您好！2018年'.$aname['area_name'].'普通高中阳光分班，在纪检、家长、教师、新闻媒体等各界代表监督下，学生分组已分配完成。请点击 <a href="http://'.$_SERVER['HTTP_HOST'].'/mob/Enter/index/areaid/'.$areaid.'/type/bb">查看学生分组结果</a>'];                 
        $data['msgtype'] = 'text';

        echo "<pre>";
        print_r($data);
        //推送请求
        // return  $this->https_request($url,json_encode($data, JSON_UNESCAPED_UNICODE));
    }
    //获取微信access_token
    public function push_access_token($areaid)
    {   
        $wxinfo = db('areawx')->field('appid,appsecret')->where('areaid',$areaid)->find();
        $appid = $wxinfo['appid'];
        $appsecret = $wxinfo['appsecret'];
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret."";
        $data = $this->togeturl($url);
        $token = json_decode($data, true);
        $access_token = $token['access_token'];
        return $access_token;
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
}
