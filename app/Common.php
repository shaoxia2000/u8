<?php

use app\common\model\Area as AreaModel;
use app\common\model\School as SchoolModel;
use app\common\model\Samename as SamenameModel;
use app\common\model\Setgroup as SetgroupModel;
use app\common\model\Teacher as TeacherModel;
use app\common\model\Teachergroup as TeachergroupModel;
use app\common\model\Tfaccess as TfaccessModel;
use app\common\model\Tgaccess as TgaccessModel;
use Overtrue\Pinyin\Pinyin;
use think\Session;


error_reporting(E_ERROR | E_WARNING | E_PARSE);
/**
 * 自己写的ajax记录提交数据的函数
 */
function debugajax($data)
{
	file_put_contents('./log/debugajax.txt', var_export($data, true));
}

//日志存储
function logs()
{
	$cont = request()->controller(); //获取当前控制器
	$act = request()->action(); //获取当前方法
	$tablename = $cont . "/" . $act;
	$content = db('auth_rule')->field('title')->where('name', $tablename)->find();
	//判断是登陆还是退出还是操作
	if ($act == 'logout') {
		//退出
		$add['content'] = '用户退出系统';
	} else if ($act == 'index') {
		//登陆
		$add['content'] = '用户成功登陆系统';
	} else {
		//操作
		$add['content'] = $content['title'];
	}
	$request = Request::instance();
	$add['name'] = session('name');
	$add['ip'] = $request->ip();
	$add['time'] = time("Y-m-d H:i:s", time());
	$rt = db('Logs')->insert($add);
}

function excelExport($fileName = '', $headArr = [], $data = [])
{
//	$fileName .= "_" . date("Y_m_d", Request::instance()->time()) . ".xls";
	vendor("PHPExcel.PHPExcel.PHPExcel");
	vendor("PHPExcel.PHPExcel.IOFactory");
	$objPHPExcel = new \PHPExcel();
	$objPHPExcel->getProperties();
	$key = 0; // 设置表头
	foreach ($headArr as $v) {
		$colum = \PHPExcel_Cell::stringFromColumnIndex($key);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
		$key += 1;
	}
	$column = 2;
	$objActSheet = $objPHPExcel->getActiveSheet();
	foreach ($data as $key => $rows) { //行写入
		$span = 0;
		foreach ($rows as $keyName => $value) {// 列写入
			$j = \PHPExcel_Cell::stringFromColumnIndex($span);
			$objActSheet->setCellValue($j . $column, $value);
			$span++;
		}
		$column++;
	}
	$fileName = iconv("utf-8", "gb2312", $fileName); // 重命名表
	$objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表
	header('Content-Type: application/vnd.ms-excel');
	header("Content-Disposition: attachment;filename='$fileName'");
	header('Cache-Control: max-age=0');
	$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output'); // 文件通过浏览器下载
	exit();
}

function a($a)
{
	$da_num = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
	return $da_num[$a];
}

/**
 * API接口请求数据的方法
 * @param $url
 * @param null $data
 * @return mixed
 */
function https_request($url, $data = null)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	if (!empty($data)) {
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($curl);
	curl_close($curl);
	return $output;
}

/**
 * 数字转字母
 * @param $index
 * @param int $start
 * @return string
 */
function IntToChr($index, $start = 65)
{
	$str = '';
	if (floor($index / 26) > 0) {
		$str .= IntToChr(floor($index / 26) - 1);
	}
	return $str . chr($index % 26 + $start);
}

/**
 * 根据教师id获取教师名字，内组表里没有教师名字
 */
function Gettname($id)
{
	$tname = TeacherModel::getFieldById($id, 'name');
	return $tname;
}

/**
 * 根据学校ID返回该学校教师总数
 */
function CountTeacher($schid)
{
	$schid && $map['schid'] = ['eq', $schid];
	$tnum = TeacherModel::where($map)->count();
	return $tnum;
}

/**
 * 根据学校ID返回该学校学生总数
 */
function CountStudents($schid)
{
	$dbname = "students" . $schid;
	$tnum = db($dbname)->count();
	return $tnum;
}

/**
 * 根据学校ID返回学校名称
 */
