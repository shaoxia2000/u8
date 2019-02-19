<?php
namespace app\admin\controller;
use think\Controller;
use think\session;
header("content-type:text/html;charset=utf-8");
class Student extends Common
{
    // public function lst(){

    //     $task = 'task'.Session::get('schoolid');
    //     $data = db($task)->order("id desc")->paginate(20);
    //     foreach ($data as $k => $v) {
    //         $datatype[$k]['time'] = date("Y-m-d H:i:s",$v['time']);
    //     }
    //     $this->assign('data', $data);
    //     $this->assign('datatype', $datatype);
    //     return view();
    // }


	public function index()
    {

        $mm = db();
        $taskdb = 'task' . Session::get('schoolid');
        $taskdbpre = 'bk_task' . Session::get('schoolid');
        $isTable = $mm->query("SHOW TABLES LIKE '{$taskdbpre}'");
        // dump($isTable);die;
        if (!$isTable) {
            $this->error('请先创建任务！', url('excel/index'));
        }
        if (request()->isGet()) {
            // 关键字
            $find = input('get.find');
            $find = trim($find);

            if (!empty($find)) {
                $find = $find;
            } else {
                $find = '';
            }
            // 下拉菜单
            $findxq = input('get.findxq');
            if (!empty($findxq)) {
                $findxq = $findxq;
            }else{
                if (session("schoolid")!=0) {
                    $findxq = session("schoolid");
                }else{
                    $findxq = '';
                }
            }

        }else {

            $find = '';
            if (session("schoolid")!=0) {
                $findxq = session("schoolid");
            }else{
                $findxq = '';
            }
        }


        if ($findxq>0) {
            $student = 'students'.$findxq;
            if (!empty($find)) {

                $data = db($student)->where('name', 'like', '%' . $find . '%')->paginate(20,false,['query'=>request()->param()]);
            }else{

                $data = db($student)->paginate(20,false,['query'=>request()->param()]);
            }

            $page = $data->render(); 
            
        }else{
            $data =array();
            $page = '';
        }

        $this->assign('data', $data);
        $this->assign("page",$page);
        $this->assign('find', $find);
        $this->assign('findxq', $findxq);

        $sch = session("schoolid");
        if ($sch!=0) {
            $res = db('school')->where('id',$sch)->where('status',0)->select();
        }else{
            $res = db('school')->where('status',0)->select();
        }
        
        $this->assign('res', $res);

        return view();
    }

    // public function trim_empty($u){
    //     if (empty(trim($u))) {
    //         $this->error('为空,添加失败');
    //     }
    // }

    public function add(){
        if (request()->isPost()) {

            $data = input('post.');

            // 姓名 性别 出生日期
            $s['name'] = trim($data['name']);
            $s['sex'] = $data['sex'];

            $s['birthday'] = trim($data['birthday']);
            $s['birthday'] = strtotime($s['birthday']);
            // 民族 籍贯
            $s['nation'] = trim($data['nation']);
            $s['address'] = trim($data['address']);
            // 落户时间
            $s['in_time'] = trim($data['in_time']);
            $s['in_time'] = strtotime($s['in_time']);

            // 身份证
            $s['id_type'] = $data['id_type'];
            $s['id_num'] = trim($data['id_num']);
            // 独生子女 残疾
            $s['single_is'] = $data['single_is'];
            $s['disabled_is'] = $data['disabled_is'];

            // 房屋
            $s['house_owner'] = trim($data['house_owner']);
            $s['house_relation'] = $data['house_relation'];
            $s['house_address'] = trim($data['house_address']);
            $s['house_type'] = $data['house_type'];

            // 落户时间
            $s['buy_time'] = trim($data['buy_time']);
            $s['buy_time'] = strtotime($s['buy_time']);

            // 家长1
            $s['name_two'] = trim($data['name_two']);
            $s['relation_two'] = $data['relation_two'];
            $s['job_two'] = trim($data['job_two']);
            $s['tel_two'] = trim($data['tel_two']);

            // 家长2
            $s['name_three'] = trim($data['name_three']);
            $s['relation_three'] = $data['relation_three'];
            $s['job_three'] = trim($data['job_three']);
            $s['tel_three'] = trim($data['tel_three']);

            // 填表人
            $s['writer'] = trim($data['writer']);
            $s['writer_relation'] = $data['writer_relation'];
            $s['time'] = time();
            
            
            $res = db('Stu')->insert($s);
            if ($res) {
                logs();
                $this->success('添加学生档案成功', url('index'));
            } else {
                $this->error('添加学生档案失败');
            }
            return;
        }

        // $res = db('Stu')->select();
        // $this->assign('res', $res);
        return view();
    }

    public function edit($id){

        if (request()->isPost()) {
            $data = input('post.');

            $data['birthday'] = strtotime($data['birthday']);
            $data['in_time'] = strtotime($data['in_time']);
            $data['buy_time'] = strtotime($data['buy_time']);

            $res = db('Stu')->where('id', $id)->update($data);

            if ($res) {

                logs();
                $this->success('修改教师信息成功', url('index'));
            } else {
                $this->error('修改教师信息失败');
            }
            return;
        }

        $res = db('Stu')->find($id);
        if (!$res) {
            $this->error('该学生不存在！');
        }

        $res['birthday'] = date("Y-m-d", $res['birthday']);
        $res['in_time'] = date("Y-m-d", $res['in_time']);
        $res['buy_time'] = date("Y-m-d", $res['buy_time']);

        $this->assign('res', $res);
        return view();
    }

    public function del($id){

        $res = db('Stu')->delete($id);

        if ($res) {
            logs();
            $this->success('删除学生档案成功', url('index'));
        } else {
            $this->error('删除学生档案失败');
        }
    }

}