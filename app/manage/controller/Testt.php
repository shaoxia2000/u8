<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/6/5
 * Time: 16:17
 */

namespace app\manage\controller;

use Predis\Client as Redis;
use Overtrue\Pinyin\Pinyin;
use think\Config;


class Testt extends Common
{
	public function index()
	{
		$config = Config::get('redis');
		$redis = new Redis($config);
		$key = 'newsfz';
		$dataredis = $redis->smembers($key);
//		$redisdata = $this->doComments($dataredis);
		halt($dataredis);
	}
	
	public function doComments($data)
	{
		foreach ($data as $v) {
			list($sfz, $schid) = explode('==||==', $v);
			$newCom[$sfz] = $schid;
		}
		return $newCom;
	}
	
	
	public function sbtarr()
	{
		$data = db('students225')->field('id,sbt')->where('sbt', 'gt', 0)->select();
		foreach ($data as $k => $v) {
			$arr[] = $v['id'] . ',' . $v['sbt'];
		}
		$str = "";
		foreach ($arr as $vv) {
			$str = $str . $vv . ',';
		}
		$strend = trim($str, ',');
		echo $strend;
	}
	
	
	public function pinyin()
	{
		dump(samenarr(58));
		
		
		$pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
		$data = db('classes58')->field('id,name')->where('sbt is null')->select();
		foreach ($data as $k => $v) {
			$name = $pinyin->permalink($v['name']);
			$newCom[$v['id']] = $name;
		}
		foreach ($newCom as $k => $v) {
			$arrn[$v][] = $k;
		}
		
		foreach ($arrn as $k => $v) {
			if (count($v) > 1) {
				echo $k . '重复下标为:　　';
				$i = 1;
				$j = 2;
				$str = "";
				foreach ($v as $vva) {
					$str = $str . $vva . ',';
				}
				$strend = trim($str, ',');
				
				foreach ($v as $vv) {
					$res = db('classes58')->field('sex', 'cno')->where('id', $vv)->find();
					echo $vv . '===' . '原始班：' . $res['cno'] . '目标班：' . $i . '　　';
					if ($res['cno'] != $i) {
						db('classes58')->where('id', $vv)->update(['cno' => $i]);
						db('classes58')->where(['sex' => $res['sex'], 'cno' => $i, 'sbt' => null])->where('id', 'not in', $strend)->limit(1)->order('cid', 'desc')->update(['cno' => $res['cno']]);
					}
					
					$i++;
					if ($i > $j) {
						$i = 1;
					}
				}
			}
		}
		
		
	}
	
	
}