function GetSchoolname($schid)
{
	$schoolname = SchoolModel::getFieldBySchid($schid, 'schname');
	return $schoolname;
}

/**
 * 根据学校ID返回学校类型
 */
function GetSchooltype($schid)
{
	$schooltype = SchoolModel::getFieldBySchid($schid, 'schtype');
	return $schooltype;
}

/**
 * 根据学校ID返回每个班级的平均人数
 */
function GetClassespnum($schid, $classnum)
{
	$znum = db('classes' . $schid)->count();
	$avgnum = $znum / $classnum;
	return $avgnum;
}

/**
 * 根据教师组id以及分层id返回分班数量
 */
function GetClassescnum($gid, $fid)
{
	$fnum = TfaccessModel::where(['gid' => $gid, 'fid' => $fid])->count();
	return $fnum;
}

/**
 * 根据教师组id以及分层id返回分层内分班的平均人数
 */
function GetCavg($gid, $fid, $classid)
{
	$fnum = TfaccessModel::where(['gid' => $gid, 'fid' => $fid])->count();
	$znum = db('classes' . $classid)->where('fcengid', $fid)->count();
	$pnum = $znum / $fnum;
	return $pnum;
}

/**
 * 根据学校schid,分层段值，分层数量,以及教师组id,返回每层[人数与分组数]
 */
function GetfengcInfo($schid, $fceng, $fcengnum, $tgroupid)
{
	if ($fcengnum == 0) {
		return '[' . CountStudents($schid) . '人]';
	} else {
		$fcnum = explode(',', $fceng);
		if ($fcnum[0] == 0 && $fcnum[1] != CountStudents($schid)) {
			$fnewnum1 = CountStudents($schid) - $fcnum[1];
			$onef = GettfaccessCount($tgroupid, 0);
			$twof = GettfaccessCount($tgroupid, 1);
			$str = "<table border=\"1\" width=\"100%\" style='border:1px solid #ccc'><tr><td>层数</td><td>一</td><td>二</td></tr><tr><td>人数</td><td>{$fcnum[1]}</td><td>{$fnewnum1}</td></tr><tr><td>分组数</td><td>{$onef}</td><td>{$twof}</td></tr></table>";
			return $str;
		}
		if ($fcnum[0] != 0 && $fcnum[1] == CountStudents($schid)) {
			$fnewnum1 = CountStudents($schid) - $fcnum[0];
			$onef = GettfaccessCount($tgroupid, 0);
			$twof = GettfaccessCount($tgroupid, 1);
			$str = "<table border=\"1\" width=\"100%\" style='border:1px solid #ccc'><tr><td>层数</td><td>一</td><td>二</td></tr><tr><td>人数</td><td>{$fcnum[0]}</td><td>{$fnewnum1}</td></tr><tr><td>分组数</td><td>{$onef}</td><td>{$twof}</td></tr></table>";
			return $str;
		}
		if ($fcnum[0] != 0 && $fcnum[1] != CountStudents($schid)) {
			$fnewnum1 = $fcnum[1] - $fcnum[0];
			$fnewnum2 = CountStudents($schid) - $fcnum[1];
			$onef = GettfaccessCount($tgroupid, 0);
			$twof = GettfaccessCount($tgroupid, 1);
			$threef = GettfaccessCount($tgroupid, 2);
			$str = "<table border=\"1\" width=\"100%\" style='border:1px solid #ccc'><tr><td>层数</td><td>一</td><td>二</td><td>三</td></tr><tr><td>人数</td><td>{$fcnum[0]}</td><td>{$fnewnum1}</td><td>{$fnewnum2}</td></tr><tr><td>分组数</td><td>{$onef}</td><td>{$twof}</td><td>{$threef}</td></tr></table>";
			return $str;
		}
	}
}


/**
 * 根据学校schid,分层ID返回人数
 */
function Getfcpnumnew($schid, $fcengid)
{
	$fcengnums = db('students' . $schid)->where('xstatus is null')->where('fcengid', $fcengid)->count();
	return $fcengnums;
}


/**
 * 根据教师组ID,层ID标识返回每层分组的数量
 */
function GettfaccessCount($tgroupid, $fcengid)
{
	$fcengnum = TfaccessModel::where(['gid' => $tgroupid, 'fid' => $fcengid])->count();
	return $fcengnum;
}

