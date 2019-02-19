<?php
namespace app\admin\controller;
use think\Controller;
header("content-type:text/html;charset=utf-8");
class logs extends Common
{
	public function index(){

		$list = db('logs') ->order('time',desc)->paginate(50);
		foreach ($list as $key => $value) 
		{
			$rex[$key]['date'] = date( "Y-m-d H:i:s", $value['time'] );
		}
		$this->assign('rex',$rex);
		$this->assign('list',$list);
	    return view();
	}
}