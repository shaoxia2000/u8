<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/6/11
 * Time: 17:05
 */

namespace app\manage\controller;

use app\common\model\Bingo as BingoModel;
use app\common\model\Recordassign as RecordassignModel;
use app\common\model\School as SchoolModel;
use app\common\model\Setgroup;
use app\common\model\Teacher as TeacherModel;
use app\common\model\Teachergroup as TeachergroupModel;
use app\common\model\Tfaccess as TfaccessModel;
use app\common\model\Tgaccess as TgaccessModel;
use think\Session;


class Assigngroup extends Common
{
    public function index()
    {
        $data = TeachergroupModel::where(['status' => 1, 'astatus' => 1, 'areaid' => Session::get('area_id')])->paginate();
        if (count($data) == 0) {
            $this->error('不存在可一键分组的学校！', url('students/index', ['ac2' => 1]));
        }
        foreach ($data as $k => $v) {
            $this->CheckClassesTable($v['schid'], $v['fcengnum'], $v['tnum'], $v['id'], GetSchooltype($v['schid']));
            $datay[] = $v['schid'];
        }
//		$this->clearclasses($datay);
        $datanmus = TeachergroupModel::where(['status' => 1, 'astatus' => 1, 'areaid' => Session::get('area_id')])->count();
        $this->assign(array('data' => $data, 'datanums' => $datanmus));
        return view();
    }


    public function checkassigngroup()
    {
        $data = input('post.');
        $res = RecordassignModel::where('areaid', $data['areaid'])->find();
        if ($res) {
            if ($res['assigngroupnums'] != $data['assigngroupnums']) {
                RecordassignModel::where('id', $res['id'])->update(['assigngroupnums' => $data['assigngroupnums']]);
                return 1;
            } else {
                return 0;
            }
        } else {
            RecordassignModel::create($data);
            return 1;
        }
    }


    /**
     * 设置教师组首页
     */
    public function view()
    {
        $gid = input('id');
        $schid = input('schid');
        $res = TeachergroupModel::where('id', $gid)->find();
        $resfc = $res['fcengnum'];
        for ($a = 0; $a < $res['tnum']; $a++) {
            $map['tgid'] = ['eq', $a];
            $map['gid'] = ['eq', $gid];
            $list[$a]['bb'] = $a;
            $list[$a]['cc'] = TgaccessModel::where($map)->select();
            $list[$a]['dd'] = db('classes' . $schid)->where('cno', $a + 1)->select();
        }
        for ($b = 0; $b < $res['fcengnum']; $b++) {
            $maps['fid'] = ['eq', $b];
            $maps['gid'] = ['eq', $gid];
            $listfc[$b]['bb'] = $b;
            $listfc[$b]['cc'] = TfaccessModel::where($maps)->select();
            $listfc[$b]['ee'] = TfaccessModel::where($maps)->count();
        }
        $where['schid'] = ['eq', $schid];
        $tdata = TeacherModel::where($where)->select();
        $this->assign(array('list' => $list, 'listfc' => $listfc, 'gid' => $gid, 'tdata' => $tdata, 'resfc' => $resfc, 'schid' => $schid, 'fbnum' => $res['tnum']));
        return view();
    }

