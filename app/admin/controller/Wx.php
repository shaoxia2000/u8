<?php
namespace app\admin\controller;

use mikkle\tp_wechat\Wechat;
use think\cache\driver\Redis;
use think\Controller;
use think\paginator\driver\Bootstrap;
use think\session;

class Wx extends Common
{
    public function index()
    {
        $options = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', Session::get('schoolid'))->find();
        // dump($options);die;
        if (request()->isPost()) {
            if (input('do') == 'remove') {
                // 删除菜单
                $re = Wechat::menu($options)->deleteMenu();
                if ($re) {
                    // 删除成功
                    echo "<script>parent.replaceok('删除成功！');</script>";
                } else {
                    // 删除失败
                    echo "<script>parent.replaceFuck('删除失败！');</script>";
                }
            } else {
                $menu = urldecode(input('do'));
                $menu = json_decode($menu, true);
                $button['button'] = $menu;
                // dump($button);die;
                $re = Wechat::menu($options)->createMenu($button);
                if ($re) {
                    echo "<script>parent.replaceok('更新菜单成功！');</script>";
                } else {
                    echo "<script>parent.replaceFuck('更新菜单失败！');</script>";
                }
            }
            exit();
        }
        $menu = Wechat::menu($options)->getMenu();
        $this->assign('menu', $menu['menu']);
        return view();
    }

    public function setting()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $school_id = input('school_id');
            $res = db('wxsetting')->field('school_id')->where('school_id', Session::get('schoolid'))->find();
            if (!$res) {
                $kk = db('wxsetting')->insert($data);
                if ($kk) {
                    logs();
                    $this->success('添加成功');
                } else {
                    $this->error('添加失败');
                }
            } else {
                $kk = db('wxsetting')->where('school_id', $school_id)->update(['token' => $data['token'], 'appid' => $data['appid'], 'appsecret' => $data['appsecret'], 'encodingaeskey' => $data['encodingaeskey']]);
                if ($kk) {
                    logs();
                    $this->success('修改成功');
                } else {
                    $this->error('修改失败');
                }
            }

        }
        $res = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', Session::get('schoolid'))->find();
        $this->assign('res', $res);
        return view();
    }

    public function fs()
    {

        $data = $this->fsdata();
        $data_list = new Page($data, 15);

        // dump($data_list);exit();

        $this->assign('userlist', $data_list);
        return view();
    }

    public function send()
    {

        $p = db('person_info')->where('schoolid',session("schoolid"))->select();

        foreach ($p as $k => $v) {

            $stid = db('students'.session('schoolid'))->field('id,taskid')->where('id_num',$v['card'])->find();

            // 任务
            $task = db('task'.session('schoolid'))->field('title')->where('id',$stid['taskid'])->find();
            $p[$k]['task'] = $task['title'];

            // 班级号
            $clid = db('classes'.session('schoolid'))->field('name,cno,teacherid')->where('id',$stid['id'])->find();
            
            // 老师
            $teacher = db('teacher')->field('name')->where('id',$clid['teacherid'])->find();

            $p[$k]['classnum'] = $clid['cno'];
            $p[$k]['teacher'] = $teacher['name'];
        }

        $this->tp($p);
    }

    public function fsdata()
    {
        $redis = new Redis();
        if (!$redis->get('fsdata')) {
            $options = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', Session::get('schoolid'))->find();
            $re = Wechat::user($options)->getUserList();
            $data = Wechat::user($options)->getUserBatchInfo($re['data']['openid']);
            $redis->set('fsdata', $data); //添加进redis缓存
            return $data;
        } else {
            return $redis->get('fsdata'); //获取redis缓存
        }

    }

    public function tp($arr) //old
    {
        $options = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', Session::get('schoolid'))->find();

        foreach ($arr as $k => $v) {
            $data=[
                    'touser'=>$v['openid'],  //openid
                    'template_id'=>'CP9SOQX6V2Z959bOTnDB6dc4FmpzHHg0Oj1NDc3jEeE',   //模板Id
                    'url'=>'http://xzedu.rmnsw58.com/wap/index/checkresult?id=4',   //详情链接
                    'topcolor'=>"#FF0000",
                    'data'=>array(
                        'first'=>array('value'=>$v['task'],'color'=>"#743A3A"),
                        'keyword1'=>array('value'=>$v['name'],'color'=>'#743A3A'),
                        'keyword2'=>array('value'=>$v['card'],'color'=>'#743A3A'),
                        'keyword3'=>array('value'=>"1年组".$v['classnum']."班",'color'=>'#743A3A'),
                        'remark'=>array('value'=>'感谢你的关注与参与','color'=>'#743A3A'),
                    )
                ];
            $re = Wechat::message($options)->sendTemplateMessage($data);
        }

        if ($re) {

            $this->success('群发成功');
        }else{

            $this->error('群发失败');
        }
    }


    // public function tp() //old
    // {
    //     $options = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', Session::get('schoolid'))->find();
    //     $data=[
    //             'touser'=>'oKIYi0lgzlVd9R0M1rNDlf5iAIS4',  //openid
    //             'template_id'=>'CP9SOQX6V2Z959bOTnDB6dc4FmpzHHg0Oj1NDc3jEeE',   //模板Id
    //             'url'=>'http://xzedu.rmnsw58.com/wap/index/checkresult?id=4',   //详情链接
    //             'topcolor'=>"#FF0000",
    //             'data'=>array(
    //                 'first'=>array('value'=>"2017届1年组分班结果",'color'=>"#743A3A"),
    //                 'keyword1'=>array('value'=>"张梓涵",'color'=>'#743A3A'),
    //                 'keyword2'=>array('value'=>"230624198401240017",'color'=>'#743A3A'),
    //                 'keyword3'=>array('value'=>"1年组10班",'color'=>'#743A3A'),
    //                 'remark'=>array('value'=>'感谢你的关注与参与','color'=>'#743A3A'),
    //             )
    //         ];
    //     $re = Wechat::message($options)->sendTemplateMessage($data);
    //     dump($re);die;
    // }


    // public function fs()
    // {
    //     $options = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', Session::get('schoolid'))->find();
    //     // $re = Wechat::user($options)->getUserList();
    //     // $data = Wechat::user($options)->getUserBatchInfo($re['data']['openid']);
    //     // dump($data);die;
    //     // $this->assign('userlist', $data);
    //     // return view();

    //     $content = "5566";
    //     $data = array(
    //         "touser" => array(
    //             "oKIYi0gSd-crsN_1toe0qVfq5ysI",
    //             "oKIYi0tpABkd0iQtIWuBET8XmM90",
    //         ),
    //         "msgtype" => "text",
    //         "text" => array("content" => $content),

    //     );

    //     $data = json_encode($data);
    //     // dump($data);
    //     // $re = Wechat::Message($options)->sendMassMessage($data);

    //     dump($data);die;

    // }
}
