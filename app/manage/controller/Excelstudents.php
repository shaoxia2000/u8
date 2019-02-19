<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/9 0009
 * Time: 22:26
 */

namespace app\manage\controller;

use Predis\Client as Redis;
use think\Config;
use think\Session;
use app\common\model\School as SchoolModel;

class Excelstudents extends Common
{
	
	public function index()
	{
		if (Session::get('area_id') == "") {
			$this->redirect('http://yg.dqedu.net');
		}
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
					$config = Config::get('redis');
					$redis = new Redis($config);
					$key = 'newsfz';
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
                            $newsfz = preg_replace('/\"/', '', trim($v['H']));
							$datanew[$k]['id_num'] = $newsfz;
							$redis->sadd($key, $newsfz . "==||==" . $pdata['schid']);
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
						db($dbname)->insertAll($datanew);
						$this->confirmschool($pdata['schid']);
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
							
							$checktyp = $this->Checksfz($pdata['schid'], $data);
							if ($checktyp != 1) {
								$remsg['code'] = 108;
								$remsg['msg'] = $checktyp;
								return $remsg;
							}
							
							
							foreach ($data as $k => $v) {
								db('students' . $pdata['schid'])->where('id_num', trim($v['F']))->update(['chinese' => sprintf('%.1f',trim($v['B'])), 'smath' => sprintf('%.1f',trim($v['C'])), 'english' => sprintf('%.1f',trim($v['D'])), 'zcj' => sprintf('%.1f',trim($v['B'] + $v['C'] + $v['D']))]);
							}
							$remsg['code'] = 100;
							$remsg['msg'] = "提交成功";
							return $remsg;
						} else {
							
							$checkty = $this->Checkexcel($data, 'J');
							if ($checkty != 1) {
								$remsg['code'] = 108;
								$remsg['msg'] = $checkty;
								return $remsg;
							}
							
							$checktys = $this->Checkexcelall($data, 'J');
							if ($checktys != 1) {
								$remsg['code'] = 108;
								$remsg['msg'] = $checktys;
								return $remsg;
							}
							
							
							foreach ($data as $k => $v) {
								$datanew[$k]['name'] = trim($v['A']);
								$datanew[$k]['sex'] = $v['B'];
								$datanew[$k]['graduate'] = $v['C'];
								$datanew[$k]['graduate_num'] = $v['D'];
								$datanew[$k]['student_code'] = $v['E'];
								$datanew[$k]['nation'] = $v['F'];
								$datanew[$k]['address'] = $v['G'];
								$datanew[$k]['in_time'] = $v['H'];
								$datanew[$k]['id_type'] = $v['I'];
								$datanew[$k]['id_num'] = trim($v['J']);
								$datanew[$k]['house_relation'] = $v['K'];
								$datanew[$k]['zj_type'] = $v['L'];
								$datanew[$k]['zj_address'] = $v['M'];
								$datanew[$k]['zj_time'] = $v['N'];
								$datanew[$k]['zj_relation'] = $v['O'];
								$datanew[$k]['x_name'] = $v['P'];
								$datanew[$k]['x_relation'] = $v['Q'];
								$datanew[$k]['x_tel'] = $v['R'];
								$datanew[$k]['writer'] = $v['S'];
								$datanew[$k]['writer_relation'] = $v['T'];
								$datanew[$k]['type'] = $v['U'];
							}
							db($dbname)->insertAll($datanew);
							$this->confirmschool($pdata['schid']);
							$remsg['code'] = 100;
							$remsg['msg'] = "提交成功";
							return $remsg;
						}
					}
					
					if ($schstype == 3) {
						$checkty = $this->Checkexcel($data, 'E');
						if ($checkty != 1) {
							$remsg['code'] = 108;
							$remsg['msg'] = $checkty;
							return $remsg;
						}
						
						$checktys = $this->Checkexcelall($data, 'E');
						if ($checktys != 1) {
							$remsg['code'] = 108;
							$remsg['msg'] = $checktys;
							return $remsg;
						}
						
						foreach ($data as $k => $v) {
							$datanew[$k]['high_school'] = trim($v['A']);
							$datanew[$k]['name'] = trim($v['B']);
							$datanew[$k]['sex'] = trim($v['C']);
							$datanew[$k]['junior_school'] = trim($v['D']);
                            $newsfz = preg_replace('/\"/', '', trim($v['E']));
							$datanew[$k]['id_num'] = $newsfz;
							$redis->sadd($key, $newsfz . "==||==" . $pdata['schid']);
							$datanew[$k]['art'] = $v['F'];
							$datanew[$k]['zcj'] = $v['G'];
							$datanew[$k]['sk_zcj'] = $v['H'];
							$datanew[$k]['luqu'] = $v['I'];
							$datanew[$k]['chinese'] = $v['J'];
							$datanew[$k]['smath'] = $v['K'];
							$datanew[$k]['english'] = $v['L'];
							$datanew[$k]['physics'] = $v['M'];
							$datanew[$k]['chemistry'] = $v['N'];
							$datanew[$k]['geography'] = $v['O'];
							$datanew[$k]['biologic'] = $v['P'];
							$datanew[$k]['history'] = $v['Q'];
							$datanew[$k]['politics'] = $v['R'];
							$datanew[$k]['sports'] = $v['S'];
							$datanew[$k]['graduate_num'] = $v['T'];
							$datanew[$k]['student_code'] = $v['U'];
							$datanew[$k]['nation'] = $v['V'];
							$datanew[$k]['parents'] = $v['W'];
							$datanew[$k]['relation'] = $v['X'];
							$datanew[$k]['tel'] = $v['Y'];
						}
						db($dbname)->insertAll($datanew);
						$this->confirmschool($pdata['schid']);
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
			$resid = db('students' . $schid)->where('id_num', trim($v['F']))->value('id');
			if (!$resid) {
				$arr[] = trim($v['F']);
			}
		}
		if (count($arr) > 0) {
			return $arr;
		} else {
			return 1;
		}
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
				array_push($box, '身份证号:' . trim($v[$str]) . $v['B'] . '重复');
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
		$config = Config::get('redis');
		$redis = new Redis($config);
		$key = 'newsfz';
		foreach ($data as $k => $v) {
			$datanew[] = $v[$str];
		}
		$temparr = array();
		foreach ($datanew as $value) {
			if (!$redis->smembers($key)) {
				return 1;
			} else {
				$dataredis = $redis->smembers($key);
				$redisdata = $this->doComments($dataredis);
				$new = preg_replace('/\"/', '', trim($value));
				if (array_key_exists($new, $redisdata)) {
					array_push($temparr, '身份证号:' . $new . '已经存在在' . GetSchoolname($redisdata[$new]));
				}
			}
		}
		if (count($temparr) > 0) {
			return $temparr;
		} else {
			return 1;
		}
	}
	
	
	public function doComments($data)
	{
		foreach ($data as $v) {
			list($sfz, $schid) = explode('==||==', $v);
			$newCom[$sfz] = $schid;
		}
		return $newCom;
	}
	
	
	public function confirmschool($schid)
	{
		$data = ['confirmnum' => 1];
		$where = ['schid' => $schid];
		$field = ['confirmnum'];
		SchoolModel::update($data, $where, $field);
	}
	
}