    public function CheckClassesTable($schid, $fcengnum, $classnum, $tgroupid, $schtype)
    {
        $cdbname = "bk_classes" . $schid;
        $mm = db();
        $isstudents = $mm->query("SHOW TABLES LIKE '{$cdbname}'");
        if (!$isstudents) {
            $sqlclasses1 = "CREATE TABLE `{$cdbname}` (
			  `cid` int(11) NOT NULL AUTO_INCREMENT,
			  `id` int(11) NOT NULL,
			  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  `sex` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  `cno` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  `sbt` int(2) DEFAULT NULL COMMENT '是否是双胞胎,有值为是',
			  `tgroupid` int(1) DEFAULT NULL COMMENT '教师组id',
			  PRIMARY KEY (`cid`)
                 ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            $sqlclasses2 = "CREATE TABLE `{$cdbname}` (
			  `cid` int(11) NOT NULL AUTO_INCREMENT,
			  `id` int(11) NOT NULL,
			  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  `chinese` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '语文',
			  `smath` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '数学',
			  `english` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '英语',
			  `zcj` int(2) DEFAULT NULL COMMENT '总成绩',
			  `sex` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  `cno` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  `sbt` int(2) DEFAULT NULL COMMENT '是否是双胞胎,有值为是',
			  `tgroupid` int(1) DEFAULT NULL COMMENT '教师组id',
			  PRIMARY KEY (`cid`)
                 ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            $sqlclasses3 = "CREATE TABLE `{$cdbname}` (
			  `cid` int(11) NOT NULL AUTO_INCREMENT,
			  `id` int(11) NOT NULL,
			  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  `zcj` decimal(10,1) DEFAULT NULL COMMENT '总成绩',
			  `sex` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  `cno` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  `tgroupid` int(1) DEFAULT NULL COMMENT '教师组id',
			  `fcengid` int(1) DEFAULT NULL COMMENT '分层标识',
			  PRIMARY KEY (`cid`)
                 ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            switch ($schtype) {
                case 1:
                    $mm->execute($sqlclasses1);
                    break;
                case 2:
                    $mm->execute($sqlclasses2);
                    break;
                case 3:
                    $mm->execute($sqlclasses3);
                    break;
            }
            if ($fcengnum == 0) {
                if ($schtype == 1) {
                    $data1 = db('students' . $schid)->field("id,name,sex,sbt")->where('sex', '男')->where('xstatus is null')->orderRaw('rand()')->select();
                    $res1 = $this->sort($data1, $classnum);
                    $data2 = db('students' . $schid)->field("id,name,sex,sbt")->where('sex', '女')->where('xstatus is null')->orderRaw('rand()')->select();
                    $res2 = $this->sort($data2, $classnum);
                    $newdata = array_merge($res1, $res2);
                    db('classes' . $schid)->insertAll($newdata);

                    //双胞胎处理
                    $setres = Setgroup::where('schid', $schid)->find();
                    if ($setres) {
                        if ($setres['sbt'] == 1) {
                            //双胞胎在同一组处理
                            $sbtdata = db('classes' . $schid)->where('sbt', 'gt', 0)->select();
                            foreach ($sbtdata as $k => $v) {
                                checksbtyes($schid, $v['sbt'], $v['cno']);
                            }
                        }
                        if ($setres['sbt'] == 2) {
                            //双胞胎不在同一组处理
                            $sbtdata = db('classes' . $schid)->where('sbt', 'gt', 0)->select();
                            foreach ($sbtdata as $k => $v) {
                                checksbtno($schid, $v['sbt'], $v['cno']);
                            }
                        }
                    }

                    //bingo处理
                    $bingodata = BingoModel::where('schid', $schid)->find();
                    if ($bingodata) {
                        $bdata = explode(",", $bingodata['studentscon']);
                        foreach ($bdata as $key) {
                            $sincno = db('classes' . $schid)->where('id', $key)->find();
                            db('classes' . $schid)->where('id', 'not in', $bingodata['studentscon'])->where('cno', $bingodata['xsz'])->where('sex', $sincno['sex'])->limit(1)->order('id desc')->update(['cno' => $sincno['cno']]);
                            db('classes' . $schid)->where('id', $key)->update(['cno' => $bingodata['xsz']]);
                        }
                    }


                }

                if ($schtype == 2) {
                    $data1 = db('students' . $schid)->field("id,name,chinese,smath,english,zcj,sex,sbt")->where('sex', '男')->where('xstatus is null')->order('zcj desc')->select();
                    $res1 = $this->sort($data1, $classnum);
                    $data2 = db('students' . $schid)->field("id,name,chinese,smath,english,zcj,sex,sbt")->where('sex', '女')->where('xstatus is null')->order('zcj desc')->select();
                    $res2 = $this->sort($data2, $classnum);
                    $newdata = array_merge($res1, $res2);
                    db('classes' . $schid)->insertAll($newdata);

                    $setres = Setgroup::where('schid', $schid)->find();
                    if ($setres) {
                        if ($setres['sbt'] == 1) {
                            //双胞胎在同一组处理
                            $sbtdata = db('classes' . $schid)->where('sbt', 'gt', 0)->select();
                            foreach ($sbtdata as $k => $v) {
                                checksbtyes($schid, $v['sbt'], $v['cno']);
                            }
                        }
                        if ($setres['sbt'] == 2) {
                            //双胞胎不在同一组处理
                            $sbtdata = db('classes' . $schid)->where('sbt', 'gt', 0)->select();
                            foreach ($sbtdata as $k => $v) {
                                checksbtno($schid, $v['sbt'], $v['cno']);
                            }
                        }
                    }


                    //bingo处理
                    $bingodata = BingoModel::where('schid', $schid)->find();
                    if ($bingodata) {
                        $bdata = explode(",", $bingodata['studentscon']);
                        foreach ($bdata as $key) {
                            $sincno = db('classes' . $schid)->where('id', $key)->find();
                            db('classes' . $schid)->where('id', 'not in', $bingodata['studentscon'])->where('cno', $bingodata['xsz'])->where('sex', $sincno['sex'])->limit(1)->order('id desc')->update(['cno' => $sincno['cno']]);
                            db('classes' . $schid)->where('id', $key)->update(['cno' => $bingodata['xsz']]);
                        }
                    }

                }

                if ($schtype == 3) {
                    $data1 = db('students' . $schid)->field("id,name,zcj,sex")->where('sex', '男')->where('xstatus is null')->order('zcj desc')->select();
                    $res1 = $this->sort($data1, $classnum);
                    $data2 = db('students' . $schid)->field("id,name,zcj,sex")->where('sex', '女')->where('xstatus is null')->order('zcj desc')->select();
                    $res2 = $this->sort($data2, $classnum);
                    $newdata = array_merge($res1, $res2);
                    db('classes' . $schid)->insertAll($newdata);
                }


            } else {
                for ($i = 0; $i < $fcengnum; $i++) {
                    $datamale = db('students' . $schid)->field("id,name,zcj,sex,fcengid")->where(['fcengid' => $i])->where('xstatus is null')->order('zcj desc')->select();
                    $cengnum = GetClassescnum($tgroupid, $i);
                    $resmale = $this->sort($datamale, $cengnum);
//                    $datafemale = db('students' . $schid)->field("id,name,zcj,sex,fcengid")->where(['fcengid' => $i, 'sex' => '女'])->where('xstatus is null')->order('zcj desc')->select();
//                    $resfemale = $this->sort($datafemale, $cengnum);
//                    $newdata = array_merge($resmale, $resfemale);
                    db('classes' . $schid)->insertAll($resmale);
                    //后加的五科均衡处理
//                    $datafive = db('classes' . $schid)->select();
//                    foreach ($datafive as $k => $v) {
//                        $newfivezcj = Getfivezcj($schid, $v['id']);
//                        db('classes' . $schid)->where('id', $v['id'])->update(['zcj' => $newfivezcj]);
//                    }
                }
            }

        }

    }