/**
 * 根据学校schid,返回该学校是否启用分组
 * @param $schid
 */
function GetcheckGroup($schid)
{
	$fzstatus = TeachergroupModel::where(['schid' => $schid, 'status' => 1])->find();
	if ($fzstatus) {
		return 1;
	} else {
		return 0;
	}
}

/**
 * 根据学校schid,返回该区是否启确认状态
 * @param $schid
 */
function GetcheckGroupa($schid)
{
	$fzstatus = TeachergroupModel::where(['schid' => $schid, 'astatus' => 1])->find();
	if ($fzstatus) {
		return 1;
	} else {
		return 0;
	}
}


/**
 * 根据学校id,返回管理员是否允许学校分班
 * @param $schid
 */
function Getcheckyesfb($schid)
{
	$fzstatus = TeachergroupModel::where(['schid' => $schid, 'xstatus' => 1])->find();
	if ($fzstatus) {
		return 1;
	} else {
		return 0;
	}
}


/**
 * @return int|string
 * 获取已经分组数量
 */
function GetGroupnums()
{
	$groups = TeachergroupModel::where('status', 1)->count();
	return $groups;
}

/**
 * ---------------------------------------组分层开关检查---------------------------------------------
 */
/**
 * 根据组id,组数量检查组中是否存在未选定班主任
 * @param $id
 * @param $tnum
 */
function Getcheckbzr($id, $tnum, $fcengnum)
{
	for ($i = 0; $i < $tnum; $i++) {
		$map['gid'] = ['eq', $id];
		$map['tgid'] = ['eq', $i];
		$map['isheader'] = ['eq', 1];
		$res = TgaccessModel::where($map)->select();
		if ($res->isEmpty()) {
			$str .= IntToChr($i) . ',';
		}
	}
	
	
	if ($fcengnum != 0) {
		$fnums = TfaccessModel::where('gid', $id)->count();
		if ($fnums != $tnum) {
			$str1 = '教师组没有完全分配给分层！';
		}
		
		for ($k = 0; $k < $fcengnum; $k++) {
			$maps['gid'] = ['eq', $id];
			$maps['fid'] = ['eq', $k];
			$res = TfaccessModel::where($maps)->select();
			if ($res->isEmpty()) {
				$str2 .= $k + 1 . '层未设定教师组,';
			}
//			if (count($res) < 2) {
//				$str2 .= $k + 1 . '层教师组数量少于2个!,';
//			}
		}
	}
	
	if ($str) {
		$str = rtrim($str, ',');
		return '该教师组内第' . $str . '组未设定班主任!';
	}
	
	
	if ($str1) {
		return $str1;
	}
	
	if ($str2) {
		$str2 = rtrim($str2, ',');
		return '该教师组内第' . $str2;
	} else {
		return 1;
	}
	
	
}

/**
 * 获取区名字
 * @param $areaid
 * @return mixed
 */
function Getareaname($areaid)
{
	$areaname = AreaModel::getFieldByArea_id($areaid, 'area_name');
	return $areaname;
}


/**
 * 通过schid获取区用户与学校用户是否确认状态status
 * @param $schid
 * @return mixed
 */
function Getcheckstatus($schid)
{
	
	$astatus = TeachergroupModel::where(['status' => 1, 'astatus' => 1])->getFieldBySchid($schid, 'id');
	if (!$astatus) {
		return 0;
	} else {
		return 1;
	}
}

/**
 * 根据学校id返回已经开启开关的教师组id
 * @param $schid
 * @return mixed
 */
function Getteachergroupid($schid)
{
	$res = TeachergroupModel::where(['status' => 1, 'schid' => $schid])->find();
	return $res['id'];
}


/**
 * 根据学校schid返回该校表是否有数据
 * @param $schid
 * @return int
 */
function Getstudentsnum($schid)
{
	$data = db('students' . $schid)->select();
	if ($data->isEmpty()) {
		return 0;
	} else {
		return 1;
	}
}

/**
 * 根据分组号与学生表名返回相关人数信息(小学用)
 * @param $cnonum
 * @param $classdbname
 * @return string
 */
