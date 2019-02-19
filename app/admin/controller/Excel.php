<?php

namespace app\admin\controller;

use think\session;
use think\Upload;

class Excel extends Common
{
    public function index()
    {
        $planset = db('planset')->where('schoolid', session('schoolid'))->where('status',0)->select();
        $schooltype = db('school')->where('id', session('schoolid'))->value('stype');
        $this->assign('planset', $planset);
        $this->assign('schooltype', $schooltype);
        return view();
    }

    public function step3()
    {
        return view();
    }

    public function step4()
    {
        return view();
    }

    public function step5()
    {
        $taskdb = 'task' . Session::get('schoolid');
        $classesdb = 'classes' . Session::get('schoolid');
        $classnum = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("classnum");
        $this->assign('res', $classnum);
        return view();
    }

    /**
     * 创建任务
     */
    public function test()
    {
        $data['title'] = input('title');
        $data['planset'] = input('planset');
        // $data['classnum'] = input('classnum');
        $planset = db('planset')->where("id", input('planset'))->find();
        $data['classnum'] = $planset['classnum'];

        $data['content'] = input('content');
        $data['puid'] = input('puid');
        $data['time'] = time();
        $nameid = input('nameid'); //ajax传过来的学校id

        $mm = db();
        $taskdbpre = 'bk_task' . $nameid;
        $taskdb = 'task' . $nameid;
        $studentsdbpre = 'bk_students' . $nameid;
        $studentsdb = 'students' . $nameid;
        $classesdbpre = 'bk_classes' . $nameid;
        $classesdb = 'bk_classes' . $nameid;
        $schooltype = db('school')->where('id', Session::get('schoolid'))->value('stype');

        $istask = $mm->query("SHOW TABLES LIKE '{$taskdbpre}'");
        if ($istask) {
            db($taskdb)->insert($data);
        } else {
            $sqltask = "CREATE TABLE `{$taskdbpre}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(50) NOT NULL COMMENT '任务标题',
            `classnum` tinyint(2) NOT NULL COMMENT '班级数量',
            `planset` int(10) NOT NULL COMMENT '招生设置编号',
            `time` int(10) NOT NULL COMMENT '创建时间',
            `content` varchar(255) NOT NULL COMMENT '备注',
            `puid` int(11) NOT NULL COMMENT '创建人用户id',
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            $mm->execute($sqltask);
            db($taskdb)->insert($data);
        }

        $isstudents = $mm->query("SHOW TABLES LIKE '{$studentsdbpre}'");
        if ($isstudents) {
            // echo json_encode('任务已经创建');
        } else {
            if ($schooltype == 2) {
                $sqlstudents = "CREATE TABLE `{$studentsdbpre}` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `name` varchar(15) NOT NULL COMMENT '姓名',
            `chinese` varchar(10) NOT NULL COMMENT '语文',
            `smath` varchar(10) NOT NULL COMMENT '数学',
            `english` varchar(10) NOT NULL COMMENT '英语',
            `zcj` varchar(10) NOT NULL COMMENT '总成绩',
            `sex` varchar(10) DEFAULT NULL COMMENT '性别 1男  2女',
            `taskid` int(2) DEFAULT 0,
            `birthday` varchar(60) NOT NULL COMMENT '出生日期',
            `nation` varchar(21) NOT NULL COMMENT '民族',
            `address` varchar(150) NOT NULL COMMENT '户籍地址',
            `in_time` varchar(60) NOT NULL COMMENT '落户时间',
            `id_type` varchar(60) NOT NULL COMMENT '身份证类型 1居民身份证 2香港特区护照',
            `id_num` varchar(25) NOT NULL COMMENT '身份证件号',
            `single_is` varchar(18) NOT NULL COMMENT '独生子女  1是  2否',
            `disabled_is` varchar(18) NOT NULL COMMENT '残疾人类型  1无残疾  2残疾',
            `house_owner` varchar(50) NOT NULL COMMENT '房屋产权人',
            `house_relation` varchar(15) NOT NULL COMMENT '房屋产权人与新生关系 1父亲 2母亲 3祖父母 4外祖父母',
            `house_address` varchar(150) NOT NULL COMMENT '房屋产权地址',
            `house_type` varchar(15) NOT NULL COMMENT '房屋产权性质  1私产 2公产 3其它',
            `buy_time` varchar(60) NOT NULL COMMENT '房屋产权购买时间',
            `name_two` varchar(15) NOT NULL COMMENT '姓名 2 ',
            `relation_two` varchar(15) NOT NULL COMMENT '关系 name_two 1父亲 2母亲 3祖父母 4外祖父母',
            `job_two` varchar(150) NOT NULL COMMENT '工作单位 name_two',
            `tel_two` varchar(25) NOT NULL COMMENT '电话 2',
            `name_three` varchar(15) NOT NULL COMMENT '姓名 3 ',
            `relation_three` varchar(15) NOT NULL COMMENT '关系 name_3 1父亲 2母亲 3祖父母 4外祖父母',
            `job_three` varchar(150) NOT NULL COMMENT '工作单位 name_3',
            `tel_three` varchar(25) NOT NULL COMMENT '电话 3',
            `writer` varchar(15) NOT NULL COMMENT '填表人',
            `writer_relation` varchar(15) NOT NULL COMMENT '填表人关系 1父亲 2母亲 3祖父母 4外祖父母',
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            } else {
                $sqlstudents = "CREATE TABLE `{$studentsdbpre}` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `name` varchar(15) NOT NULL COMMENT '姓名',
            `sex` varchar(10) DEFAULT NULL COMMENT '性别 1男  2女',
            `taskid` int(2) DEFAULT 0,
            `birthday` varchar(60) NOT NULL COMMENT '出生日期',
            `nation` varchar(21) NOT NULL COMMENT '民族',
            `address` varchar(150) NOT NULL COMMENT '户籍地址',
            `in_time` varchar(60) NOT NULL COMMENT '落户时间',
            `id_type` varchar(60) NOT NULL COMMENT '身份证类型 1居民身份证 2香港特区护照',
            `id_num` varchar(25) NOT NULL COMMENT '身份证件号',
            `single_is` varchar(18) NOT NULL COMMENT '独生子女  1是  2否',
            `disabled_is` varchar(18) NOT NULL COMMENT '残疾人类型  1无残疾  2残疾',
            `house_owner` varchar(50) NOT NULL COMMENT '房屋产权人',
            `house_relation` varchar(15) NOT NULL COMMENT '房屋产权人与新生关系 1父亲 2母亲 3祖父母 4外祖父母',
            `house_address` varchar(150) NOT NULL COMMENT '房屋产权地址',
            `house_type` varchar(15) NOT NULL COMMENT '房屋产权性质  1私产 2公产 3其它',
            `buy_time` varchar(60) NOT NULL COMMENT '房屋产权购买时间',
            `name_two` varchar(15) NOT NULL COMMENT '姓名 2 ',
            `relation_two` varchar(15) NOT NULL COMMENT '关系 name_two 1父亲 2母亲 3祖父母 4外祖父母',
            `job_two` varchar(150) NOT NULL COMMENT '工作单位 name_two',
            `tel_two` varchar(25) NOT NULL COMMENT '电话 2',
            `name_three` varchar(15) NOT NULL COMMENT '姓名 3 ',
            `relation_three` varchar(15) NOT NULL COMMENT '关系 name_3 1父亲 2母亲 3祖父母 4外祖父母',
            `job_three` varchar(150) NOT NULL COMMENT '工作单位 name_3',
            `tel_three` varchar(25) NOT NULL COMMENT '电话 3',
            `writer` varchar(15) NOT NULL COMMENT '填表人',
            `writer_relation` varchar(15) NOT NULL COMMENT '填表人关系 1父亲 2母亲 3祖父母 4外祖父母',
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            }
            $mm->execute($sqlstudents);
        }

        $isclasses = $mm->query("SHOW TABLES LIKE '{$classesdbpre}'");
        if ($isclasses) {
            // echo json_encode('任务已经创建');
        } else {
            if ($schooltype == 2) {
                $sqlclasses = "CREATE TABLE `{$classesdbpre}` (
            `cid` int(11) NOT NULL AUTO_INCREMENT,
            `id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            `chinese` varchar(10) NOT NULL COMMENT '语文',
            `smath` varchar(10) NOT NULL COMMENT '数学',
            `english` varchar(10) NOT NULL COMMENT '英语',
            `zcj` varchar(10) NOT NULL COMMENT '总成绩',
            `sex` varchar(255) NOT NULL,
            `cno` varchar(255) NOT NULL,
            `taskid` int(2) DEFAULT 0,
            `teacherid` int(2) DEFAULT 0,
            `jhnum` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`cid`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            } else {
                $sqlclasses = "CREATE TABLE `{$classesdbpre}` (
            `cid` int(11) NOT NULL AUTO_INCREMENT,
            `id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            `sex` varchar(255) NOT NULL,
            `cno` varchar(255) NOT NULL,
            `taskid` int(2) DEFAULT 0,
            `teacherid` int(2) DEFAULT 0,
            `jhnum` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`cid`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            }
            $mm->execute($sqlclasses);
        }
        logs();
        echo json_encode('任务创建成功');

    }