    /**
     * 重新整理数组，处理双胞胎
     * @param $data
     * @return array
     */
    public function sortsbt($data, $schid)
    {
        foreach ($data as $k => $v) {
            $this->upsbt($v['cid'], $v['cno'], $v['sbt'], $schid);
        }
    }


    /**
     * 更新数组中的双胞胎交换班级
     * @param $cno
     * @param $sbt
     */
    public function upsbt($cid, $cno, $sbt, $schid)
    {
        $map['cid'] = ['neq', $cid];
        $map['sbt'] = ['eq', $sbt];
        $res = db('classes' . $schid)->where($map)->select();
        foreach ($res as $k => $v) {
            $mapnew['cno'] = ['eq', $cno];
            $mapnew['sex'] = ['eq', $v['sex']];
            db('classes' . $schid)->where($mapnew)->where('sbt is NULL')->limit(1)->order('cid', 'desc')->update(['cno' => $v['cno']]);
            db('classes' . $schid)->where('cid', $v['cid'])->update(['cno' => $cno]);
        }
    }


    /**
     * @param 分层结果集分男女重组
     * @param $data
     * @return array
     */
    public function sortmale($data, $cengid)
    {
        foreach ($data as $k => $v) {
            if ($v['sex'] == '男') {
                $v['fceng'] = $cengid;
                $arr[] = $v;
            }
        }
        return $arr;
    }

    public function sortfemale($data, $cengid)
    {
        foreach ($data as $k => $v) {
            if ($v['sex'] == '女') {
                $v['fceng'] = $cengid;
                $arr[] = $v;
            }
        }
        return $arr;
    }


    /**
     * S型分班算法-蛇形分班排列
     * @param $data
     * @param $classnum /班级数量
     * @return array
     */
    public function sort($data, $classnum)
    {
        $i = 1;
        $j = $classnum;
        foreach ($data as $k => $v) {
            if ($i <= $j) {
                $v['cno'] = $i;
                $i++;
            } else {
                $v['cno'] = $j;
                $j--;
                if ($j == 0) {
                    $i = 1;
                    $j = $classnum;
                }
            }
            $arr[] = $v;

        }
        return $arr;
    }




