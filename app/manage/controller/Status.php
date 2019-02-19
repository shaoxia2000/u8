<?php

namespace app\manage\controller;

use app\common\model\School as SchoolModel;
use app\common\model\Teacher as TeacherModel;
use think\Session;

class Status extends Common
{
	/**
	 * 学校管理列表首页
	 */
	public function index()
	{
		$name = input('name');
		$name && $map['schname'] = ['like', '%' . $name . '%'];
		Session::has('area_id') && $map['areaid'] = ['eq', Session::get('area_id')];
		Session::get('school_id')!=0 && $map['schid'] = ['eq', Session::get('school_id')];
		$query = ['query' => request()->param()];
		$data = SchoolModel::where($map)->paginate($query);
		$this->assign(array('data' => $data));
		return view();
	}
	
}
