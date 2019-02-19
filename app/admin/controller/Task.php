<?php

namespace app\admin\controller;

use think\session;

class Task extends Common
{
    public function index()
    {
        $mm = db();
        $taskdb = 'task' . Session::get('schoolid');
        $taskdbpre = 'bk_task' . Session::get('schoolid');
        $isTable = $mm->query("SHOW TABLES LIKE '{$taskdbpre}'");
        // dump($isTable);die;
        if ($isTable) {
            $res = db($taskdb)->where('puid', Session::get('id'))->paginate(50);
            if ($res) {

                foreach ($res as $k => $v) {
                    $rex[$k]['date'] = date("Y-m-d H:i:s", $v['time']);
                }
                $this->assign('res', $res);
                $this->assign('rex', $rex);
                return view();
            } else {
                $this->error('请先创建任务！', url('excel/index'));
            }

        } else {
            $this->error('请先创建任务！', url('excel/index'));

        }
    }

    // 导出数据
    public function excel()
    {
        $studentsdb = 'students' . Session::get('schoolid');
        $schooltype = db('school')->where('id', Session::get('schoolid'))->value('stype');
        $classesdb = 'classes' . Session::get('schoolid');
        $classesnumid = input('classesnumid');
        $taskid = input('taskid');
        $tasktt = input('tasktitle');

        $stu = db($classesdb)->field('id')->where('taskid', $taskid)->where('cno', $classesnumid)->select();

        foreach ($stu as $key => $value) {
            $stu_id[] = $value['id'];
        }

        $ids = implode(',', $stu_id);
        // echo $ids;exit();

        $name = "任务" . $tasktt . ":" . $classesnumid . "班分班结果";
        if ($schooltype == 2) {
            $header = ['姓名', '语文', '数学', '外语', '总成绩', '性别', '出生日期', '民族', '户籍地址', '落户时间', '身份证类型', '身份证件号', '是否独生子女', '残疾人类型', '房屋产权人', '房屋产权人与新生关系', '房屋产权地址', '房屋产权性质', '房屋产权购买时间', '母亲姓名', '关系', '工作单位', '电话', '父亲姓名', '关系', '工作单位', '电话', '填表人', '填表人关系'];
            $data = db($studentsdb)->field('name,chinese,smath,english,zcj,sex,birthday,nation,address,
                in_time,id_type,id_num,single_is,disabled_is,house_owner,house_relation,house_address,house_type,buy_time,name_two,relation_two,job_two,tel_two, name_three,relation_three,job_three,tel_three,writer,writer_relation')->where('taskid', $taskid)->where('id', 'in', $ids)->select();
        } else {
            $header = ['姓名', '性别', '出生日期', '民族', '户籍地址', '落户时间', '身份证类型', '身份证件号', '是否独生子女', '残疾人类型', '房屋产权人', '房屋产权人与新生关系', '房屋产权地址', '房屋产权性质', '房屋产权购买时间', '母亲姓名', '关系', '工作单位', '电话', '父亲姓名', '关系', '工作单位', '电话', '填表人', '填表人关系'];
            $data = db($studentsdb)->field('name,sex,birthday,nation,address,
                in_time,id_type,id_num,single_is,disabled_is,house_owner,house_relation,house_address,house_type,buy_time,name_two,relation_two,job_two,tel_two, name_three,relation_three,job_three,tel_three,writer,writer_relation')->where('taskid', $taskid)->where('id', 'in', $ids)->select();
        }
        excelExport($name, $header, $data);
    }

    public function ccback()
    {
        $taskdb = 'task' . Session::get('schoolid');
        $schooltype = db('school')->where('id', Session::get('schoolid'))->value('stype');

        $taskid = input('taskid');
        $data = db($taskdb)->where('id', $taskid)->select();
        $res = $this->sort($data, $schooltype);
        echo json_encode($res);
    }

    // 向结果集里面增加数组属性并返回
    public function sort($data, $stype)
    {
        static $arr = array();
        foreach ($data as $k => $v) {
            if ($stype == 2) {
                $gavg = $this->getavg($v['id'], $v['classnum']);
                $v['zavg'] = $gavg;
            } else {
                $v['zavg'] = 1;
            }
            $gcount = $this->getcount($v['id'], $v['classnum']);
            $v['zcount'] = $gcount;
            $gmalecount = $this->getmalecount($v['id'], $v['classnum']);
            $v['malecount'] = $gmalecount;
            $gfemalecount = $this->getfemalecount($v['id'], $v['classnum']);
            $v['femalecount'] = $gfemalecount;
            $v['stype'] = $stype;
            $arr[] = $v;
        }
        return $arr;
    }

    public function getavg($id, $classnum)
    {
        $classesdb = 'classes' . Session::get('schoolid');
        for ($ii = 1; $ii <= $classnum; $ii++) {
            $dataavg = db($classesdb)->where('cno', $ii)->where('taskid', $id)->avg('zcj');
            $ar[] = round($dataavg, 2);
        }
        return $ar;
    }

    public function getcount($id, $classnum)
    {
        $classesdb = 'classes' . Session::get('schoolid');
        for ($ii = 1; $ii <= $classnum; $ii++) {
            $count = db($classesdb)->where('cno', $ii)->where('taskid', $id)->count();
            $ar[] = $count;
        }
        return $ar;
    }

    public function getmalecount($id, $classnum)
    {
        $classesdb = 'classes' . Session::get('schoolid');
        for ($ii = 1; $ii <= $classnum; $ii++) {
            $count = db($classesdb)->where('cno', $ii)->where('sex', '男')->where('taskid', $id)->count();
            $ar[] = $count;
        }
        return $ar;
    }

    public function getfemalecount($id, $classnum)
    {
        $classesdb = 'classes' . Session::get('schoolid');
        for ($ii = 1; $ii <= $classnum; $ii++) {
            $count = db($classesdb)->where('cno', $ii)->where('sex', '女')->where('taskid', $id)->count();
            $ar[] = $count;
        }
        return $ar;
    }

}