    /**
     * --------------一道华丽的分隔符【以下进入分班正式逻辑】-----------------------
     */

    /**
     * 均衡班级人数方法
     */
    public function randclass()
    {
        $datas = input("post.");
        for ($ii = 1; $ii <= $datas['classnum']; $ii++) {
            $datacount = db($datas['classesdb'])->where('cno', $ii)->count();
            $ar[] = $datacount;
        }

        $max = array_search(max($ar), $ar);
        $min = array_search(min($ar), $ar);
        $rem = $ar[$max] - $ar[$min];
        $newmax = $max + 1;
        $newmin = $min + 1;
        if ($rem > 1) {
            $dataid = db($datas['classesdb'])->field('sbt')->where('sbt', 'gt', 0)->select();
            if (count($dataid) > 0) {
                $map['id'] = ['not in', $dataid];
                $map['sbt'] = ['null'];
            }
            $map['cno'] = ['eq', $newmax];
            db($datas['classesdb'])->where($map)->limit(1)->order('id', 'desc')->update(['cno' => $newmin]);

        }
        return json($rem);
    }

    /**
     * ---------------------华丽的分割线多层分班开始----------------------------
     */


    public function randclassfc()
    {
        $datas = input("post.");
        for ($ii = 1; $ii <= $datas['classnum']; $ii++) {
            $datacount = db($datas['classesdb'])->where('cno', $ii)->where('fcengid', $datas['fceng'])->count();
            $ar[] = $datacount;
        }

        $max = array_search(max($ar), $ar);
        $min = array_search(min($ar), $ar);
        $rem = $ar[$max] - $ar[$min];
        $newmax = $max + 1;
        $newmin = $min + 1;
        if ($rem > 1) {
            db($datas['classesdb'])->where('cno', $newmax)->where('fcengid', $datas['fceng'])->limit(1)->order('id', 'desc')->update(['cno' => $newmin]);
        }
        return json($rem);

    }


    public function bgfbfc()
    {
        $datas = input("post.");
        /*
         * 循环出每个班级的平均分组成数组ar,13是分班的数量
         */

        for ($ii = 1; $ii <= $datas['classnum']; $ii++) {
            $dataavg = db($datas['classesdb'])->where('cno', $ii)->where('fcengid', $datas['fceng'])->avg('zcj');
            $ar[] = $dataavg;
        }
        /*
         * 找出数组里面数值最大的与数值最小的并求差
         */
        $max = array_search(max($ar), $ar);
        $min = array_search(min($ar), $ar);
        $rem = $ar[$max] - $ar[$min];
        /*
         * 平均分最高的班级里面的所有成员分别与平均分最低里面的成员相减求差，得出的差值最接近以下公式的pp值的
         * 则确认交换！公式里面的33为每个班的平均人数，400/12(总人数/班级数)四舍五入得出！理论完美差值
         */

//        $pp = $datas['avgnum'] * ($rem) / 2;

        /**
         * 为了保证随机性，所以在完美值范围内随机出一个浮点型的完美值
         */


        $pp_start = $datas['avgnum'] * ($rem / 2 - 1);
        $pp_end = $datas['avgnum'] * ($rem / 2 + 1);
//        $pp = $this->randomFloat($pp_start, $pp_end);
        if ($pp_start < 0) {
            $pp_start = 0;
        }
//        $pp = mt_rand($pp_start, $pp_end);
        $pp = $this->randFloat($pp_start, $pp_end);
        /*
         * 打印原始数组、打印最接近差值、打印平均分差值（方便查看每次刷新后是否在不断变小）
    //         */
//        dump($ar);
//        dump($pp);
//        dump('平均分差值:' . $rem);


        /*
         * 由于数组是从0开始的，所以下标+1与班级号对应绑定
         */
        $newmax = $max + 1;
        $newmin = $min + 1;
//        dump('最大班级号:' . $newmax);
//        dump('最小班级号:' . $newmin);
        /*
         * 输出平均分最大的班级数据集
         */
        $maxdata = db($datas['classesdb'])->where('cno', $newmax)->where('fcengid', $datas['fceng'])->order('zcj', 'desc')->select();
        /**
         * 将数据集传送给sort方法，参数[数据集，最小班级号，理论完美差值]
         */

        $maxdatasort = explode('-', $this->sorttceng($maxdata, $newmin, $pp, $datas['classesdb'], $datas['fceng']));
        db($datas['classesdb'])->where('cid', $maxdatasort[1])->update(['cno' => $newmin]);
        db($datas['classesdb'])->where('cid', $maxdatasort[2])->update(['cno' => $newmax]);


        for ($iii = 1; $iii <= $datas['classnum']; $iii++) {
            $dataavgnew = db($datas['classesdb'])->where('cno', $iii)->where('fcengid', $datas['fceng'])->avg('zcj');
            $arr[] = $dataavgnew;
        }
        /*
         * 找出数组里面数值最大的与数值最小的并求差
         */
        $maxnew = array_search(max($arr), $arr);
        $minnew = array_search(min($arr), $arr);
        $remnew = $arr[$maxnew] - $arr[$minnew];


        $data = ["cz" => $remnew, "pp_start" => $pp_start, "pp_end" => $pp_end, "rand" => $pp, "newmax" => $newmax, "newmin" => $newmin, "cmaxno" => $maxdatasort[1], "cminno" => $maxdatasort[2]];

        return json($data);
    }

