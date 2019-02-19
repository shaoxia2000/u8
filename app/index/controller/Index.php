<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        return "index index index";
    }

    public function bgfb()
    {
        $datas = input("post.");
        /*
         * 循环出每个班级的平均分组成数组ar,13是分班的数量
         */

        for ($ii = 1; $ii <= $datas['classnum']; $ii++) {
            $dataavg = db('classes' . $datas['classesdb'])->where('cno', $ii)->where('taskid', $datas['taskid'])->avg('smath');
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
        $maxdata = db('classes' . $datas['classesdb'])->where('cno', $newmax)->where('taskid', $datas['taskid'])->order('smath', 'desc')->select();
        /**
         * 将数据集传送给sort方法，参数[数据集，最小班级号，理论完美差值]
         */

        $maxdatasort = explode('-', $this->sortt($maxdata, $newmin, $pp, $datas['classesdb'], $datas['taskid']));
        db('classes' . $datas['classesdb'])->where('cid', $maxdatasort[1])->where('taskid', $datas['taskid'])->update(['cno' => $newmin, 'jhnum' => 1]);
        db('classes' . $datas['classesdb'])->where('cid', $maxdatasort[2])->where('taskid', $datas['taskid'])->update(['cno' => $newmax, 'jhnum' => 1]);


        for ($iii = 1; $iii <= $datas['classnum']; $iii++) {
            $dataavgnew = db('classes' . $datas['classesdb'])->where('cno', $iii)->where('taskid', $datas['taskid'])->avg('smath');
            $arr[] = $dataavgnew;
        }
        /*
         * 找出数组里面数值最大的与数值最小的并求差
         */
        $maxnew = array_search(max($arr), $arr);
        $minnew = array_search(min($arr), $arr);
        $remnew = $arr[$maxnew] - $arr[$minnew];

        $data = ["cz" => $remnew, "pp_start" => $pp_start, "pp_end" => $pp_end, "rand" => $pp];

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
            if ($v['jhnum'] != 1) {
                $gtitle = $this->getminzcj($v['cid'], $v['smath'], $newmin, $pp, $v['sex'], $classdb, $taskid);
            }
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
            $v['newzcj'] = $maxzcj - $v['smath'];
            if ($v['newzcj'] > 0 && $v['jhnum'] != 1) {
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
}
