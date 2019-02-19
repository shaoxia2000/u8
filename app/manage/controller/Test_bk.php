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
use think\Session;


class Test extends Common
{
    public function index()
    {

//            $res = db('students379')->where('id', 3)->find();
//            $fivezcj = $res['chinese'] + $res['smath'] + $res['english'] + $res['physics'] + $res['chemistry'];
//            dump($fivezcj);

//		$data = db('students384')->order('zcj desc')->limit(468, 500)->select();
//		halt($data);


//		大庆二十八中单独调整
//		$this->esbdeal(384);

//		大庆第四中单独调整
//		$this->ts(393, 154, 0);
//		$this->tsf(393, 257, 48, 2);
//        db('students384')->where('fcengid is null')->update(['fcengid' => 4]);
//		$this->tsf(384, 467, 33, 4);
//		$coun = db('students384')->where('fcengid', 4)->count();
//		halt($coun);

//		for ($a = 0; $a < 8; $a++) {
//			$map['tgid'] = ['eq', $a];
//			$map['gid'] = ['eq', $gid];
//			$list[$a]['bb'] = $a;
//			$list[$a]['cc'] = TgaccessModel::where($map)->select();
//			$list[$a]['dd'] = db('classes' . $schid)->where('cno', $a + 1)->select();
//		}
//		halt($list[0]);


//		$this->viewresult(8, "classes337");
//		$this->testfc(2, "classes340", 4);
//		$this->viewresultfc(2, "classes340", 0);
//		$this->viewresult(20, "classes338");
        //取出数据按总分高低排列形成新数组
//        $resetsdata = db('students340')->field('id')->order('zcj desc')->select()->toArray();
//        foreach ($resetsdata as $k => $v) {
//            $arr[] = $v['id'];
//        }
//        //将新数组分成5份，每份77人形成二维数组循环后重置为一维数组并加上分层标识
//        $newdata = $this->array_group($arr, 5, 77);
//        for ($i = 0; $i <= 4; $i++) {
//            foreach ($newdata[$i] as $key => $vos) {
//                $nn[] = $vos . '-' . $i;
//                db('students340')->where('id', $vos)->update(['fcengid' => $i]);
//            }
//        }
//
//        for ($j = 0; $j <= 4; $j++) {
//            $fcengpid = $j + 1;
//            if ($fcengpid < 4) {
//                $minzcj = db('students340')->where('fcengid', $j)->limit(1)->order('zcj asc')->value('zcj');
//                db('students340')->where(['zcj' => $minzcj, 'fcengid' => $fcengpid])->order('zcj desc')->update(['fcengid' => $j]);
//            }
//        }


//        for ($kk = 0; $kk <= 4; $kk++) {
//            $res[$kk] = db('students340')->field('zcj')->where('fcengid', $kk)->order('zcj desc')->select();
//        }
//
//        dump($res);
//        dump(array_chunk($resetsdata,77));
//        halt($this->numberAvg(388, 5));
        return view();

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

//	public function zzdeal($schid, $fcengnum)
//	{
//		//将每份衔接处相同总分选出并更新分层标识
//		for ($j = 0; $j <= $fcengnum - 1; $j++) {
//			$fcengpid = $j + 1;
//			dump($fcengpid.'---');
//			if ($fcengpid < ($fcengnum - 1)) {
//				$minzcj = db('students' . $schid)->where('fcengid', $j)->limit(1)->order('zcj asc')->value('zcj');
////				db('students' . $schid)->where(['zcj' => $minzcj, 'fcengid' => $fcengpid])->order('zcj desc')->update(['fcengid' => $j]);
////				dump(db()->getLastSql());
//				dump($j);
//			}
//		}
//	}


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







    //------------------------方法备份----------------------------

    //小学专有随机交换处理
//for ($ii = 1; $ii <= $classnum; $ii++) {
//$ar[] = $ii;
//}
//$rand_keys = array_rand($ar, 2);
//$dbname = "bk_classes".$schid;
////小学专有随机交换处理
//$randdata = db()->query("SELECT * FROM {$dbname} where cno={$ar[$rand_keys[0]]} and sex='男' ORDER BY  RAND() LIMIT 5");
//$arrcid = "";
//foreach ($randdata as $k => $v) {
//	db('classes' . $schid)->where('cid', $v['cid'])->update(['cno' => $ar[$rand_keys[1]]]);
//	$arrcid = $arrcid . $v['cid'] . ',';
//}
//$strend = trim($arrcid, ',');
//$randdatatwo = db()->query("SELECT * FROM {$dbname} where cno={$ar[$rand_keys[1]]} and sex='男' and cid not in ({$strend}) ORDER BY  RAND() LIMIT 5");
//foreach ($randdatatwo as $key => $vs) {
//	db('classes' . $schid)->where('cid', $vs['cid'])->update(['cno' => $ar[$rand_keys[0]]]);
//}

}