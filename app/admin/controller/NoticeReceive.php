<?php
namespace app\admin\controller;
use think\Controller;
use think\session;

header("content-type:text/html;charset=utf-8");
class NoticeReceive extends Common
{
	public function index(){

        $find = '';
        $data = array();
        $datatype = array();

        if (request()->isGet()) {
            // 关键字
            $find = trim(input('get.find'));
        }

        $data = db('Notice_receive')->field('id,noticeid,status')->where('to_user',session('id'))->order('id',DESC)->paginate(20,false,['query'=>request()->param()]);

        foreach ($data as $key => $value) {
            // 替换 信息条
            $notice = db("Notice")->field('id,title,from_user,time')->where('id',$value['noticeid'])->find();
            $datatype[$key] = $notice;

            if ($value['status']==1) {
                $datatype[$key]['status']='已读';
            }else{
                $datatype[$key]['status']='未读';
            }
        }

        // 替换 文字
        foreach ($datatype as $key => $value) {
            // 时间戳
            $datatype[$key]['time'] = date('Y-m-d H:i:s',$datatype[$key]['time']);
            // 发送者
            $from_user = db('Admin')->field('name')->where('id',$value['from_user'])->find();
             $datatype[$key]['from_user'] = $from_user['name'];
        }

        $this->assign('data', $data);
        $this->assign('datatype', $datatype);

        // echo "<pre>";
        // print_r($data);exit();
        $this->assign('find', $find);
        return view();
    }
    public function del($id){
        $res = db('Notice_receive')->delete($id);
        if ($res) {
            logs();
            $this->success('删除站内信通知成功', url('index'));
        } else {
            $this->error('删除站内信通知失败');
        }
    }

    public function show(){
        
        $id = input('id');
        $data = db('Notice')->field('title,from_user,content,time')->where('id',$id)->find();

        // 替换 时间戳
        $data['time'] = date('Y-m-d H:i:s',$data['time']);
        // 发送者
        $from_user = db('Admin')->field('name')->where('id',$data['from_user'])->find();
        $data['from_user'] = $from_user['name'];

        // 更新
        $r = db('Notice_receive')->where('to_user', session('id'))->where('noticeid', $id)->update(['status'=>1]);

        // num
        $note = db('Notice_receive')->where('to_user',session('id'))->where('status',0)->count();

        $data['notenum'] = $note;

        echo json_encode($data);
    }

    public function num(){
        $note = db('Notice_receive')->where('to_user',session('id'))->where('status',0)->count();
        echo json_encode($note);
    }

}