    public function sorttceng($data, $newmin, $pp, $classdb, $fceng)
    {
        static $arr1 = array();
        foreach ($data as $k => $v) {
            /*
             * 循环所接收的数据，通过getminzcj方法返回$v['zcj']与平均分最小班级所有人求差后
             * 最接近理论完美差值的那个值，并增加$v['cz']字段，用来接收此值
             * 返回来的值'cz' => string '11-253-259' (length=9)
             * 11是与理论完美差值做比较用的，如果是最接近的那么确认
             * 平均分高的班级里面的cid=253的与平均分低的班级里面的cid=259的进行交换
             * $v['sex']这里面传入了这个值，是为了男生和男生换，女生只和女生换
             */
            $gtitle = $this->getminzcjceng($v['cid'], $v['zcj'], $newmin, $pp, $v['sex'], $classdb, $fceng);
            $v['cz'] = $gtitle;
            $arr1[] = $v['cz'];
        }
        /*
         * 到这里就打印并停止了！并没有继续增加逻辑代码，方便手工调试观察
         */
//        halt($arr);

        $x = $pp;
        $count = count($arr1);
        for ($i = 0; $i < $count; $i++) {
            $newnew = explode('-', $arr1[$i]);
            $arr2[] = abs($x - intval($newnew[0]));
        }
        $min = min($arr2);
        for ($i = 0; $i < $count; $i++) {
            if ($min == $arr2[$i]) {
                return $arr1[$i];
            }
        }
    }


    public function getminzcjceng($cid, $maxzcj, $newmin, $pp, $sex, $classdb, $fceng)
    {
        static $arr1 = array();
        /*
         * 输出平均分最小班级里面的数据，性别根据平均分最高分班级传过来的性别作为条件
         */
        $data = db($classdb)->where('cno', $newmin)->where('fcengid', $fceng)->where('sex', $sex)->select();
        /*
         * 平均分最高的班级传过来的每一条数据里面的成绩，都与平均分最低里面的所有人成绩求差
         * 形成新数组$arr1，差-平高班求差cid-平低班被求差cid
         */
        foreach ($data as $k => $v) {
            $v['newzcj'] = $maxzcj - $v['zcj'];
            if ($v['newzcj'] > 0) {
                $arr1[] = $v['newzcj'] . '-' . $cid . '-' . $v['cid'];
            }
        }

        /*
         * 以下代码的功能是，在得出$arr1数组很多求差结果后，将最接近理论完美差值的那条数据返回去
         * 其它数据自动过滤掉
         */

        $x = $pp;
        $count = count($arr1);
        for ($i = 0; $i < $count; $i++) {
            $newnew = explode('-', $arr1[$i]);
            $arr2[] = abs($x - intval($newnew[0]));
        }
        $min = min($arr2);
        for ($i = 0; $i < $count; $i++) {
            if ($min == $arr2[$i]) {
                return $arr1[$i];
            }
        }

    }


    /**
     * ---------------------华丽的分割线多层分班结束----------------------------
     */


