<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/9 0009
 * Time: 22:26
 */

namespace app\manage\controller;

use think\Session;


class Excelstudents extends Common
{
	
	public function index()
	{
		if (request()->isPost()) {
			$pdata = input('post.');
			$file = request()->file('file');
			if ($file) {
				$info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
				if ($info) {
					$exts = $info->getExtension(); //获取扩展名
					if ($exts != "xlsx" && $exts != "xls") {
						$remsg['code'] = 105;
						$remsg['msg'] = "不是excel表格文件！";
						return $remsg;
					}
					
					$filename = 'public/uploads/' . $info->getSaveName(); //文件路径+文件名
					vendor("PHPExcel.PHPExcel.PHPExcel");
					vendor("PHPExcel.PHPExcel.IOFactory");
					//创建PHPExcel对象，注意，不能少了\
					$PHPExcel = new \PHPExcel();
					//如果excel文件后缀名为.xls，导入这个类
					if ($exts == 'xls') {
						$PHPReader = \PHPExcel_IOFactory::createReader('Excel5');
					} else if ($exts == 'xlsx') {
						$PHPReader = \PHPExcel_IOFactory::createReader('Excel2007');
					}
					//载入文件
					// $PHPReader = \PHPExcel_IOFactory::createReader('Excel5');
					$PHPExcel = $PHPReader->load($filename);
					//获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
					$currentSheet = $PHPExcel->getSheet(0);
					//获取总列数
					$allColumn = $currentSheet->getHighestColumn();
					//获取总行数
					$allRow = $currentSheet->getHighestRow();
					++$allColumn;
					//循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
					for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
						//从哪列开始，A表示第一列
						for ($currentColumn = 'A'; $currentColumn != $allColumn; $currentColumn++) {
							//数据坐标
							$address = $currentColumn . $currentRow;
							//读取到的数据，保存到数组$arr中
							$data[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getFormattedValue();
						}
					}
					
					$schstype = GetSchooltype($pdata['schid']);
					$dbname = "students" . $pdata['schid'];
					if ($schstype == 1) {
						
						$checkty = $this->Checkexcel($data, 'H');
						if ($checkty != 1) {
							$remsg['code'] = 108;
							$remsg['msg'] = $checkty;
							return $remsg;
						}
						
						$checktys = $this->Checkexcelall($data, 'H');
						if ($checktys != 1) {
							$remsg['code'] = 108;
							$remsg['msg'] = $checktys;
							return $remsg;
						}
						
						foreach ($data as $k => $v) {
							$datanew[$k]['name'] = trim($v['A']);
							$datanew[$k]['sex'] = $v['B'];
							$datanew[$k]['birthday'] = $v['C'];
							$datanew[$k]['nation'] = $v['D'];
							$datanew[$k]['address'] = $v['E'];
							$datanew[$k]['in_time'] = $v['F'];
							$datanew[$k]['id_type'] = $v['G'];
							$datanew[$k]['id_num'] = trim($v['H']);
							$datanew[$k]['single_is'] = $v['I'];
							$datanew[$k]['disabled_is'] = $v['J'];
							$datanew[$k]['house_owner'] = $v['K'];
							$datanew[$k]['house_relation'] = $v['L'];
							$datanew[$k]['house_address'] = $v['M'];
							$datanew[$k]['house_type'] = $v['N'];
							$datanew[$k]['buy_time'] = $v['O'];
							$datanew[$k]['name_two'] = $v['P'];
							$datanew[$k]['relation_two'] = $v['Q'];
							$datanew[$k]['job_two'] = $v['R'];
							$datanew[$k]['tel_two'] = $v['S'];
							$datanew[$k]['name_three'] = $v['T'];
							$datanew[$k]['relation_three'] = $v['U'];
							$datanew[$k]['job_three'] = $v['V'];
							$datanew[$k]['tel_three'] = $v['W'];
							$datanew[$k]['writer'] = $v['X'];
							$datanew[$k]['writer_relation'] = $v['Y'];
							if (empty($v['Z'])) {
								$datanew[$k]['sbt'] = 0;
							} else {
								$datanew[$k]['sbt'] = $v['Z'];
							}
						}
						db($dbname)->insertAll($datanew);
						$remsg['code'] = 100;
						$remsg['msg'] = "提交成功";
						return $remsg;
					}
					
					if ($schstype == 2) {
						if (Session::get('school_id') != 0) {
							
							$checkty = $this->Checkexcel($data, 'F');
							if ($checkty != 1) {
								$remsg['code'] = 108;
								$remsg['msg'] = $checkty;
								return $remsg;
							}
							
							$checktys = $this->Checkexcelall($data, 'F');
							if ($checktys != 1) {
								$remsg['code'] = 108;
								$remsg['msg'] = $checktys;
								return $remsg;
							}
							
							foreach ($data as $k => $v) {
								$datanew[$k]['name'] = trim($v['A']);
								$datanew[$k]['chinese'] = $v['B'];
								$datanew[$k]['smath'] = $v['C'];
								$datanew[$k]['english'] = $v['D'];
								$datanew[$k]['zcj'] = trim((int)$v['B'] + (int)$v['C'] + (int)$v['D']);
								$datanew[$k]['id_num'] = trim($v['F']);
							}
							db($dbname)->insertAll($datanew);
							$remsg['code'] = 100;
							$remsg['msg'] = "提交成功";
							return $remsg;
						} else {
							
							$checkty = $this->Checkexcel($data, 'H');
							if ($checkty != 1) {
								$remsg['code'] = 108;
								$remsg['msg'] = $checkty;
								return $remsg;
							}
							
							$checknums = $this->Checkimportnum($pdata['schid'], $data);
							if ($checknums == 1) {
								$remsg['code'] = 108;
								$remsg['msg'] = $checknums;
								return $remsg;
							}
							
							$checkresult = $this->Checksfz($pdata['schid'], $data);
							if (count($checkresult) != 0) {
								$remsg['code'] = 101;
								$remsg['msg'] = $checkresult;
								return $remsg;
							}
							
							foreach ($data as $k => $v) {
								$datanew[$k]['name'] = trim($v['A']);
								$datanew[$k]['chinese'] = $this->Getbind($pdata['schid'], trim($v['H']), 'chinese');
								$datanew[$k]['smath'] = $this->Getbind($pdata['schid'], trim($v['H']), 'smath');
								$datanew[$k]['english'] = $this->Getbind($pdata['schid'], trim($v['H']), 'english');
								$datanew[$k]['zcj'] = $this->Getbind($pdata['schid'], trim($v['H']), 'zcj');
								$datanew[$k]['sex'] = $v['B'];
								$datanew[$k]['birthday'] = $v['C'];
								$datanew[$k]['nation'] = $v['D'];
								$datanew[$k]['address'] = $v['E'];
								$datanew[$k]['in_time'] = $v['F'];
								$datanew[$k]['id_type'] = $v['G'];
								$datanew[$k]['id_num'] = trim($v['H']);
								$datanew[$k]['single_is'] = $v['I'];
								$datanew[$k]['disabled_is'] = $v['J'];
								$datanew[$k]['house_owner'] = $v['K'];
								$datanew[$k]['house_relation'] = $v['L'];
								$datanew[$k]['house_address'] = $v['M'];
								$datanew[$k]['house_type'] = $v['N'];
								$datanew[$k]['buy_time'] = $v['O'];
								$datanew[$k]['name_two'] = $v['P'];
								$datanew[$k]['relation_two'] = $v['Q'];
								$datanew[$k]['job_two'] = $v['R'];
								$datanew[$k]['tel_two'] = $v['S'];
								$datanew[$k]['name_three'] = $v['T'];
								$datanew[$k]['relation_three'] = $v['U'];
								$datanew[$k]['job_three'] = $v['V'];
								$datanew[$k]['tel_three'] = $v['W'];
								$datanew[$k]['writer'] = $v['X'];
								$datanew[$k]['writer_relation'] = $v['Y'];
							}
							db($dbname)->where('id', 'gt', 0)->delete();
							db($dbname)->insertAll($datanew);
							$remsg['code'] = 100;
							$remsg['msg'] = "提交成功";
							return $remsg;
						}
					}
					
					if ($schstype == 3) {
						
						$checkty = $this->Checkexcel($data, 'L');
						if ($checkty != 1) {
							$remsg['code'] = 108;
							$remsg['msg'] = $checkty;
							return $remsg;
						}
						
						$checktys = $this->Checkexcelall($data, 'L');
						if ($checktys != 1) {
							$remsg['code'] = 108;
							$remsg['msg'] = $checktys;
							return $remsg;
						}
						
						foreach ($data as $k => $v) {
							$datanew[$k]['name'] = trim($v['A']);
							$datanew[$k]['chinese'] = trim($v['B']);
							$datanew[$k]['smath'] = trim($v['C']);
							$datanew[$k]['english'] = trim($v['D']);
							$datanew[$k]['zcj'] = trim((int)$v['B'] + (int)$v['C'] + (int)$v['D']);
							$datanew[$k]['sex'] = $v['F'];
							$datanew[$k]['birthday'] = $v['G'];
							$datanew[$k]['nation'] = $v['H'];
							$datanew[$k]['address'] = $v['I'];
							$datanew[$k]['in_time'] = $v['J'];
							$datanew[$k]['id_type'] = $v['K'];
							$datanew[$k]['id_num'] = trim($v['L']);
							$datanew[$k]['single_is'] = $v['M'];
							$datanew[$k]['disabled_is'] = $v['N'];
							$datanew[$k]['house_owner'] = $v['O'];
							$datanew[$k]['house_relation'] = $v['P'];
							$datanew[$k]['house_address'] = $v['Q'];
							$datanew[$k]['house_type'] = $v['R'];
							$datanew[$k]['buy_time'] = $v['S'];
							$datanew[$k]['name_two'] = $v['T'];
							$datanew[$k]['relation_two'] = $v['U'];
							$datanew[$k]['job_two'] = $v['V'];
							$datanew[$k]['tel_two'] = $v['W'];
							$datanew[$k]['name_three'] = $v['X'];
							$datanew[$k]['relation_three'] = $v['Y'];
							$datanew[$k]['job_three'] = $v['Z'];
							$datanew[$k]['tel_three'] = $v['AA'];
							$datanew[$k]['writer'] = $v['AB'];
							$datanew[$k]['writer_relation'] = $v['AC'];
						}
						db($dbname)->insertAll($datanew);
						$remsg['code'] = 100;
						$remsg['msg'] = "提交成功";
						return $remsg;
					}
					
					
				} else {
					$remsg['code'] = 104;
					$remsg['msg'] = "上传出错，请重新上传！";
					return $remsg;
				}
			} else {
				$remsg['code'] = 103;
				$remsg['msg'] = "未提交表格数据！";
				return $remsg;
			}
			
		}
		
		
		return view();
	}
	