function viewresultsmall($cnonum, $classdbname)
{
	$schtype = GetSchooltype($classdbname);
	if ($schtype == 1) {
		$singlemale = db('classes' . $classdbname)->where('cno', $cnonum)->where('sex', '男')->count();
		$singlefemale = db('classes' . $classdbname)->where('cno', $cnonum)->where('sex', '女')->count();
		$znum = $singlemale + $singlefemale;
		return "总人数：" . $znum . "　　男生数：" . $singlemale . "　　女生数：" . $singlefemale . "<br>";
	}
	
	if ($schtype == 2) {
		$dataavg = db('classes' . $classdbname)->where('cno', $cnonum)->avg('zcj');
		$dataavg = number_format($dataavg, 2, '.', '');
		$singlemale = db('classes' . $classdbname)->where('cno', $cnonum)->where('sex', '男')->count();
		$singlefemale = db('classes' . $classdbname)->where('cno', $cnonum)->where('sex', '女')->count();
		$znum = $singlemale + $singlefemale;
		return "总人数：" . $znum . "　　男生数：" . $singlemale . "　　女生数：" . $singlefemale . "　　平均分：" . $dataavg . "<br>";
	}
	
	if ($schtype == 3) {
		$res = TeachergroupModel::where(['schid' => $classdbname, 'status' => 1, 'astatus' => 1])->find();
		$dataavg = db('classes' . $classdbname)->where('cno', $cnonum)->avg('zcj');
		$dataavg = number_format($dataavg, 2, '.', '');
		$singlemale = db('classes' . $classdbname)->where('cno', $cnonum)->where('sex', '男')->count();
		$singlefemale = db('classes' . $classdbname)->where('cno', $cnonum)->where('sex', '女')->count();
		$znum = $singlemale + $singlefemale;
		return "总人数：" . $znum . "　　男生数：" . $singlemale . "　　女生数：" . $singlefemale . "　　平均分：" . $dataavg . "<br>";
	}
	
}


/**
 * -------------------以下是分层三组展示方式-----------------------
 */

function Getclassesmale($classcno, $fcengid, $schid)
{
	$data = db('classes' . $schid)->field('name')->where(['cno' => $classcno, 'fceng' => $fcengid, 'sex' => '男'])->select();
	foreach ($data as $k => $v) {
		echo $v['name'] . "   ";
	}
}

function Getclassesfemale($classcno, $fcengid, $schid)
{
	$data = db('classes' . $schid)->field('name')->where(['cno' => $classcno, 'fceng' => $fcengid, 'sex' => '女'])->select();
	foreach ($data as $k => $v) {
		echo $v['name'] . "   ";
	}
}

function Getclassesname($classcno, $fcengid, $schid)
{
	$data = db('classes' . $schid)->field('name')->where(['cno' => $classcno, 'fcengid' => $fcengid])->select();
	foreach ($data as $k => $v) {
		if (Session::has('admin')) {
			$viewname = $v['name'];
		} else {
			$viewname = substr_cut($v['name']);
		}
		echo "<td class='layui-btn layui-btn-primary' style='margin: 4px;width: 100px; text-align: center;'>" . $viewname . "</td>" . "   ";
	}
	
}

/**
 * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
 * @param string $user_name 姓名
 * @return string 格式化后的姓名
 */
function substr_cut($user_name)
{
	$strlen = mb_strlen($user_name, 'utf-8');
	$firstStr = mb_substr($user_name, 0, 1, 'utf-8');
	$lastStr = mb_substr($user_name, -1, 0, 'utf-8');
	return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
}


function Getclassesnamenofc($classcno, $schid)
{
	$data = db('classes' . $schid)->field('name')->where(['cno' => $classcno])->select();
	foreach ($data as $k => $v) {
		if (Session::has('admin')) {
			$viewname = $v['name'];
		} else {
			$viewname = substr_cut($v['name']);
		}
		echo "<td class='layui-btn layui-btn-primary' style='margin: 4px;width: 100px; text-align: center;'>" . $viewname . "</td>" . "   ";
	}
}

