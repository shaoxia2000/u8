<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/6/5
 * Time: 16:17
 */

namespace app\manage\controller;

use app\common\model\Teachergroup as TeachergroupModel;
use Predis\Client as Redis;
use think\Config;
use think\Request;
use think\Session;
use think\Db;

class Test extends Common
{
	public function index()
	{
        halt(IntToChr(2));
	}
	
	
	public function setapwd()
	{
		return view();
	}
	
	public function setpwd()
	{
		
		if (request()->isPost()) {
			$data = input('post.');
			db('area')->where('id', $data['id'])->update(['pushpwd' => md5($data['pwd'])]);
			$remsg['code'] = 100;
			$remsg['msg'] = "推送密码设置成功!";
			return $remsg;
		}
		
		return view();
	}
	
	public function pagedata()
	{
		//获取总条数
		$list = db('area')->select();
		$count = count($list);
		//获取每页显示的条数
		$limit = Request::instance()->param('limit');
		//获取当前页数
		$page = Request::instance()->param('page');
		//计算出从那条开始查询
		$begin = ($page - 1) * $limit;
		// 查询出当前页数显示的数据
//		$map['id'] = ['egt', $begin];
		$list = db('area')->limit($begin, $limit)->order('id asc')->select();
		foreach ($list as $k => $v) {
			$arr[] = $v;
		}
		//返回数据
		return ["code" => "0", "msg" => "", "count" => $count, "data" => $arr];
	}
	
	
	public function tjs($schid, $i, $tnum, $id)
	{
		$studentscount = db('students' . $schid)->where('xstatus is null')->count();
		$fcengnum = TeachergroupModel::getFieldById($id, 'fcengnum');
		$avgnumz = $studentscount / $tnum;
		$avgnum = sprintf("%.1f", $avgnumz);
		if ($i == $fcengnum - 1) {
			db('students' . $schid)->where('xstatus is null')->where('fcengid is null')->order('zcj desc')->update(['fcengid' => $i]);
		} else {
			$limitvalue = GetClassescnum($id, $i) * $avgnum;
			dump($limitvalue . '-');
			if (floor($limitvalue) == $limitvalue) {
				
			} else {
				$limitvalue = intval(floor($limitvalue)) + 1;
			}
			dump($limitvalue);
			
			db('students' . $schid)->where('xstatus is null')->where('fcengid is null')->order('zcj desc')->limit($limitvalue)->update(['fcengid' => $i]);
		}
		$minzcj = db('students' . $schid)->where('fcengid is not null')->limit(1)->order('zcj asc')->value('zcj');
		
		db('students' . $schid)->where('fcengid is null')->where('zcj', $minzcj)->order('zcj desc')->update(['fcengid' => $i]);
		$countz = db('students' . $schid)->where('fcengid is not null')->count();
		dump($countz);
		
	}
	
	
	public function ts($schid, $num, $fcengid)
	{
		$data = db('students' . $schid)->order('zcj desc')->limit($num)->select();
		foreach ($data as $k => $v) {
			db('students' . $schid)->where('id', $v['id'])->update(['fcengid' => $fcengid]);
		}
		dump($data);
	}
	
	public function tsf($schid, $numstart, $numend, $fcengid)
	{
		$data = db('students' . $schid)->order('zcj desc')->limit($numstart, $numend)->select();
		foreach ($data as $k => $v) {
			db('students' . $schid)->where('id', $v['id'])->update(['fcengid' => $fcengid]);
		}
		dump($data);
	}
	
	
	public function tb()
	{
		$config = Config::get('redis');
		$redis = new Redis($config);
		$key = 'newsfz';
		$data = $redis->smembers($key);
		foreach ($data as $k => $v) {
			$datanew[] = ['sfz' => $v];
		}
		$sfzm = model('Sfz');
		$sfzm->saveAll($datanew);
	}
	
	
	public function testfc($classnum, $classdbname, $fcengid)
	{
		for ($ii = 1; $ii <= $classnum; $ii++) {
			$dataavg = db($classdbname)->where('cno', $ii)->where('fcengid', $fcengid)->avg('zcj');
			$ar[] = round($dataavg, 2);
		}
		
		$max = array_search(max($ar), $ar);
		$min = array_search(min($ar), $ar);
		$rem = $ar[$max] - $ar[$min];
		dump($ar);
		echo '----总分平均差值:----';
		echo round($rem, 2);
		
	}
	
	
	public function test($classnum, $classdbname)
	{
		for ($ii = 1; $ii <= $classnum; $ii++) {
			$dataavg = db($classdbname)->where('cno', $ii)->avg('zcj');
			$ar[] = round($dataavg, 2);
		}
		
		$max = array_search(max($ar), $ar);
		$min = array_search(min($ar), $ar);
		$rem = $ar[$max] - $ar[$min];
		dump($ar);
		echo '----总分平均差值:----';
		echo round($rem, 2);
		
	}
	
	public function viewresult($classnum, $classdbname)
	{
		for ($ii = 1; $ii <= $classnum; $ii++) {
			$singlemale = db($classdbname)->where('cno', $ii)->where('sex', '男')->count();
			$singlefemale = db($classdbname)->where('cno', $ii)->where('sex', '女')->count();
			$znum = $singlemale + $singlefemale;
			echo $ii . "班<br>";
			echo "总人数：" . $znum . "<br>";
			echo "男生数：" . $singlemale . "<br>";
			echo "女生数：" . $singlefemale . "<br><br><br>";
		}
		
	}
	
