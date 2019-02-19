<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/4/17
 * Time: 10:50
 */

namespace app\index\controller;


class Testone
{
    public function testone()
    {
        for ($ii = 1; $ii <= 8; $ii++) {
            $dataavg = db('classes4')->where('cno', $ii)->where('taskid', 1)->avg('smath');
            $ar[] = $dataavg;
        }

        $max = array_search(max($ar), $ar);
        $min = array_search(min($ar), $ar);
        $rem = $ar[$max] - $ar[$min];

        $pp_start = 50 * ($rem / 2 - 1);
        $pp_end = 50 * ($rem / 2 + 1);
        if ($pp_start < 0) {
            $pp_start = 0;
        }
        $pp = $this->randFloat($pp_start, $pp_end);


        $newmax = $max + 1;
        $newmin = $min + 1;
        $maxdata = db('classes4')->where('cno', $newmax)->where('taskid', 1)->order('smath', 'desc')->select();

        $maxdatasort = explode('-', $this->sortt($maxdata, $newmin, $pp, 'classes4', 1));
        db('classes4')->where('cid', $maxdatasort[1])->where('taskid', 1)->update(['cno' => $newmin]);
        db('classes4')->where('cid', $maxdatasort[2])->where('taskid', 1)->update(['cno' => $newmax]);

        for ($iii = 1; $iii <= 8; $iii++) {
            $dataavgnew = db('classes4')->where('cno', $iii)->where('taskid', 1)->avg('smath');
            $arr[] = $dataavgnew;
        }
        /*
         * 找出数组里面数值最大的与数值最小的并求差
         */
        $maxnew = array_search(max($arr), $arr);
        $minnew = array_search(min($arr), $arr);
        $remnew = $arr[$maxnew] - $arr[$minnew];

        echo $remnew;

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
            $gtitle = $this->getminzcj($v['cid'], $v['chinese'], $v['smath'], $newmin, $pp, $v['sex'], 'classes4', 1);
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


    public function getminzcj($cid, $chinese, $maxzcj, $newmin, $pp, $sex, $classdb, $taskid)
    {
        static $arr1 = array();
        /*
         * 输出平均分最小班级里面的数据，性别根据平均分最高分班级传过来的性别作为条件
         */

        $chinesestart = $chinese - 10;
        $chinesesend = $chinese + 10;

//        halt($chinesestart.','.$chinesesend);
        $data = db($classdb)->where('cno', $newmin)->where('taskid', 1)->where('sex', $sex)->select();



        /*
         * 平均分最高的班级传过来的每一条数据里面的成绩，都与平均分最低里面的所有人成绩求差
         * 形成新数组$arr1，差-平高班求差cid-平低班被求差cid
         */
        foreach ($data as $k => $v) {
            $v['newzcj'] = $maxzcj - $v['smath'];
            if ($v['newzcj'] > 0 && $v['chinese']>$chinesestart && $v['chinese']<$chinesesend) {
                $arr1[] = $v['newzcj'] . '-' . $cid . '-' . $v['cid'];
            }
        }

//        halt($arr1);

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