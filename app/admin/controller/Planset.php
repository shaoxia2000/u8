<?php
namespace app\admin\controller;
use think\Controller;
use think\session;
header("content-type:text/html;charset=utf-8");
class Planset extends Common
{
	public function index() {
        // if (request()->isGet()) {
        //     // 关键字
        //     $find = input('get.find');
        //     $find = trim($find);

        //     if (!empty($find)) {
        //         $find = $find;
        //     } else {
        //         $find = '';
        //     }
        //     // 下拉菜单
        //     $findxq = input('get.findxq');
        //     if (!empty($findxq)) {
        //         $findxq = $findxq;
        //     }else{
        //         if (session("schoolid")!=0) {
        //             $findxq = session("schoolid");
        //         }else{
        //             $findxq = '';
        //         }
        //     }

        // }


        // if ($findxq>0) {
            // $student = 'students'.$findxq;
            // if (!empty($find)) {

                // $data = db($student)->where('name', 'like', '%' . $find . '%')->paginate(20,false,['query'=>request()->param()]);

            // }else{

                $data = db('planset')->where('status',0)->where('schoolid',session('schoolid'))->order('id desc')->paginate(20,false,['query'=>request()->param()]);
            // }

            $page = $data->render(); 
            
        // }else{
        //     $data =array();
        //     $page = '';
        // }

        $res = array();
        foreach ($data as $key => $value) {
            // 替换学校
            $sch = db('school')->where('id',$value['schoolid'])->find();
            $res[$key]['sch'] = $sch['name'];

            // 替换老师
            $t = array();
            $teacher = db('teacher')->field('name')->where('id','in', $value['teacherid'])->select();
            foreach ($teacher as $kk => $vv) {
                $t[] = $vv['name'];
            }
            $res[$key]['teacher'] = implode(',', $t);
            $res[$key]['id'] = $value['id'];
        }

        // echo "<pre>";
        // print_r($res);exit();

        $this->assign('data', $data);
        $this->assign("page",$page);
        $this->assign('find', $find);
        $this->assign('findxq', $findxq);
        $this->assign('res', $res);
        return view();
    }


    public function add(){
        if (request()->isPost()) {

            $data = input('post.');

            $s['title'] = trim($data['title']);
            $s['studentnum'] = trim($data['studentnum']);
            $s['classnum'] = trim($data['classnum']);
            $s['remark'] = trim($data['remark']);
            $s['teacherid'] = implode(',', $data['teacherid']);

            $s['schoolid'] = session('schoolid');
            $s['time'] = time();

            // echo "<pre>";
            // print_r($s);exit();
            $res = db('Planset')->insert($s);
            if ($res) {
                logs();
                $this->success('添加招生信息成功', url('index'));
            } else {
                $this->error('添加招生信息失败');
            }
            return;
        }

        $sch = session('schoolid');
        // echo $sch;exit();
        if ($sch!=0) {
            $res = db('Teacher')->where('sch',$sch)->where('status',0)->select();
        }
        $this->assign('res', $res);
        return view();
    }

    public function edit($id){

        // if (request()->isPost()) {
        //     $data = input('post.');

        //     // echo "<pre>";
        //     // print_r($data);exit();

        //     $s['title'] = trim($data['title']);
        //     $s['studentnum'] = trim($data['studentnum']);
        //     $s['classnum'] = trim($data['classnum']);
        //     $s['remark'] = trim($data['remark']);
        //     $s['teacherid'] = implode(',', $data['teacherid']);

        //     $s['schoolid'] = session('schoolid');
        //     $s['time'] = time();

        //     $res = db('planset')->where('id', $id)->update($s);

        //     if ($res) {
        //         logs();
        //         $this->success('修改招生设置成功', url('index'));
        //     } else {
        //         $this->error('修改招生设置失败');
        //     }
        //     return;
        // }

        $res = db('planset')->find($id);
        if (!$res) {
            $this->error('该招生设置不存在！');
        }

        // 替换学校
        $sch = db('school')->where('id',$res['schoolid'])->find();
        $res['sch'] = $sch['name'];
        // 替换老师
        // $res['teacher'] = explode(',', $res['teacherid']);

        $this->assign('res', $res);

        // // 老师数组
        $schoolid = session('schoolid');
        if ($schoolid!=0) {
            $ts = db('Teacher')->where('sch',$schoolid)->where('id','in',$res['teacherid'])->select();

            // echo db()->getLastSql();die();

            // echo "<pre>";
            // print_r($ts);die();
        // }
        // // 增加checked属性
        // foreach ($ts as $kk => $vv) {
        //     foreach ($res['teacher'] as $ko => $vo) {
        //         if ($vv['id'] == $vo) {
        //             $ts[$kk]['check'] = "checked";
        //         }
        //     }
        }
        $this->assign('ts', $ts);

        return view();
    }

    public function del($id){

        $res = db('planset')->where('id', $id)->update(['status' => 1]);

        if ($res) {
            logs();
            $this->success('删除招生设置成功', url('index'));
        } else {
            $this->error('删除招生设置失败');
        }
    }

}