	public function viewresultfc($classnum, $classdbname, $fcengid)
	{
		for ($ii = 1; $ii <= $classnum; $ii++) {
			$singlemale = db($classdbname)->where('cno', $ii)->where(['sex' => '男', 'fcengid' => $fcengid])->count();
			$singlefemale = db($classdbname)->where('cno', $ii)->where(['sex' => '女', 'fcengid' => $fcengid])->count();
			$znum = $singlemale + $singlefemale;
			echo $ii . "班<br>";
			echo "总人数：" . $znum . "<br>";
			echo "男生数：" . $singlemale . "<br>";
			echo "女生数：" . $singlefemale . "<br><br><br>";
		}
		
	}
	
	
	public function randclass($dbname, $classnum, $schtype)
	{
		for ($ii = 1; $ii <= $classnum; $ii++) {
			$datacount = db($dbname)->where('cno', $ii)->count();
			$ar[] = $datacount;
		}
		
		$max = array_search(max($ar), $ar);
		$min = array_search(min($ar), $ar);
		$rem = $ar[$max] - $ar[$min];
		$newmax = $max + 1;
		$newmin = $min + 1;
		if ($rem > 1) {
			if ($schtype == 1) {
				db($dbname)->where('cno', $newmax)->where('sbt', 0)->limit(1)->order('id', 'desc')->update(['cno' => $newmin]);
			} else {
				db($dbname)->where('cno', $newmax)->limit(1)->order('id', 'desc')->update(['cno' => $newmin]);
			}
		}
		dump($rem);
		
	}
	
	
	public function testredis()
	{
		$config = Config::get('redis');
		$redis = new Redis($config);
		$key = "sfz";
		$data = $redis->smembers($key);
		dump($data);
	}
	
	public function testp()
	{
		$config = Config::get('redis');
		$redis = new Redis($config);
		$key = 'areaid_' . Session::get('area_id');
		$value = "230106198305150820";
		$redis->sadd($key, $value);
	}
	
	
	public function testr()
	{
		$config = Config::get('redis');
		$redis = new Redis($config);
		$key = 'areaid_' . Session::get('area_id');
		if (!$redis->smembers($key)) {
			$value = "230624198401240017";
			$redis->sadd($key, $value);
			return "不存在任何身份证！";
		} else {
			$data = $redis->smembers($key);
			$sfz = "230106198305150820";
			if (in_array($sfz, $data)) {
				return "身份证存在!";
			} else {
				$data = $redis->smembers($key);
				return "身份证通过!";
			}
		}
	}
	
	
	public function testgroup()
	{
		$res = TeachergroupModel::where('id', 110)->find();
		dump(unserialize($res['knowledge']));
	}
	
	/**
	 * 将一个数组切成n份
	 * @param $number 切的数值
	 * @param $avgNumber 份数
	 * @return array
	 */
	public function numberAvg($number, $avgNumber)
	{
		if ($number == 0) {
			$array = array_fill(0, $avgNumber, 0);
		} else {
			$avg = floor($number / $avgNumber);
			$ceilSum = $avg * $avgNumber;
			$array = array();
			for ($i = 0; $i < $avgNumber; $i++) {
				if ($i < $number - $ceilSum) {
					array_push($array, $avg + 1);
				} else {
					array_push($array, $avg);
				}
			}
		}
		return $array;
	}
	
	/**
	 * @param $arrF
	 * @param $user_count 分组数量
	 * @param $group_num 每组多少个
	 * @return array
	 */
	public function array_group($arrF, $user_count, $group_num)
	{
		for ($i = 0; $i < $user_count; $i++) {
			if ($i == $user_count - 1) {
				$arrT[] = array_slice($arrF, $i * $group_num);
			} else {
				$arrT[] = array_slice($arrF, $i * $group_num, $group_num);
			}
		}
		return $arrT;
	}
	
	
	public function onetest()
	{
		for ($ii = 1; $ii <= 8; $ii++) {
			$ar[] = $ii;
		}
		$rand_keys = array_rand($ar, 2);
		print $ar[$rand_keys[0]] . "\n";
		print $ar[$rand_keys[1]] . "\n";
		$dbname = "bk_classes337";
		//小学专有随机交换处理
		$randdata = db()->query("SELECT * FROM {$dbname} where cno={$ar[$rand_keys[0]]} and sex='男' ORDER BY  RAND() LIMIT 5");
		$arrcid = "";
		foreach ($randdata as $k => $v) {
			db('classes337')->where('cid', $v['cid'])->update(['cno' => $ar[$rand_keys[1]]]);
			$arrcid = $arrcid . $v['cid'] . ',';
		}
		$strend = trim($arrcid, ',');
		$randdatatwo = db()->query("SELECT * FROM {$dbname} where cno={$ar[$rand_keys[1]]} and sex='男' and cid not in ({$strend}) ORDER BY  RAND() LIMIT 5");
		foreach ($randdatatwo as $key => $vs) {
			db('classes337')->where('cid', $vs['cid'])->update(['cno' => $ar[$rand_keys[0]]]);
		}
	}
	
	public function linshi()
	{
		$countmale = db('students337')->field("id,name,sex,sbt")->where('sex', '男')->where('xstatus is null')->count();
		$data1 = db('students337')->field("id,name,sex,sbt")->where('sex', '男')->where('xstatus is null')->orderRaw('rand()')->select();
		halt($data1);
	}
}