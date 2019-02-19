<?php
namespace app\wap\controller;

use think\Controller;
use mikkle\tp_wechat\Wechat;
use mikkle\tp_wechat\WechatApi;



class Index extends Controller
{
    // 绑定学生信息
    public function index() {

        if (request()->isPost()) {

            $data = input('post.');

            // 01.重复提交
            $x = db('Person_info')->where('openid',$data['openid'])->find();
            if ($x) {

                $this->error('已经提交过信息了!');

            }else{

                // 02.查看学生表中有无此学生
                $schoolidarr = explode('.', $data['schoolid']);
                $data['schoolid'] = $schoolidarr[0];

                $stid = db('students'. $data['schoolid'])
                        ->where('id_num',$data['card'])
                        ->where('name',$data['name'])
                        ->find();
                if (empty($stid)) {

                    $this->error('学生信息不正确或者分班暂未开始!');

                }else{

                    // echo "<pre>";
                    // print_r($data);exit();
                    
                    $res = db('Person_info')->insert($data);
                    if ($res) {
                
                        $this->success('绑定成功',url('http://xzedu.rmnsw58.com/wap/index/checkresult?id='.$data['schoolid']));
                            
                    } else {

                        $this->error('绑定失败');
                    }
                }
            }
            return;
        }
        
        $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $urlarr = parse_url($url);
        parse_str($urlarr['query'], $parr);
        $sid = $parr['id'];
        session('id', $sid);

        // openid
        $options = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', session('id'))->find();
        $re = Wechat::oauth($options)->getOauthRedirect('http://xzedu.rmnsw58.com/wap/index/index?id='.session('id'));
        $code=$_GET['code'];
        if(!isset($code)){
            header('Location:'.$re);
        }
        $res = Wechat::oauth($options)->getOauthAccessToken();
        $this->assign('openid', $res['openid']);
        $this->assign('schoolid', session('id'));

        // echo $res['openid'];exit();
        // openid 结束
        return view();
    }

    // 查询分班结果
    public function checkresult() {

        $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $urlarr = parse_url($url);
        parse_str($urlarr['query'], $parr);
        $sid = $parr['id'];

        $schoolidarr = explode('.', $sid);
        // $data['schoolid'] = $schoolidarr[0];

        
        session('id', $schoolidarr[0]);
        // session('id', $sid);

        
        // openid 开始
        $options = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', session('id'))->find();
        $re = Wechat::oauth($options)->getOauthRedirect('http://xzedu.rmnsw58.com/wap/index/checkresult?id='.session('id'));
        $code=$_GET['code'];
        if(!isset($code)){
            header('Location:'.$re);
        }
        $res = Wechat::oauth($options)->getOauthAccessToken();
        // openid 结束

        $openid = $res['openid'];

        // echo "1";die();
        //查看绑定信息
        $x = db('Person_info')->where('openid',$openid)->find();

        // var_dump($x);die();
        if ($x) {

            //发送分班结果

            // id_num 带空格了
            $stid = db('students'.session('id'))->field('id')->where('id_num',$x['card'])->find();

            $clid = db('classes'.session('id'))->field('name,cno,teacherid')->where('id',$stid['id'])->find();

            $teacher = db('teacher')->field('name,tel')->where('id',$clid['teacherid'])->find();

            $this->assign('clid',$clid);
            $this->assign('teacher',$teacher['name']);
            $this->assign('tel',$teacher['tel']);

        }else{

            $this->success('请先绑定学生信息', url('http://xzedu.rmnsw58.com/wap/index/index?id='.session('id')));
        }

        return view();
    }

}
