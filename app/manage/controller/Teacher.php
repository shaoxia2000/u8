<?php

namespace app\manage\controller;

use app\common\model\School as SchoolModel;
use app\common\model\Teacher as TeacherModel;
use think\Session;

class Teacher extends Common
{
	/**
	 * 学校管理列表首页
	 */
	public function index()
	{
		$name = input('name');
		$name && $map['schname'] = ['like', '%' . $name . '%'];
		$map['schtype'] = ['neq', 3];
		Session::has('area_id') && $map['areaid'] = ['eq', Session::get('area_id')];
		Session::get('school_id') != 0 && $map['schid'] = ['eq', Session::get('school_id')];
		$query = ['query' => request()->param()];
		$data = SchoolModel::where($map)->paginate($query);
//		$ss = db()->getLastSql();
//		halt($ss);
		$this->assign(array('data' => $data));
		return view();
	}
	
	
	/**
	 * 在学校列表上点击跳入教师列表展示
	 */
	public function tview()
	{
		$name = input('name');
		$name && $map['name'] = ['like', '%' . $name . '%'];
		$schid = input('schid');
		$schid && $map['schid'] = ['eq', $schid];
		$query = ['query' => request()->param()];
		$data = TeacherModel::where($map)->paginate($query);
		$this->assign('data', $data);
		return view();
	}
	
	public function add()
	{
		if (request()->isPost()) {
			$data = input('post.');
			$result = TeacherModel::Createteacher($data);
			if ($result) {
				$remsg['code'] = 100;
				$remsg['msg'] = "提交成功";
			} else {
				$remsg['code'] = 101;
				$remsg['msg'] = "提交失败";
			}
			return json($remsg);
		}
		return view();
	}
	
	
	public function edit()
	{
		if (request()->isPost()) {
			$data = input('post.');
			$where = ['id' => $data['id']];
			$field = ['name', 'sex', 'duty', 'xueke', 'xueli', 'school', 'age', 'teachage', 'tel', 'thumb'];
			$result = TeacherModel::update($data, $where, $field);
			if ($result) {
				$remsg['code'] = 100;
				$remsg['msg'] = "修改成功";
			} else {
				$remsg['code'] = 101;
				$remsg['msg'] = "修改失败";
			}
			return json($remsg);
		}
		$res = TeacherModel::where('id', input('id'))->find();
		if (!$res) {
			$this->error('该教师不存在！');
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
		$res = TeacherModel::destroy($id);
		if ($res) {
			return true;
		} else {
			return false;
		}
	}
	
	
}
