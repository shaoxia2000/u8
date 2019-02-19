<?php

namespace app\manage\controller;

use app\common\model\Teachergroup as TeachergroupModel;
use think\Session;

class Bigscrt extends Common
{
	/**
	 * 后台管理首页
	 */
	public function setup()
	{
		$data = TeachergroupModel::where(['status' => 1, 'astatus' => 1, 'areaid' => Session::get('area_id')])->paginate();
		$this->assign(array('data' => $data));
		return view();
	}
	
	public function startone()
	{
		return view();
	}
	
	
}