    /**
     * 后台分班ajax调用
     */
    public function bgfb()
    {
        $datas = input("post.");
        /*
         * 循环出每个班级的平均分组成数组ar,13是分班的数量
         */

        for ($ii = 1; $ii <= $datas['classnum']; $ii++) {
            $dataavg = db($datas['classesdb'])->where('cno', $ii)->avg('zcj');
            $ar[] = $dataavg;
        }
        /*
         * 找出数组里面数值最大的与数值最小的并求差
         */
        $max = array_search(max($ar), $ar);
        $min = array_search(min($ar), $ar);
        $rem = $ar[$max] - $ar[$min];
        /*
         * 平均分最高的班级里面的所有成员分别与平均分最低里面的成员相减求差，得出的差值最接近以下公式的pp值的
         * 则确认交换！公式里面的33为每个班的平均人数，400/12(总人数/班级数)四舍五入得出！理论完美差值
         */

//        $pp = $datas['avgnum'] * ($rem) / 2;

        /**
         * 为了保证随机性，所以在完美值范围内随机出一个浮点型的完美值
         */


        $pp_start = $datas['avgnum'] * ($rem / 2 - 1);
        $pp_end = $datas['avgnum'] * ($rem / 2 + 1);
//        $pp = $this->randomFloat($pp_start, $pp_end);
        if ($pp_start < 0) {
            $pp_start = 0;
        }
//        $pp = mt_rand($pp_start, $pp_end);
        $pp = $this->randFloat($pp_start, $pp_end);
        /*
         * 打印原始数组、打印最接近差值、打印平均分差值（方便查看每次刷新后是否在不断变小）
    //         */
//        dump($ar);
//        dump($pp);
//        dump('平均分差值:' . $rem);


        /*
         * 由于数组是从0开始的，所以下标+1与班级号对应绑定
         */
        $newmax = $max + 1;
        $newmin = $min + 1;
//        dump('最大班级号:' . $newmax);
//        dump('最小班级号:' . $newmin);
        /*
         * 输出平均分最大的班级数据集
         */
        $maxdata = db($datas['classesdb'])->where('cno', $newmax)->order('zcj', 'desc')->select();
        /**
         * 将数据集传送给sort方法，参数[数据集，最小班级号，理论完美差值]
         */

        $maxdatasort = explode('-', $this->sortt($maxdata, $newmin, $pp, $datas['classesdb'], $datas['taskid']));
        db($datas['classesdb'])->where('cid', $maxdatasort[1])->update(['cno' => $newmin]);
        db($datas['classesdb'])->where('cid', $maxdatasort[2])->update(['cno' => $newmax]);


        for ($iii = 1; $iii <= $datas['classnum']; $iii++) {
            $dataavgnew = db($datas['classesdb'])->where('cno', $iii)->avg('zcj');
            $arr[] = $dataavgnew;
        }
        /*
         * 找出数组里面数值最大的与数值最小的并求差
         */
        $maxnew = array_search(max($arr), $arr);
        $minnew = array_search(min($arr), $arr);
        $remnew = $arr[$maxnew] - $arr[$minnew];


        $data = ["cz" => $remnew, "pp_start" => $pp_start, "pp_end" => $pp_end, "rand" => $pp, "newmax" => $newmax, "newmin" => $newmin, "cmaxno" => $maxdatasort[1], "cminno" => $maxdatasort[2]];

        return json($data);
    }