function Getcsexnum($classcno, $fcengid, $schid)
{
	$singlemale = db('classes' . $schid)->where('cno', $classcno)->where(['sex' => '男', 'fcengid' => $fcengid])->count();
	$singlefemale = db('classes' . $schid)->where('cno', $classcno)->where(['sex' => '女', 'fcengid' => $fcengid])->count();
	$dataavg = db('classes' . $schid)->where(['cno' => $classcno, 'fcengid' => $fcengid])->avg('zcj');
	$dataavg = number_format($dataavg, 2, '.', '');
	$znum = $singlemale + $singlefemale;
	return "总人数：" . $znum . "男生数：  " . $singlemale . "   女生数：  " . $singlefemale . "   平均分：  " . $dataavg;
}

function Getclassesavg($classnum, $fcengid, $schid)
{
	for ($ii = 1; $ii <= $classnum; $ii++) {
		$dataavg = db('classes' . $schid)->where('cno', $ii)->where('fcengid', $fcengid)->avg('zcj');
		$ar[] = round($dataavg, 2);
	}
	$max = array_search(max($ar), $ar);
	$min = array_search(min($ar), $ar);
	$rem = $ar[$max] - $ar[$min];
	return number_format($rem, 2, '.', '');
}


/**
 * 不分层平均分最大差值
 * @param $classnum
 * @param $fcengid
 * @param $schid
 * @return string
 */
function Getclassesavgz($classnum, $schid)
{
	for ($ii = 1; $ii <= $classnum; $ii++) {
		$dataavg = db('classes' . $schid)->where('cno', $ii)->avg('zcj');
		$ar[] = round($dataavg, 2);
	}
	$max = array_search(max($ar), $ar);
	$min = array_search(min($ar), $ar);
	$rem = $ar[$max] - $ar[$min];
	return number_format($rem, 2, '.', '');
}

/**
 * -------------------整体班级返回值------------------------------
 */

function Getclassesnums($schid)
{
	$nums = db('classes' . $schid)->count();
	return $nums;
}

function Getclassesnumsfc($schid, $cno, $fceng)
{
	$nums = db('classes' . $schid)->where(['cno' => $cno, 'fcengid' => $fceng])->count();
	return $nums;
}


function Getclassesmalenums($schid)
{
	$malenums = db('classes' . $schid)->where('sex', '男')->count();
	return $malenums;
}

function Getclassesmalenumsfc($schid, $cno, $fceng)
{
	$malenums = db('classes' . $schid)->where(['sex' => '男', 'cno' => $cno, 'fcengid' => $fceng])->order('id desc')->count();
	return $malenums;
}

function Getclassesfemalenums($schid)
{
	$femalenums = db('classes' . $schid)->where('sex', '女')->count();
	return $femalenums;
}

function Getclassesfemalenumsfc($schid, $cno, $fceng)
{
	$femalenums = db('classes' . $schid)->where(['sex' => '女', 'cno' => $cno, 'fcengid' => $fceng])->order('zcj desc')->count();
	return $femalenums;
}

/**
 * -------------------大屏用整体班级返回值------------------------------
 */

function Getclassesnumsb($schid, $cno)
{
	$nums = db('classes' . $schid)->where('cno', $cno)->count();
	return $nums;
}

function Getclassesmalenumsb($schid, $cno)
{
	$malenums = db('classes' . $schid)->where(['sex' => '男', 'cno' => $cno])->count();
	return $malenums;
}

function Getclassesfemalenumsb($schid, $cno)
{
	$femalenums = db('classes' . $schid)->where(['sex' => '女', 'cno' => $cno])->count();
	return $femalenums;
}

function Getclassesavgb($schid, $cno)
{
	$dataavg = db('classes' . $schid)->where('cno', $cno)->avg('zcj');
	return number_format($dataavg, 2, '.', '');
}

function Getclassesavgbfc($schid, $cno, $fceng)
{
	$dataavg = db('classes' . $schid)->where(['cno' => $cno, 'fcengid' => $fceng])->avg('zcj');
	return number_format($dataavg, 2, '.', '');
}

/**
 * @校级用户大屏分组-根据教师组id,与组内id获取班主任名称
 * @param $gid
 * @param $tgid
 */
