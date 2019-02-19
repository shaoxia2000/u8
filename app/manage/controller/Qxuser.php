<?php

namespace app\manage\controller;

use app\common\model\Qxuser as QxuserModel;
use think\Session;

class Qxuser extends Common
{
    /**
     * 分组人员管理列表首页
     */
    public function index()
    {
        $name = input('name');
        $map['id'] = ['gt', 0];
        $name && $map['name'] = ['like', '%' . $name . '%'];
	    Session::has('area_id') && $map['areaid'] = ['eq', Session::get('area_id')];
        $query = ['query' => request()->param()];
        $data = QxuserModel::where($map)->paginate($query);
        $this->assign('data', $data);
        return view();
    }

    public function add()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $result = QxuserModel::Createqxuser($data);
            if ($result) {
                $remsg['code'] = 100;
                $remsg['msg'] = "提交成功";
            } else {
                $remsg['code'] = 101;
                $remsg['msg'] = "提交失败";
            }
            return $remsg;
        }
        return view();
    }


    public function edit()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $where = ['id' => $data['id']];
            $field = ['name', 'type', 'tel', 'thumb'];
            $result = QxuserModel::update($data, $where, $field);
            if ($result) {
                $remsg['code'] = 100;
                $remsg['msg'] = "修改成功";
            } else {
                $remsg['code'] = 101;
                $remsg['msg'] = "修改失败";
            }
            return json($remsg);
        }
        $res = QxuserModel::where('id', input('id'))->find();
        if (!$res) {
            $this->error('该人员不存在！');
        }
        $this->assign(array('res' => $res));
        return view();
    }


    /**
     * 批量删除方法
     * @return bool
     */
    public function del()
    {
        $id = input('id');
        $res = QxuserModel::destroy($id);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }


}
