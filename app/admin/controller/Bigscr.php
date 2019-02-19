<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/4/25
 * Time: 17:51
 */

namespace app\admin\controller;

use think\session;


class Bigscr extends Common
{
    public function index()
    {
        $classesdb = 'classes' . Session::get('schoolid');
        $taskdb = 'task' . Session::get('schoolid');
        $taskid = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("id");
        $zongnum = db($classesdb)->where('taskid', $taskid)->count();
        $classnum = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("classnum");
        $data = db($classesdb)->field('name')->where('taskid', $taskid)->select();

        $datat = db('teacher')->field('name,thumb')->where('sch', Session::get('schoolid'))->where('status',0)->select();
        $zongtnum = db('teacher')->where('sch', Session::get('schoolid'))->where('status',0)->count();


        $this->assign('data', $data);
        for ($i = 1; $i < $classnum + 1; $i++) {
            $arr[] = $i;
        }

        $this->assign('classnum', $arr);
        $this->assign('zongtnum', $zongtnum);
        $this->assign('datat', $datat);
        $this->assign('cnum', $classnum);
        $this->assign('znum', $zongnum);
        return view();
    }

    public function cheader()
    {
        $classesdb = 'classes' . Session::get('schoolid');
        $data = db($classesdb)->field('name')->select();

        $taskdb = 'task' . Session::get('schoolid');
        $taskid = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("id");

        $this->assign('data', $data);
        $this->assign('classesdb', $classesdb);
        $this->assign('taskid', $taskid);
        return view();
    }

    public function sedheader()
    {
        $classesdb = 'classes' . Session::get('schoolid');
        $taskdb = 'task' . Session::get('schoolid');
        $taskid = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("id");

        $resname = db($classesdb)->where('taskid', $taskid)->where('jhnum', 1)->value('name');
        $this->assign('resname', $resname);
        return view();
    }

    public function teacherfp()
    {
        $classesdb = 'classes' . Session::get('schoolid');
        $taskdb = 'task' . Session::get('schoolid');
        $taskid = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("id");
        $zongnum = db($classesdb)->where('taskid', $taskid)->count();
        $zongtnum = db('teacher')->where('sch', Session::get('schoolid'))->where('status',0)->count();
        $datat = db('teacher')->field('id,name,thumb')->where('sch', Session::get('schoolid'))->where('status',0)->select();

        $jsdatat = $this->getcz($datat);

//        halt($jsdatat);


        $this->assign('jsdatat', json_encode($jsdatat));
        $this->assign('classesdb', $classesdb);
        $this->assign('taskid', $taskid);
        $this->assign('zongnum', $zongnum);
        $this->assign('zongtnum', $zongtnum);
        $this->assign('datat', $datat);
        return view();
    }


    public function resultinfo()
    {
        $classesdb = 'classes' . Session::get('schoolid');
        $taskdb = 'task' . Session::get('schoolid');
        $taskid = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("id");
        $tasktitle = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("title");
        $result = db($classesdb)->field('cno,teacherid')->where('taskid', $taskid)->group("cno")->select();
        $newrem= $this->sortnew($result);
//        halt($newrem);
        $rem = $this->sortre($result);
        $this->assign('rem', $rem);
        $this->assign('sid', Session::get('schoolid'));
        $this->assign('newrem', $newrem);
        $this->assign('tasktitle', $tasktitle);


        return view();
    }


    // 向结果集里面增加一个数组属性并返回 ，比较经典
    public function getcz($data)
    {
        static $arr = array();
        foreach ($data as $k => $v) {
            $arr[] = $v['id'] . '-' . $v['name'] . '-' . $v['thumb'];
        }
        return $arr;
    }

    public function sortre($data)
    {
        static $arr = array();
        foreach ($data as $k => $v) {
            $tname = $this->getname($v['teacherid']);
            $tp = $this->gettp($v['teacherid']);
            $v['tname']=$tname;
            $v['tp']=$tp;
            $arr[] = $v;
        }
        return $arr;
    }

    public function getname($id)
    {
        $res = db('teacher')->where('id', $id)->value('name');
        return $res;
    }

    public function gettp($id)
    {
        $res = db('teacher')->where('id', $id)->value('thumb');
        return $res;
    }


    public function sortnew($data)
    {
        static $arr = array();
        foreach ($data as $k => $v) {
            $classes = $this->getar($v['teacherid']);
            $v['classes']=$classes;
            $arr[] = $v;
        }
        return $arr;
    }

    public function getar($tid)
    {
        $taskdb = 'task' . Session::get('schoolid');
        $taskid = db($taskdb)->where('puid', Session::get('id'))->limit(1)->order('id', 'desc')->value("id");
        $classesdb = 'classes' . Session::get('schoolid');
        $res = db($classesdb)->where('teacherid', $tid)->where('taskid',$taskid)->select();
        return $res;
    }




}