    function randFloat($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    public function sortt($data, $newmin, $pp, $classdb)
    {
        static $arr1 = array();
        foreach ($data as $k => $v) {
            /*
             * 循环所接收的数据，通过getminzcj方法返回$v['zcj']与平均分最小班级所有人求差后
             * 最接近理论完美差值的那个值，并增加$v['cz']字段，用来接收此值
             * 返回来的值'cz' => string '11-253-259' (length=9)
             * 11是与理论完美差值做比较用的，如果是最接近的那么确认
             * 平均分高的班级里面的cid=253的与平均分低的班级里面的cid=259的进行交换
             * $v['sex']这里面传入了这个值，是为了男生和男生换，女生只和女生换
             */
            $gtitle = $this->getminzcj($v['cid'], $v['zcj'], $newmin, $pp, $v['sex'], $classdb);
            $v['cz'] = $gtitle;
            $arr1[] = $v['cz'];
        }
        /*
         * 到这里就打印并停止了！并没有继续增加逻辑代码，方便手工调试观察
         */
//        halt($arr);

        $x = $pp;
        $count = count($arr1);
        for ($i = 0; $i < $count; $i++) {
            $newnew = explode('-', $arr1[$i]);
            $arr2[] = abs($x - intval($newnew[0]));
        }
        $min = min($arr2);
        for ($i = 0; $i < $count; $i++) {
            if ($min == $arr2[$i]) {
                return $arr1[$i];
            }
        }
    }

    public function getminzcj($cid, $maxzcj, $newmin, $pp, $sex, $classdb)
    {
        static $arr1 = array();
        /*
         * 输出平均分最小班级里面的数据，性别根据平均分最高分班级传过来的性别作为条件
         */

        $dataid = db($classdb)->field('sbt')->where('sbt', 'gt', 0)->select();
        if (count($dataid) > 0) {
            $map['id'] = ['not in', $dataid];
            $map['sbt'] = ['null'];
        }

        $bingoschid = str_replace("classes", "", $classdb);
        $bingodata = BingoModel::where('schid', $bingoschid)->find();
        if ($bingodata) {
            $map['id'] = ['not in', $bingodata['studentscon']];
        }

        $map['cno'] = ['eq', $newmin];
        $map['sex'] = ['eq', $sex];


        $data = db($classdb)->where($map)->select();
        /*
         * 平均分最高的班级传过来的每一条数据里面的成绩，都与平均分最低里面的所有人成绩求差
         * 形成新数组$arr1，差-平高班求差cid-平低班被求差cid
         */
        foreach ($data as $k => $v) {
            $v['newzcj'] = $maxzcj - $v['zcj'];
            if ($v['newzcj'] > 0) {
                $arr1[] = $v['newzcj'] . '-' . $cid . '-' . $v['cid'];
            }
        }

        /*
         * 以下代码的功能是，在得出$arr1数组很多求差结果后，将最接近理论完美差值的那条数据返回去
         * 其它数据自动过滤掉
         */

        $x = $pp;
        $count = count($arr1);
        for ($i = 0; $i < $count; $i++) {
            $newnew = explode('-', $arr1[$i]);
            $arr2[] = abs($x - intval($newnew[0]));
        }
        $min = min($arr2);
        for ($i = 0; $i < $count; $i++) {
            if ($min == $arr2[$i]) {
                return $arr1[$i];
            }
        }

    }


    public function clearclasses($data)
    {
        $schooldata = SchoolModel::where('areaid', Session::get('area_id'))->select();
        foreach ($schooldata as $key => $v) {
            if (!in_array($v['schid'], $data)) {
                $dbname = "bk_classes" . $v['schid'];
                $isclasses = db()->query("SHOW TABLES LIKE '{$dbname}'");
                if ($isclasses) {
                    $sqldel = "DROP TABLE `{$dbname}`";
                    db()->execute($sqldel);
                }
            }
        }
    }

    public function bingo()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $result = BingoModel::get(['schid' => $data['schid']]);
            if ($result) {
                $result->delete();
            }
            RecordassignModel::where('areaid', Session::get('area_id'))->setDec('assigngroupnums');
            $dbname = 'bk_classes' . $data['schid'];
            $sqldel = "DROP TABLE `{$dbname}`";
            db()->execute($sqldel);
            $newdata = explode("|", $data['studentscon']);
            $str1 = "";
            foreach ($newdata as $key) {
                $val = db('students' . $data['schid'])->where('id_num', $key)->value('id');
                $str1 = $str1 . $val . ',';
            }
            $strend = trim($str1, ',');
            $data['studentscon'] = $strend;
            BingoModel::create($data);
            $remsg['code'] = 100;
            $remsg['msg'] = "添加成功";
            return $remsg;
        }

        $data = TeachergroupModel::where(['status' => 1, 'astatus' => 1, 'schid' => input('schid')])->find();
        $bingodata = BingoModel::where('schid', input('schid'))->find();
        if ($bingodata) {
            $scon = explode(",", $bingodata['studentscon']);
            foreach ($scon as $key) {
                $val = db('students' . input('schid'))->where('id', $key)->value('id_num');
                $str1 = $str1 . $val . '|';
            }
            $strend = trim($str1, '|');

            $this->assign(array('xsz' => $bingodata['xsz'], 'jsz' => $bingodata['jsz'], 'studentscon' => $strend));
        }
        $this->assign('data', $data);
        return view();
    }


}