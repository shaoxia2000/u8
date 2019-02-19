<?php

namespace app\admin\controller;

use think\Controller;

class School extends Common
{
    public function index()
    {
        if (request()->isGet()) {
            // 关键字
            $find = input('get.find');
            if (!empty($find)) {
                $find = trim($find);
            } else {
                $find = '';
            }
            // 下拉菜单
            $findxq = input('get.findxq');
            if (empty($findxq)) {
                $findxq = '';
            }

        } else {
            $find = '';
            $findxq = '';
        }

        if (!empty($find) and !empty($findxq)) {
            $data = db('school')->where('name', 'like', '%' . $find . '%')->where('pid', $findxq)->where('status', 0)->paginate(20, false, ['type'  => 'Bootstrap', 'var_page' => 'page', //第一种方法，使用数组方式传入参数
                // 'query' => ['find'=>$find,'findxq'=>$findxq],
                //第二种方法，使用函数助手传入参数
                                                                                                                                            'query' => request()->param(),]);
        } else if (!empty($find) and empty($findxq)) {
            $data = db('school')->where('name', 'like', '%' . $find . '%')->where('status', 0)->paginate(20, false, ['type'  => 'Bootstrap', 'var_page' => 'page', //第一种方法，使用数组方式传入参数
                // 'query' => ['find'=>$find,'findxq'=>$findxq],
                //第二种方法，使用函数助手传入参数
                                                                                                                     'query' => request()->param(),]);
        } else if (empty($find) and !empty($findxq)) {
            $data = db('school')->where('pid', $findxq)->where('status', 0)->paginate(20, false, ['type'  => 'Bootstrap', 'var_page' => 'page', //第一种方法，使用数组方式传入参数
                // 'query' => ['find'=>$find,'findxq'=>$findxq],
                //第二种方法，使用函数助手传入参数
                                                                                                  'query' => request()->param(),]);
        } else {
            $data = db('school')->where('status', 0)->paginate(20);
        }

        if (empty($data)) {
            $page = '';
        } else {
            $page = $data->render();
        }

        $datatype = array();
        foreach ($data as $key => $value) {

            $arr = db('school_area')->where('id', '=', $value['pid'])->find();
            $datatype[$key]['xq'] = $arr['name'];

        }

        $this->assign('data', $data);
        $this->assign('page', $page);
        $this->assign('datatype', $datatype);
        $this->assign('find', $find);
        $this->assign('findxq', $findxq);

        $res = db('school_area')->where('status', 0)->select();
        $this->assign('res', $res);

        return view();
    }

    public function add()
    {
        if (request()->isPost()) {

            $data = input('post.');
            // 验证重复
            $r = db('school')->where('name', trim($data['name']))->find();
            if ($r) {
                $this->error('不能重复添加学校名称!');
                return;
            }
            // 写入
            $adata = array();
            $adata['name'] = trim($data['name']);
            $adata['pid'] = $data['pid'];
            $adata['stype'] = $data['stype'];
            $res = db('school')->insert($adata);
            if ($res) {
                logs();
                $this->success('添加学校成功', url('index'));
            } else {
                $this->error('添加学校失败');
            }
            return;
        }
        $res = db('school_area')->where('status',0)->select();
        $this->assign('res', $res);
        return view();
    }

    public function edit($id)
    {

        if (request()->isPost()) {
            $data = input('post.');
            $res = db('school')->where('id', $id)->update(['name' => $data['name'], 'pid' => $data['pid'],'stype' => $data['stype']]);
            if ($res) {
                logs();
                $this->success('修改学校成功', url('index'));
            } else {
                $this->error('修改学校失败');
            }
            return;
        }

        $res = db('school')->field('id,name,pid')->find($id);
        if (!$res) {
            $this->error('该学校不存在！');
        }
        $this->assign('res', $res);

        $gid = db('school')->where('id', $id)->find();
        $this->assign('gid', $gid['pid']);
        $this->assign('tid', $gid['stype']);

        $resfl = db('school_area')->where('status',0)->select();
        $this->assign('resfl', $resfl);
        return view();
    }

    public function del($id)
    {
        $res = db('school')->where('id', $id)->update(['status' => 1]);
        if ($res) {
            logs();
            $this->success('删除学校成功', url('index'));
        } else {
            $this->error('删除学校失败');
        }
    }

}
