<?php
namespace app\admin\controller;
use think\Controller;

class Schoolarea extends Common
{
    public function index()
    {
        if (request()->isGet()) {
            $find = input('get.find');
            $find = trim($find);
        } else {
            $find = '';
        }

        $data = db('school_area')->where('name', 'like', '%' . $find . '%')->where('status',0)->paginate(20,false,['query'=>request()->param()]);

        if (empty($data)) {
            $page = '';
        }else{
            $page = $data->render();
        }

        $this->assign('data', $data);
        $this ->assign("page",$page);
        $this->assign('find', $find);
        return view();
    }

    public function add()
    {
        if (request()->isPost()) {
            $data = input('post.');

            // 验证重复
            $r = db('school_area')->where('name',trim($data['name']))->find();
            if ($r) {
                $this->error('不能重复添加校区名称!');
                return;
            }

            // 写入
            $adata = array();
            $adata['name'] = $data['name'];
            $res = db('school_area')->insert($adata);
            if ($res) {

                logs();
                $this->success('添加校区成功', url('index'));
            } else {
                $this->error('添加校区失败');
            }
            return;
        }
        $data = db('school_area')->select();
        $this->assign('data', $data);
        return view();
    }

    public function edit($id)
    {
        if (request()->isPost()) {
            $data = input('post.');
            $res = db('school_area')->where('id', $id)->update(['name' => $data['name']]);
            if ($res) {
                
                logs();
                $this->success('修改校区成功', url('index'));
            } else {
                $this->error('修改校区失败');
            }
            return;
        }

        $res = db('school_area')->field('id,name')->find($id);
        if (!$res) {
            $this->error('该校区不存在！');
        }

        $this->assign('res', $res);
        return view();
    }

    public function del($id)
    {
        $res = db('school_area')->where('id',$id)->update(['status'=>1]);
        if ($res) {
            logs();
            $this->success('删除校区成功', url('index'));
        } else {
            $this->error('删除校区失败');
        }
    }

}
