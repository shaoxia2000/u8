<?php

namespace app\manage\controller;

use think\Session;
use app\common\model\Teachergroup as TeachergroupModel;

class Daochu extends Common
{
	
	public function index()
	{
		$schid = input('schid');
		$name = GetSchoolname($schid) . "-分组结果" . ".xls";
		$header = ['校名', '姓名', '性别', '身份证号码', '初中学校', '中考总分', '语文', '数学', '外语', '物理', '化学', '地理', '生物', '历史', '政治', '体育', '所在层', '所在组'];
		$data = db('classes' . $schid)->order('zcjbk desc')->select();
		foreach ($data as $k => $v) {
			$m['schoolname'] = GetSchoolname($schid);
			$m['name'] = $v['name'];
			$m['sex'] = $v['sex'];
			$m['id_num'] = db('students' . $schid)->where('id', $v['id'])->value('id_num') . ' ';
			$m['junior_school'] = db('students' . $schid)->where('id', $v['id'])->value('junior_school');
			$m['zcjbk'] = db('students' . $schid)->where('id', $v['id'])->value('zcjbk');
			$m['chinese'] = db('students' . $schid)->where('id', $v['id'])->value('chinese');
			$m['smath'] = db('students' . $schid)->where('id', $v['id'])->value('smath');
			$m['english'] = db('students' . $schid)->where('id', $v['id'])->value('english');
			$m['physics'] = db('students' . $schid)->where('id', $v['id'])->value('physics');
			$m['chemistry'] = db('students' . $schid)->where('id', $v['id'])->value('chemistry');
			$m['geography'] = db('students' . $schid)->where('id', $v['id'])->value('geography');
			$m['biologic'] = db('students' . $schid)->where('id', $v['id'])->value('biologic');
			$m['history'] = db('students' . $schid)->where('id', $v['id'])->value('history');
			$m['politics'] = db('students' . $schid)->where('id', $v['id'])->value('politics');
			$m['sports'] = db('students' . $schid)->where('id', $v['id'])->value('sports');
			$m['fcengid'] = ($v['fcengid'] + 1) . '层';
			$m['cno'] = IntToChr($v['cno'] - 1) . '组';
			$arr[] = $m;
		}
		excelExport($name, $header, $arr);
	}
	
	
	public function indexx()
	{
		$schid = input('schid');
		$name = GetSchoolname($schid) . "-分组结果" . ".xls";
		$header = ['校名', '姓名', '性别', '身份证号码', '所在组'];
		$data = db('classes' . $schid)->order('cno asc')->select();
		foreach ($data as $k => $v) {
			$m['schoolname'] = GetSchoolname($schid);
			$m['name'] = $v['name'];
			$m['sex'] = $v['sex'];
			$m['id_num'] = db('students' . $schid)->where('id', $v['id'])->value('id_num') . ' ';
			$m['cno'] = IntToChr($v['cno'] - 1) . '组';
			$arr[] = $m;
		}
		excelExport($name, $header, $arr);
	}
	
	
	public function indexc()
	{
		$schid = input('schid');
		$name = GetSchoolname($schid) . "-分组结果" . ".xls";
		$header = ['校名', '姓名', '性别', '身份证号码', '中考总分', '语文', '数学', '外语', '所在组'];
		$data = db('classes' . $schid)->order('zcj desc')->select();
		foreach ($data as $k => $v) {
			$m['schoolname'] = GetSchoolname($schid);
			$m['name'] = $v['name'];
			$m['sex'] = $v['sex'];
			$m['id_num'] = db('students' . $schid)->where('id', $v['id'])->value('id_num') . ' ';
			$m['zcj'] = db('students' . $schid)->where('id', $v['id'])->value('zcj');
			$m['chinese'] = db('students' . $schid)->where('id', $v['id'])->value('chinese');
			$m['smath'] = db('students' . $schid)->where('id', $v['id'])->value('smath');
			$m['english'] = db('students' . $schid)->where('id', $v['id'])->value('english');
			$m['cno'] = IntToChr($v['cno'] - 1) . '组';
			$arr[] = $m;
		}
		excelExport($name, $header, $arr);
	}
	
	
	//分班结果导出 开始
	public function indexfb()
	{
		$schid = input('schid');
		$gid = input('id');
		$schtype = GetSchooltype($schid);
		$name = GetSchoolname($schid) . "-分组结果" . ".xls";
		if ($schtype == 1) {
			$header = ['校名', '姓名', '性别', '身份证号码', '所在组', '所在班', '班主任'];
			$data = db('classes' . $schid)->select();
			foreach ($data as $k => $v) {
				$m['schoolname'] = GetSchoolname($schid);
				$m['name'] = $v['name'];
				$m['sex'] = $v['sex'];
				$m['id_num'] = db('students' . $schid)->where('id', $v['id'])->value('id_num') . ' ';
				$m['cno'] = IntToChr($v['cno'] - 1) . '组';
				$m['bjh'] = ($v['tgroupid'] + 1) . '班';
				$m['bzr'] = Getbigtname($gid, $v['tgroupid']);
				$arr[] = $m;
			}
		}
		
		if ($schtype == 2) {
			$header = ['校名', '姓名', '性别', '身份证号码', '小学学校', '中考总分', '语文', '数学', '外语', '所在组', '所在班', '班主任'];
			$data = db('classes' . $schid)->order('zcj desc')->select();
			foreach ($data as $k => $v) {
				$m['schoolname'] = GetSchoolname($schid);
				$m['name'] = $v['name'];
				$m['sex'] = $v['sex'];
				$m['id_num'] = db('students' . $schid)->where('id', $v['id'])->value('id_num') . ' ';
				$m['graduate'] = db('students' . $schid)->where('id', $v['id'])->value('graduate');
				$m['zcj'] = db('students' . $schid)->where('id', $v['id'])->value('zcj');
				
				$m['chinese'] = db('students' . $schid)->where('id', $v['id'])->value('chinese');
				$m['smath'] = db('students' . $schid)->where('id', $v['id'])->value('smath');
				$m['english'] = db('students' . $schid)->where('id', $v['id'])->value('english');
				$m['cno'] = IntToChr($v['cno'] - 1) . '组';
				$m['bjh'] = ($v['tgroupid'] + 1) . '班';
				$m['bzr'] = Getbigtname($gid, $v['tgroupid']);
				$arr[] = $m;
			}
		}
		
		if ($schtype == 3) {
			$header = ['校名', '姓名', '性别', '身份证号码', '初中学校', '中考总分', '语文', '数学', '外语', '物理', '化学', '地理', '生物', '历史', '政治', '体育', '所在层', '所在组', '所在班', '班主任'];
			$data = db('classes' . $schid)->order('zcj desc')->select();
			foreach ($data as $k => $v) {
				$m['schoolname'] = GetSchoolname($schid);
				$m['name'] = $v['name'];
				$m['sex'] = $v['sex'];
				$m['id_num'] = db('students' . $schid)->where('id', $v['id'])->value('id_num') . ' ';
				$m['junior_school'] = db('students' . $schid)->where('id', $v['id'])->value('junior_school');
				if (Session::get('area_id') == 10) {
					$m['zcj'] = db('students' . $schid)->where('id', $v['id'])->value('zcj');
				} else {
					$m['zcj'] = db('students' . $schid)->where('id', $v['id'])->value('zcjbk');
				}
				$m['chinese'] = db('students' . $schid)->where('id', $v['id'])->value('chinese');
				$m['smath'] = db('students' . $schid)->where('id', $v['id'])->value('smath');
				$m['english'] = db('students' . $schid)->where('id', $v['id'])->value('english');
				$m['physics'] = db('students' . $schid)->where('id', $v['id'])->value('physics');
				$m['chemistry'] = db('students' . $schid)->where('id', $v['id'])->value('chemistry');
				$m['geography'] = db('students' . $schid)->where('id', $v['id'])->value('geography');
				$m['biologic'] = db('students' . $schid)->where('id', $v['id'])->value('biologic');
				$m['history'] = db('students' . $schid)->where('id', $v['id'])->value('history');
				$m['politics'] = db('students' . $schid)->where('id', $v['id'])->value('politics');
				$m['sports'] = db('students' . $schid)->where('id', $v['id'])->value('sports');
				$m['fcengid'] = ($v['fcengid'] + 1) . '层';
				$m['cno'] = IntToChr($v['cno'] - 1) . '组';
				$m['bjh'] = ($v['tgroupid'] + 1) . '班';
				$m['bzr'] = Getbigtname($gid, $v['tgroupid']);
				$arr[] = $m;
			}
		}
		
		
		excelExport($name, $header, $arr);
	}
	
	
	public function indexs()
	{
		$schid = input('schid');
		$name = GetSchoolname($schid) . "-分组结果" . ".xls";
		$header = ['校名', '姓名', '层数', '组号', '身份证'];
		$data = db('classes' . $schid)->order('zcj desc')->select();
		foreach ($data as $k => $v) {
			$m['schoolname'] = GetSchoolname($schid);
			$m['name'] = $v['name'];
			$m['fcengid'] = $v['fcengid'] + 1;
			$m['cno'] = IntToChr($v['cno'] - 1) . '组';
			$m['sfz'] = db('students' . $schid)->where('id', $v['id'])->value('id_num') . ' ';
			$arr[] = $m;
		}
		excelExport($name, $header, $arr);
	}
	
	
	public function teachergroup()
	{
		$areaid = input('areaid');
		$name = Getareaname($areaid) . "-统计结果" . ".xls";
		$header = ['学校总数', '小学数量', '初中数量', '总人数', '小学人数', '初中人数', '分班总数', '小学分班数', '初中分班数'];
		$data = TeachergroupModel::where(['areaid' => $areaid, 'status' => 1, 'astatus' => 1])->where('schtype', 'neq', 3)->select();
		$xdata = TeachergroupModel::where(['areaid' => $areaid, 'status' => 1, 'astatus' => 1, 'schtype' => 1])->select();
		$cdata = TeachergroupModel::where(['areaid' => $areaid, 'status' => 1, 'astatus' => 1, 'schtype' => 2])->select();
		
		foreach ($data as $k => $v) {
			$mzcount[] = db('students' . $v['schid'])->count();
		}
		foreach ($xdata as $k => $v) {
			$mxcount[] = db('students' . $v['schid'])->count();
		}
		foreach ($cdata as $k => $v) {
			$mccount[] = db('students' . $v['schid'])->count();
		}
		
		
		$xtnum = TeachergroupModel::where(['areaid' => $areaid, 'status' => 1, 'astatus' => 1, 'schtype' => 1])->sum('tnum');
		$ctnum = TeachergroupModel::where(['areaid' => $areaid, 'status' => 1, 'astatus' => 1, 'schtype' => 2])->sum('tnum');
		$ztnum = $xtnum + $ctnum;
		
		$m['zcount'] = count($data);
		$m['xcount'] = count($xdata);
		$m['ccount'] = count($cdata);
		$m['zpnum'] = array_sum($mzcount);
		$m['xpnum'] = array_sum($mxcount);
		$m['cpnum'] = array_sum($mccount);
		$m['ztnum'] = $ztnum;
		$m['xtnum'] = $xtnum;
		$m['ctnum'] = $ctnum;
		$arr[] = $m;
		excelExport($name, $header, $arr);
	}
	
	
}