function Getbigtname($gid, $tgid)
{
	$map = ['gid' => $gid, 'tgid' => $tgid, 'isheader' => 1];
	$res = TgaccessModel::where($map)->find();
	$tname = TeacherModel::where('id', $res['tid'])->find();
	return $tname['name'];
}

function Getclassesavgfc($tgroupid, $schid, $fcengid)
{
	$classnum = TfaccessModel::where(['gid' => $tgroupid, 'fid' => $fcengid])->count();
	for ($ii = 1; $ii <= $classnum; $ii++) {
		$dataavg = db('classes' . $schid)->where('cno', $ii)->where('fcengid', $fcengid)->avg('zcj');
		$ar[] = round($dataavg, 2);
	}
	$max = array_search(max($ar), $ar);
	$min = array_search(min($ar), $ar);
	$rem = $ar[$max] - $ar[$min];
	return number_format($rem, 2, '.', '');
}

//------------------双胞胎处理-----------------------------


/**
 * 小学双胞胎同一班
 * @param $schid
 * @param $sbt
 * @param $cno
 */
function checksbtyes($schid, $sbt, $cno)
{
	$sbtarr = sbtarr($schid);
	$sbtdata = db('classes' . $schid)->where('id', $sbt)->find();
	$nxdata = db('classes' . $schid)->where('sbt', $sbtdata['id'])->select();
	foreach ($nxdata as $key => $v) {
		if ($v['cno'] != $sbtdata['cno']) {
			db('classes' . $schid)->where('id', $v['id'])->update(['cno' => $sbtdata['cno']]);
//			$mapnew['cno'] = ['eq', $sbtdata['cno']];
//			$mapnew['sex'] = ['eq', $v['sex']];
//			$mapnew['id'] = ['not in', $sbtarr];
//			db('classes' . $schid)->where($mapnew)->limit(1)->order('cid', 'desc')->update(['cno' => $v['cno']]);
		}
	}
}


/**
 * 小学双胞胎不在同一班
 * @param $schid
 * @param $sbt
 * @param $cno
 */
function checksbtno($schid, $sbt, $cno)
{
	$sbtdata = db('classes' . $schid)->where('id', $sbt)->find();
	$nxdata = db('classes' . $schid)->where('sbt', $sbtdata['id'])->select();
	foreach ($nxdata as $key => $v) {
		if ($v['cno'] == $sbtdata['cno']) {
			$mapnew['cno'] = ['neq', $sbtdata['cno']];
			$mapnew['sex'] = ['eq', $v['sex']];
			$mapnew['id'] = ['neq', $sbtdata['id']];
			$linshi = db('classes' . $schid)->where($mapnew)->where('sbt is NULL')->limit(1)->order('cid', 'desc')->find();
			db('classes' . $schid)->where('id', $v['id'])->update(['cno' => $linshi['cno']]);
			db('classes' . $schid)->where('id', $linshi['id'])->update(['cno' => $vo['cno']]);
		}
	}
}

//--------------------双胞胎处理结束----------------------------

/**
 * @通过学校schid获取科目开关是否打开
 * @param $schid
 * @return mixed
 */
function Getknowledgeset($schid)
{
	$knowledge = SetgroupModel::getFieldBySchid($schid, 'knowledge');
	if ($knowledge) {
		return $knowledge;
	} else {
		return 'off';
	}
}

function Getnofbset($schid)
{
	$nofb = SetgroupModel::getFieldBySchid($schid, 'nofb');
	if ($nofb) {
		return $nofb;
	} else {
		return 'off';
	}
}


//---------------教师组学生族绑定结果展示----------------------------------

function getbandmale($schid, $tgroupid)
{
	$malenum = db('classes' . $schid)->where(['sex' => '男', 'tgroupid' => $tgroupid])->count();
	return $malenum;
}

function getbandfemale($schid, $tgroupid)
{
	$femalenum = db('classes' . $schid)->where(['sex' => '女', 'tgroupid' => $tgroupid])->count();
	return $femalenum;
}

/**
 * @获取科任教师名单
 * @param $gid
 * @param $tgid
 * @return string
 */
