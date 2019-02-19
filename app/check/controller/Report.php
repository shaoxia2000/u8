<?php
namespace app\check\controller;

use app\check\logic\ReportLogic;
use app\check\model\School;
class Report extends Common{
	public function index(){
		$sid = input("param.sid/d",0);

		ReportLogic::setSchool($sid);
		$school = ReportLogic::getSchool();

		if(!in_array($school['schtype'], [1,2])){
			return $this->error('error');
		}

		$this->assign('school', $school);

		$tableName = config('database.prefix').'students'.$sid;

		if(!tableExits($tableName)){
			return view();
		}
		
		$config = getCheckFieldConfig($school['schtype']);
		
		$datas = ReportLogic::getSchoolAverAgeData($sid, 'warming', $config);

		$this->assign('datas', $datas);
		if($school['schtype'] != '1'){
			$this->assign('average', db()->table($tableName)->field($config['avg'])->find());
		}
		$this->assign('field', $config['fielda']);
		$this->assign('ptitle', ReportLogic::getPublicTitle());

		// $sexs = db()->table($tableName)->field('count(1) as total,sex')->group('sex')->select()->toArray();

		return view();

	}
	public function average(){
		$sid = input('param.sid/d', 0);
		$aid = input('param.aid/d', 0);

		// 加一个视图

		if($sid){
			$excel = ReportLogic::reportSchoolAverage($sid);
		}elseif($aid){

			// 区导出成绩  使用上一次刷新的post数据  在dataCheck里做的session存储
			$sids = [];
			if(!empty(session('last_post'))){
				$sids = session('last_post.'.$aid);
			}

			$excel = ReportLogic::reportAreaAverage($aid, $sids);
		}

		if(is_string($excel)){
			$this->error($excel,'index/index');
		}

	}
  
 
}