	/**
	 * (初中身份证绑定)通过身份证获取成绩
	 * @param $schid
	 * @param $sfz
	 * @param $value
	 * @return mixed
	 */
	public function Getbind($schid, $sfz, $value)
	{
		$res = db('students' . $schid)->where('id_num', $sfz)->value($value);
		return $res;
	}
	
	/**
	 * (初中验证)检测所导入的数据，与校级用户已经导入的学生表数据是否相等
	 * @param $schid
	 * @param $data
	 * @return array
	 */
	public function Checkimportnum($schid, $data)
	{
		$schoolnums = db('students' . $schid)->count();
		$importnums = count($data);
		if ($schoolnums != $importnums) {
			return 1;
		}
	}
	
	
	/**
	 * (初中验证)验证所导入表格中的每行身份证是否能找到对应
	 * @param $data
	 * @return int|mixed
	 */
	public function Checksfz($schid, $data)
	{
		$arr = array();
		foreach ($data as $k => $v) {
			$resid = db('students' . $schid)->where('id_num', trim($v['H']))->value('id');
			if (!$resid) {
				$arr[] = trim($v['H']);
			}
		}
		return $arr;
	}
	
	/**
	 * 检测所导入的数据是否存在身份证存在的情况
	 * @param $data
	 * @param $str
	 * @return array|int
	 */
	public function Checkexcel($data, $str)
	{
		$temparr = array();
		$box = array();
		foreach ($data as $key => $v) {
			if (in_array(trim($v[$str]), $temparr)) {
				array_push($box, '身份证号:' . trim($v[$str]) . '重复');
			} else {
				array_push($temparr, trim($v[$str]));
			}
		}
		
		if (count($box) > 0) {
			return $box;
		} else {
			return 1;
		}
	}
	
	
	/**
	 * 检测库中所有的学生表是否有重复的身份证
	 * @param $data
	 * @param $str
	 * @return array|int
	 */
	public function Checkexcelall($data, $str)
	{
		
		$tables = db()->query("SELECT table_name from information_schema.columns where table_name like '%students%' group by table_name");
		$temparr = array();
		for ($i = 0; $i < count($tables); $i++) {
			foreach ($data as $key => $v) {
				$dbname = str_replace("bk_", "", $tables[$i]['table_name']);
				$existvalue = db($dbname)->where('id_num', trim($v[$str]))->find();
				if ($existvalue) {
					$schooid = str_replace("bk_students", "", $tables[$i]['table_name']);
					array_push($temparr, '身份证号:' . trim($v[$str]) . '-在' . GetSchoolname($schooid) . '中已存在');
				}
			}
		}
		
		if (count($temparr) > 0) {
			return $temparr;
		} else {
			return 1;
		}
	}
	
}