function Getsmalltname($gid, $tgid)
{
	$map = ['gid' => $gid, 'tgid' => $tgid];
	$res = TgaccessModel::where($map)->where('isheader', 0)->select();
	if (count($res) > 0) {
		foreach ($res as $k => $v) {
			$tname = TeacherModel::getFieldById($v['tid'], 'name');
			echo "<li><span>{$tname}</span></li>";
		}
	} else {
		return "该教师组不存在科任教师";
	}
	
	
}

function Gettstudents($schid, $tgroupid)
{
	$sname = db('classes' . $schid)->field('name')->where('tgroupid', $tgroupid)->select();
	foreach ($sname as $k => $v) {
		echo "<li><span>{$v['name']}</span></li>";
	}
}

function seunserialize($data)
{
	$datanew = unserialize($data);
	return $datanew;
}

function Getfcnum($schid)
{
	$map['status'] = 1;
	$map['astatus'] = 1;
	$map['schid'] = $schid;
	$res = TeachergroupModel::where($map)->find();
	return $res['fcengnum'];
}

/**
 * @根据所在的层返回所在层的总班级数量
 * @param $gid
 * @param $fid
 */
function Getfcmincno($gid, $fid)
{
	$mincno = TfaccessModel::where(['gid' => $gid, 'fid' => $fid])->count();
	return $mincno;
}


/**
 * 获取之前层的分班数量
 * @param $fid
 * @return mixed
 */
function Getfcmaxcno($gid, $fid)
{
	$map['gid'] = ['eq', $gid];
	$map['fid'] = ['lt', $fid];
	$maxcno = TfaccessModel::where($map)->count();
	if ($maxcno > 0) {
		return $maxcno;
	} else {
		return 0;
	}
}

/**
 * @根据学校id，学生组号获取真实班级号
 * @param $schid
 * @param $cno
 * @return mixed
 */
function Getsecondtgid($cno)
{
	$classnumber = db('classes' . Session::get('school_id'))->where('cno', $cno)->value('tgroupid');
	return $classnumber + 1;
}

/**
 * @根据学校id获取gstatus状态(大屏是否公示)
 * @param $schid
 */
function Getgstatus($schid)
{
	$map['status'] = ['eq', 1];
	$map['astatus'] = ['eq', 1];
	$map['schid'] = ['eq', $schid];
	$gstatus = TeachergroupModel::where($map)->value('gstatus');
	if ($gstatus) {
		return $gstatus;
	} else {
		return 0;
	}
}

/**
 * @一键分组头部获取分组总数
 * @return int|string
 */
function Getagcount()
{
	$map['status'] = ['eq', 1];
	$map['astatus'] = ['eq', 1];
	$map['areaid'] = ['eq', Session::get('area_id')];
	$count = TeachergroupModel::where($map)->count();
	return $count;
}

/**
 * @一键分组头部获取已公示分组总数
 * @return int|string
 */
function Getagycount()
{
	$map['status'] = ['eq', 1];
	$map['astatus'] = ['eq', 1];
	$map['gstatus'] = ['eq', 1];
	$map['areaid'] = ['eq', Session::get('area_id')];
	$count = TeachergroupModel::where($map)->count();
	return $count;
}

/**
 * @一键分组头部获取未公示分组总数
 * @return int|string
 */
function Getagwcount()
{
	$map['status'] = ['eq', 1];
	$map['astatus'] = ['eq', 1];
	$map['gstatus'] = ['eq', 0];
	$map['areaid'] = ['eq', Session::get('area_id')];
	$count = TeachergroupModel::where($map)->count();
	return $count;
}

/**
 * @检查学校管理员是否已经分过班级
 */
function Checkfblock()
{
	$map['cstatus'] = ['eq', 1];
	$map['schid'] = ['eq', Session::get('school_id')];
	$res = TeachergroupModel::where($map)->find();
	if ($res['id']) {
		return 1;
	} else {
		return 0;
	}
}

function Getfivezcj($schid, $id)
{
	$res = db('students' . $schid)->where('id', $id)->find();
	$fivezcj = $res['chinese'] + $res['smath'] + $res['english'] + $res['physics'] + $res['chemistry'];
	return $fivezcj;
}


/**
 * @区用户是否显示分班结果导出
 * @return int
 */
