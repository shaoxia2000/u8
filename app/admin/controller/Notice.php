<?php
namespace app\admin\controller;
use think\Controller;
use think\session;

header("content-type:text/html;charset=utf-8");
class Notice extends Common
{
	public function index(){

        $find = '';
        $data = array();
        $datatype = array();

        if (request()->isGet()) {
            // 关键字
            $find = trim(input('get.find'));
        }

        $data = db('Notice')->field('id,title,from_user,time,to_user,to_user_group')->where('from_user',session('id'))->where('title','like','%'.$find.'%')->order('time desc')->paginate(20,false,['query'=>request()->param()]);

        foreach ($data as $key => $value) {
            // 替换发送者姓名
        	$user = db("admin")->where('id',$value['from_user'])->find();
        	$datatype[$key]['from_user'] = $user['name'];
            // 时间戳
        	$datatype[$key]['time'] = date('Y-m-d H:i:s',$value['time']);
        }

        $this->assign('data', $data);
        $this->assign('datatype', $datatype);
        $this->assign('find', $find);
        return view();
    }

    public function add(){
        if (request()->isPost()) {

            $data = input('post.');
            // 01 notice
            $s = array();
            $s['title'] = trim($data['title']);
            $s['to_user'] = implode($data['rules'], ',');
            $s['to_user_group'] = implode($data['rules_group'], ',');
            $s['content'] = trim($data['content']);
            $s['from_user'] = session('id');
            $s['time'] = time();
            $res = db('Notice')->insert($s);

            // 02 notice_receive
            $sr['noticeid'] = db('Notice')->getLastInsID();

            foreach ($data['rules'] as $k => $v) {
                $sr['to_user'] = $v;
                $r = db('notice_receive')->insert($sr); 
            }

            if ($res) {
                if ($r) {
                    logs();
                    $this->success('发布成功', url('index'));
                } else {
                    $this->error('发布失败');
                }
            } else {
                $this->error('发布失败');
            }
            return;
        }
        // 管理组
        $res = db('auth_group')->field('id,title')->select();
        // 组下用户
        $res_a = array();
        foreach ($res as $k => $v) {

            $res[$k]['level'] = 0;
            $res[$k]['pid'] = 0;
            $res[$k]['dataid'] = $res[$k]['id'];
            $res_a[]=$res[$k];

            $c = db('auth_group_access')->where("group_id",$v['id'])->select();
            foreach ($c as $ko => $vo) {
                $child[]=$vo;
            }

            foreach ($child as $kk => $vv) {
                $admin = db("admin")->where("id",$vv['uid'])->find();
                $s['id'] = $admin['id'];
                $s['title'] = $admin['name'];
                $s['level'] = 1;
                $s['pid'] = $vv['group_id'];
                $s['dataid'] = $vv['group_id']."-".$vv['uid'];
                $res_a[]=$s;
                unset($child[$kk]);
            }
        }

        // echo "<pre>";
        // print_r($res_a);exit();

        $this->assign('res', $res_a);
        return view();
    }

    public function del($id){
        $res = db('Notice')->delete($id);
        if ($res) {
            logs();
            $this->success('删除通知成功', url('index'));
        } else {
            $this->error('删除通知失败');
        }
    }

    public function show(){
        
        $id = input('id');
        $data = db('notice')->where('id', $id)->field('title,from_user,time,content,to_user,to_user_group')->find();

        // 替换
        $data['date'] = date('Y-m-d H:i:s', $data['time']);
        $user = db('admin')->where('id',$data['from_user'])->find();
        $data['user'] = $user['name'];

        //  替换 接收者
        $to_user = db('admin')->field('name')->where('id','in',$data['to_user'])->select();

        foreach ($to_user as $k => $v) {
            $to_user_name .= $v['name'].',';
        }
        $data['to_user'] = trim($to_user_name,',');

        echo json_encode($data);
    }



    // public function sort($data,$pid=0,$level=0)
    // {
    //     static $arr=array();
    //     foreach ($data as $k => $v) {
    //         if($v['pid']==$pid){
    //             $v['level']=$level;
    //             $v['dataid']=$this->getparentid($v['id']);
    //             $arr[]=$v;
    //             $this->sort($data,$v['id'],$level+1);
    //         }
    //     }
    //     return $arr;
    // }

    // public function getparentid($id)
    // {
    //   $res=db('auth_rule')->select();
    //   return $this->_getparentid($res,$id,True);
    // }

    // public function _getparentid($data,$cateid,$clear=False)
    // {
    //     static $arr=array();
    //     if($clear){
    //       	$arr=array();
    //     }
    //     foreach ($data as $k => $v) {
    //         if($v['id']==$cateid){
    //             $arr[]=$v['id'];
    //             $this->_getparentid($data,$v['pid'],False);
    //         }
    //     }
    //     asort($arr);
    //     // dump($arr);die;
    //     $arrStr=implode("-",$arr);
    //     return $arrStr;
    // }

    // public function edit($id){

    //     if (request()->isPost()) {

    //         $data = input('post.');
    //         $to_user = implode(',',$data['rules']);
    //         $update = array();
    //         $update['title'] = $data['title'];
    //         $update['content'] = $data['content'];
    //         $update['to_user'] = $to_user;

    //         $res = db('Notice')->where('id', $id)->update($update);

    //         if ($res) {
    //             logs();
    //             $this->success('修改站内信通知成功', url('index'));
    //         } else {
    //             $this->error('修改站内信通知失败');
    //         }
    //         return;
    //     }

    //     $notice = db('Notice')->find($id);
    //     if (!$notice) {
    //     	$this->error('该通知不存在！');
    //     }

    //     // $res = db('auth_group')->field('id,title')->select();
    //     $res = db('Notice')->find($id);
    //     $this->assign('res', $res);
    //     // $res=db('auth_group')->field('id,title,status,rules')->find($id);
    //     // $res_check = explode( ',',$res_check['to_user']);
    //     // // echo "<pre>";
    //     // // print_r($res_check);exit();


    //     // 管理组
    //     $res = db('auth_group')->field('id,title')->select();
    //     // 组下用户
    //     $res_a = array();
    //     foreach ($res as $k => $v) {

    //         $res[$k]['level'] = 0;
    //         $res[$k]['pid'] = 0;
    //         $res[$k]['dataid'] = $res[$k]['id'];
    //         $res_a[]=$res[$k];

    //         $c = db('auth_group_access')->where("group_id",$v['id'])->select();
    //         foreach ($c as $ko => $vo) {
    //             $child[]=$vo;
    //         }

    //         foreach ($child as $kk => $vv) {
    //             $admin = db("admin")->where("id",$vv['uid'])->find();
    //             $s['id'] = $admin['id'];
    //             $s['title'] = $admin['name'];
    //             $s['level'] = 1;
    //             $s['pid'] = $vv['group_id'];
    //             $s['dataid'] = $vv['group_id']."-".$vv['uid'];
    //             $res_a[]=$s;
    //             unset($child[$kk]);
    //         }

    //     }

    //     // echo "<pre>";
    //     // print_r($res_a);exit();

    //     $this->assign('resfl', $res_a);
    //     $this->assign('notice', $notice);
        
    //     // $this->assign('res_check', $res_check);
    //     $this->assign('id', $id);
    //     return view();
    // }

}