    /**
     * 上传图片
     */
    public function upload()
    {
        logs();
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if ($info) {
                // 成功上传后 获取上传信息
                // 输出 jpg
                $exts = $info->getExtension(); //获取扩展名
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                $filename = 'public/uploads/' . $info->getSaveName(); //文件路径+文件名
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                // echo $info->getFilename().'<br>';
                $this->goodsImport($filename, $exts);
            } else {
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
    }

    /**
     * 导入数据
     * @param $filename 获取文件名称
     * @param string $exts 文件格式
     */
    protected function goodsImport($filename, $exts = 'xls')
    {
        // dump($exts);die;
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
        for ($currentRow = 0; $currentRow <= $allRow; $currentRow++) {
            //从哪列开始，A表示第一列
            for ($currentColumn = 'A'; $currentColumn != $allColumn; $currentColumn++) {
                //数据坐标
                $address = $currentColumn . $currentRow;
                //读取到的数据，保存到数组$arr中
                $data[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getValue();
            }
        }
//        halt($data);
        $this->saveImport($data);
    }

    public function saveImport($data)
    {
        $dbmz = 'students' . Session::get('schoolid');
        $schooltype = db('school')->where('id', Session::get('schoolid'))->value('stype');

        foreach ($data as $k => $v) {
            if ($k > 1) {
                if ($schooltype == 1) {
                    $date['name'] = trim($v['A']);
                    $date['sex'] = $v['B'];
                    $date['birthday'] = $v['C'];
                    $date['nation'] = $v['D'];
                    $date['address'] = $v['E'];
                    $date['in_time'] = $v['F'];
                    $date['id_type'] = $v['G'];
                    $date['id_num'] = trim($v['H']);
                    $date['single_is'] = $v['I'];
                    $date['disabled_is'] = $v['J'];
                    $date['house_owner'] = $v['K'];
                    $date['house_relation'] = $v['L'];
                    $date['house_address'] = $v['M'];
                    $date['house_type'] = $v['N'];
                    $date['buy_time'] = $v['O'];
                    $date['name_two'] = $v['P'];
                    $date['relation_two'] = $v['Q'];
                    $date['job_two'] = $v['R'];
                    $date['tel_two'] = $v['S'];
                    $date['name_three'] = $v['T'];
                    $date['relation_three'] = $v['U'];
                    $date['job_three'] = $v['V'];
                    $date['tel_three'] = $v['W'];
                    $date['writer'] = $v['X'];
                    $date['writer_relation'] = $v['Y'];
                } else {
                    $date['name'] = trim($v['A']);
                    $date['chinese'] = $v['B'];
                    $date['smath'] = $v['C'];
                    $date['english'] = $v['D'];
                    $date['zcj'] = trim((int)$v['B'] + (int)$v['C'] + (int)$v['D']);
                    $date['sex'] = $v['F'];
                    $date['birthday'] = $v['G'];
                    $date['nation'] = $v['H'];
                    $date['address'] = $v['I'];
                    $date['in_time'] = $v['J'];
                    $date['id_type'] = $v['K'];
                    $date['id_num'] = trim($v['L']);
                    $date['single_is'] = $v['M'];
                    $date['disabled_is'] = $v['N'];
                    $date['house_owner'] = $v['O'];
                    $date['house_relation'] = $v['P'];
                    $date['house_address'] = $v['Q'];
                    $date['house_type'] = $v['R'];
                    $date['buy_time'] = $v['S'];
                    $date['name_two'] = $v['T'];
                    $date['relation_two'] = $v['U'];
                    $date['job_two'] = $v['V'];
                    $date['tel_two'] = $v['W'];
                    $date['name_three'] = $v['X'];
                    $date['relation_three'] = $v['Y'];
                    $date['job_three'] = $v['Z'];
                    $date['tel_three'] = $v['AA'];
                    $date['writer'] = $v['AB'];
                    $date['writer_relation'] = $v['AC'];
                }

                $result = db($dbmz)->insert($date);
            }
        }
        if ($result) {
            $taskdb = 'task' . Session::get('schoolid');
            $taskid = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("id");
            db($dbmz)->where('taskid', '=', 'null')->update(['taskid' => $taskid]);
            $num = db($dbmz)->count();
            $this->success('成功导入' . '，现在<span style="color:red">' . $num . '</span>条学生数据！', url('step3'));
        } else {
            $this->error('学生导入失败');
        }
    }

    /*
     * 导出数据测试
     */
    public function excel()
    {
        $classesnumid = input('classesnumid');
        $taskid = input('taskid');
        $name = '测试导出';
        $header = ['权限路径', '权限名称', '权限类型'];
        $data = db('auth_rule')->field('name,title,type')->select();
        // dump($data);
        excelExport($name, $header, $data);
    }


    public function randclass()
    {
        $datas = input("post.");

        for ($ii = 1; $ii <= $datas['classnum']; $ii++) {
            $datacount = db('classes' . $datas['classesdb'])->where('cno', $ii)->where('taskid', $datas['taskid'])->count();
            $ar[] = $datacount;
        }

        $max = array_search(max($ar), $ar);
        $min = array_search(min($ar), $ar);
        $rem = $ar[$max] - $ar[$min];
        $newmax = $max + 1;
        $newmin = $min + 1;
        if ($rem > 1) {
            db('classes' . $datas['classesdb'])->where('cno', $newmax)->where('taskid', $datas['taskid'])->limit(1)->order('id', 'desc')->update(['cno' => $newmin]);
        }
        return json($rem);

    }


    public function fb()
    {
        $studentsdb = 'students' . Session::get('schoolid');
        $classesdb = 'classes' . Session::get('schoolid');
        $schooltype = db('school')->where('id', Session::get('schoolid'))->value('stype');


        $taskdb = 'task' . Session::get('schoolid');
        $taskid = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("id");
        //最新的任务id
        if ($schooltype == 2) {
            $data1 = db($studentsdb)->field("id,name,chinese,smath,english,zcj,sex,taskid")->where('sex', '男')->where('taskid', $taskid)->order('zcj desc')->select();
            $res = $this->sort($data1);
            $data2 = db($studentsdb)->field("id,name,chinese,smath,english,zcj,sex,taskid")->where('sex', '女')->where('taskid', $taskid)->order('zcj desc')->select();
            $res = $this->sort($data2);
        } else {
            $data1 = db($studentsdb)->field("id,name,sex,taskid")->where('sex', '男')->where('taskid', $taskid)->select();
            $res = $this->sort($data1);
            $data2 = db($studentsdb)->field("id,name,sex,taskid")->where('sex', '女')->where('taskid', $taskid)->select();
            $res = $this->sort($data2);
        }
        $result = db($classesdb)->insertAll($res);
        db($classesdb)->where('taskid', '=', 'null')->update(['taskid' => $taskid]);
        //班级数量
        $taskdb = 'task' . Session::get('schoolid');
        $classnum = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("classnum");

        //班级总人数
        $znum = db($classesdb)->where('taskid', $taskid)->count();

        //每班平均人数
        $avgnum = $znum / $classnum;

        //数据表名

        $this->assign('classnum', $classnum);
        $this->assign('schooltype', $schooltype);
        $this->assign('avgnum', $avgnum);
        $this->assign('taskid', $taskid);
        $this->assign('classesdb', Session::get('schoolid'));
//        halt($avgnum);
        return view();

    }

    /*
     * 后台分班方法供前端ajax调用
     */
    public function bgfb()
    {
        $datas = input("post.");
        /*
         * 循环出每个班级的平均分组成数组ar,13是分班的数量
         */

        for ($ii = 1; $ii <= $datas['classnum']; $ii++) {
            $dataavg = db('classes' . $datas['classesdb'])->where('cno', $ii)->where('taskid', $datas['taskid'])->avg('zcj');
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
        $maxdata = db('classes' . $datas['classesdb'])->where('cno', $newmax)->where('taskid', $datas['taskid'])->order('zcj', 'desc')->select();
        /**
         * 将数据集传送给sort方法，参数[数据集，最小班级号，理论完美差值]
         */

        $maxdatasort = explode('-', $this->sortt($maxdata, $newmin, $pp, $datas['classesdb'], $datas['taskid']));
        db('classes' . $datas['classesdb'])->where('cid', $maxdatasort[1])->where('taskid', $datas['taskid'])->update(['cno' => $newmin]);
        db('classes' . $datas['classesdb'])->where('cid', $maxdatasort[2])->where('taskid', $datas['taskid'])->update(['cno' => $newmax]);


        for ($iii = 1; $iii <= $datas['classnum']; $iii++) {
            $dataavgnew = db('classes' . $datas['classesdb'])->where('cno', $iii)->where('taskid', $datas['taskid'])->avg('zcj');
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


    public function sortt($data, $newmin, $pp, $classdb, $taskid)
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
            $gtitle = $this->getminzcj($v['cid'], $v['zcj'], $newmin, $pp, $v['sex'], $classdb, $taskid);
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

    public function getminzcj($cid, $maxzcj, $newmin, $pp, $sex, $classdb, $taskid)
    {
        static $arr1 = array();
        /*
         * 输出平均分最小班级里面的数据，性别根据平均分最高分班级传过来的性别作为条件
         */
        $data = db('classes' . $classdb)->where('cno', $newmin)->where('taskid', $taskid)->where('sex', $sex)->select();
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

    public function sort($data)
    {
        $taskdb = 'task' . Session::get('schoolid');
        $classnum = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("classnum");
        // 最新任务中的班级数量
        static $arr = array();
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


    //excel模板导出展示页1111
    public function template()
    {
        return view();
    }

    public function export()
    {
        $name = "Excel模板";
        $header = ['姓名', '总成绩', '性别', '出生日期', '民族', '户籍地址', '落户时间', '身份证类型', '身份证件号', '是否独生子女', '残疾人类型', '房屋产权人', '房屋产权人与新生关系', '房屋产权地址', '房屋产权性质', '房屋产权购买时间', '母亲姓名', '关系', '工作单位', '电话', '父亲姓名', '关系', '工作单位', '电话', '填表人', '填表人关系'];
        $data = array();
        $data[0] = ['张梓涵', '100', '女', '2012-07-21', '汉族', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx'];
        excelExport($name, $header, $data);
    }

    public function sexport()
    {
        $name = "Excel模板";
        $header = ['姓名', '性别', '出生日期', '民族', '户籍地址', '落户时间', '身份证类型', '身份证件号', '是否独生子女', '残疾人类型', '房屋产权人', '房屋产权人与新生关系', '房屋产权地址', '房屋产权性质', '房屋产权购买时间', '母亲姓名', '关系', '工作单位', '电话', '父亲姓名', '关系', '工作单位', '电话', '填表人', '填表人关系'];
        $data = array();
        $data[0] = ['张梓涵', '女', '2012-07-21', '汉族', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx', 'xxx'];
        excelExport($name, $header, $data);
    }

}