function Checkfblockdeal($id)
{
	$map['cstatus'] = ['eq', 1];
	$map['id'] = ['eq', $id];
	$res = TeachergroupModel::where($map)->value('cstatus');
	return $res;
}


/**
 * @获取班级双胞胎结果id,id,id...
 * @return string
 */
function sbtarr($classid)
{
	$data = db('students' . $classid)->field('id,sbt')->where('sbt', 'gt', 0)->select();
	if ($data->isEmpty()) {
		return 0;
	}
	foreach ($data as $k => $v) {
		$arr[] = $v['id'] . ',' . $v['sbt'];
	}
	$str = "";
	foreach ($arr as $vv) {
		$str = $str . $vv . ',';
	}
	$strend = trim($str, ',');
	return $strend;
}


function samenarr($classid)
{
	$sbtarr = sbtarr($classid);
	$pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
	$data = db('students' . $classid)->field('id,name')->where('id', 'not in', $sbtarr)->select();
	foreach ($data as $k => $v) {
		$name = $pinyin->permalink($v['name']);
		$newCom[$v['id']] = $name;
	}
	foreach ($newCom as $k => $v) {
		$arrn[$v][] = $k;
	}
	
	shuffle($arrn);
	
	foreach ($arrn as $k => $v) {
		if (count($v) > 1) {
			$str = "";
			foreach ($v as $vva) {
				$str = $str . $vva . ',';
			}
			$strend = $strend . trim($str, ',') . ',';
		}
	}
	
	$strend = trim($strend, ',');
	
	return $strend;
	
}


/**
 * @重名处理函数
 * @param $classid 班级id
 * @param $classnum 分班数量
 */
function splitname($classid, $classnum)
{
	$sbtarr = sbtarr($classid);
	$sbtarray = explode(",", $sbtarr);
	
	$data = SamenameModel::where('schid', $classid)->find();
	$strend = $data['sameid'];
	$strendarray = explode(",", $strend?:",");
	
	$zarray = array_merge($strendarray, $sbtarray);
	$map = array();
	if(!empty($zarray)){
		$map['id'] = ['not in', $zarray];
	}
	
	$i = 1;
	$j = $classnum;
	
	foreach ($strendarray as $vv) {
		$res = db('classes' . $classid)->field('sex, cno')->where('id', $vv)->find();
		if ($res['cno'] != $i) {
			db('classes' . $classid)->where('id', $vv)->update(['cno' => $i]);
			db('classes' . $classid)->where(['sex' => $res['sex'], 'cno' => $i])->where($map)->limit(1)->order('id', 'desc')->update(['cno' => $res['cno']]);
		}
		$i++;
		if ($i > $j) {
			$i = 1;
		}
	}
	
	
}

/**
 * @指定范围内指定数量随机数
 * @param int $begin
 * @param int $end
 * @param int $limit
 * @return array
 */
function number_rand($begin = 1, $end = 8, $limit = 8)
{
	$rand_array = range($begin, $end);
	shuffle($rand_array);
	return array_slice($rand_array, 0, $limit);
}


function Getexportstatus($schid)
{
	$gstatus = TeachergroupModel::where(['schid' => $schid, 'status' => 1, 'astatus' => 1])->value('gstatus');
	return $gstatus;
}




function randrand($min = 0, $max = 1)
{
	$values = $min + mt_rand() / mt_getrandmax() * ($max - $min);
	return round($values, 2);
}

/**
 * 新的双胞胎绑定方法
 * @param $id
 * @param $cno
 */
function sortdo($id, $cno, $schid)
{
	$sbtarr = sbtarr($schid);
	if ($sbtarr != 0) {
		$sbtarray = explode(",", $sbtarr);
		$res = db('classes' . $schid)->where('sbt', $id)->select();
		foreach ($res as $index => $re) {
			if ($re['cno'] != $cno) {
				$linshiold = db('classes' . $schid)->where('id', $re['id'])->find();
				$linshinew = db('classes' . $schid)->where('id', 'not in', $sbtarray)->where('cno', $cno)->where('sex', $linshiold['sex'])->orderRaw('rand()')->limit(1)->update(['cno' => $linshiold['cno']]);
				db('classes' . $schid)->where('id', $re['id'])->update(['cno' => $cno]);
			}
		}
	}
	
}


