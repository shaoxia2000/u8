<?php
namespace app\mob\controller;
use think\Controller;
use think\Request;

class Result extends Controller
{

    public function status()
    {
        $request = Request::instance();
        $areaid = $request->param('areaid');
        $subscribe = $request->param('subscribe');
        $type = $request->param('type');
        $referer = parse_url($_SERVER['HTTP_REFERER']);

        if ($subscribe === '0' || $subscribe !== '1') {
            $arealist = db('area')->where('area_id',$areaid)->find();
            $this->assign('areaname', $arealist['area_name']);
            $this->assign('areaid', $areaid);
            return view('erro');
        } else {
            if ($referer['host'] != $_SERVER['SERVER_NAME']) {
                if ($type == 'bb') 
                {
                    $schoollist = db('school')->where('areaid', $areaid)->where('schtype','neq','3')->select();

                    //检查校的设置里是否隐藏了该学校
                    foreach ($schoollist as $key => $value) 
                    {
                        $seting = db('schoolset')->field('hide')->where('schid',$value['schid'])->find();
                        //判断学校是否参加分班
                        $ifgstatus = db('teachergroup')->field('astatus')->where('schid',$value['schid'])->where('astatus',1)->find();
                        if($ifgstatus['astatus']==0)
                        {
                            // echo 123;
                            // echo $value['schname'];
                            unset($schoollist[$key]);
                        }else
                        {
                            if($seting['hide']==1)
                            {
                                // echo 234;
                                // echo $value['schname'];
                                unset($schoollist[$key]);
                            }
                        }
                        
                    }
                    // exit;
                    //检查区的设置里是否开启了系统维护
                    $areasetlist = db('areaset')->where('area_id',$areaid)->find();
                
                    if($areasetlist['erro']==1)
                    {
                        $this->assign('errotext', $areasetlist['errotext']);
                        return view('xt');
                    }

                    $this->assign('schoollist', $schoollist);
                    $this->assign('areaid', $areaid);
                    return view('index');
                }else
                {
                    $this->assign('errotext','系统维护中,请等候....');
                    //参数没接到开启系统维护中
                    return view('xt');
                }


            }
        }
    }
    public function checkform()
    {
        $schid = input('post.schname');
        $id_num = input('post.id_num');
        $sname = input('post.sname');
        $areaid = input('post.areaid');
        //查区的设置表
        $areasetlist = db('areaset')->where('area_id',$areaid)->find();   
        //区查询关闭开启   
        if($areasetlist['start']==1)
        {

            $status = 4;
            $this->assign('status', $status);
            $this->assign('host', $_SERVER['HTTP_HOST']);
            return view('msg');
        }
        //查校的设置表
        $exset = db('schoolset')->field('start,erro,errotext')->where('schid',$schid)->find();
        //校开启系统维护
        if($exset['erro']==1)
        {
            $this->assign('errotext', $exset['errotext']);
            return view('xt');  
        }
        //校查询关闭
        if($exset['start']==1)
        {
            $status = 3;
            $this->assign('areaid', $areaid);
            $this->assign('status', $status);
            $this->assign('host', $_SERVER['HTTP_HOST']);
            return view('msg');

        }
        $table = 'bk_students' . $schid;
        $exist = db()->query('show tables like "' . $table . '"');
        if (!$exist) {
            $status = 1;//数据表不存在
            $this->assign('areaid', $areaid);
            $this->assign('status', $status);
            $this->assign('host', $_SERVER['HTTP_HOST']);
            return view('msg');
        }
        $stinfo = db('students' . $schid)->field('id')->where('id_num', $id_num)->where('name', $sname)->find();
        if (empty($stinfo)) {
            $status = 2;
            $this->assign('host', $_SERVER['HTTP_HOST']);
            $this->assign('areaid', $areaid);
            $this->assign('status', $status);
            return view('msg');
        } else {
            $stype = db('school')->field('schtype,schname')->where('schid', $schid)->find();
            //中小学
            if ($stype['schtype'] == 1 || $stype['schtype'] == 2) {
                //学生信息
                $slist = db('classes' . $schid)->field('name,sex,id,tgroupid,cno')->where('id', $stinfo['id'])->select()->toArray();

                $slist[0]['id_num'] = $id_num;
                $newcno = $slist[0]['tgroupid'] + 1;
                $fname = IntToChr($slist[0]['cno']-1) . ' 组';
                $cname = $newcno . ' 班';
            }
            //高中
            if ($stype['schtype'] == 3) {
                //学生信息
                $slist = db('classes' . $schid)->field('name,sex,id,cno,tgroupid,fcengid')->where('id', $stinfo['id'])->select()->toArray();
                $slist[0]['id_num'] = $id_num;
                $fname = '第 ' . ($slist[0]['fcengid'] + 1) . ' 层 ' . IntToChr($slist[0]['cno']-1) . ' 组';
                $newcno = $slist[0]['tgroupid'] + 1;
                $cname = '第 ' . ($slist[0]['fcengid'] + 1) . ' 层 ' . $newcno . ' 班';

            }
            //教师信息
            $tlist = db('classes' . $schid)->field('tgroupid')->where('id', $stinfo['id'])->find();
            $group = db('teachergroup')->where('status', 1)->where('astatus', 1)->field('id,cstatus')->where('schid', $schid)->find();

            $teahlist = db('tgaccess')->field('tid,isheader,tgid')->where('gid', $group['id'])->where('tgid', $tlist['tgroupid'])->where('isheader', 1)->select()->toArray();

            foreach ($teahlist as $key => $value) {
                $arr = db('teacher')->field('name,thumb')->where('id', $value['tid'])->find();
                $teahlist[$key]['name'] = $arr['name'];
                if ($arr['thumb'] == '') {
                    $teahlist[$key]['thumb'] = 'http://' . $_SERVER['HTTP_HOST'] . substr($PHP_SELF, 0, strrpos($PHP_SELF, '/') + 1) . '/public/groupphoto/nullteacher.jpg';
                } else {
                    $teahlist[$key]['thumb'] = 'http://' . $_SERVER['HTTP_HOST'] . substr($PHP_SELF, 0, strrpos($PHP_SELF, '/') + 1) . '/public/' . $arr['thumb'];
                }
                if ($value['isheader'] == 1) {
                    $teahlist[$key]['workname'] = '<span style=color:#1bbbac;padding-left:5px;>班主任</span>';
                }

                $teahlist[$key]['cname'] = $value['tgid'] + 1;
            }
            if (empty($teahlist)) {
                $rtype = 0;//如果班主任为空 就是分组结果
            } else {
                $rtype = 1;//如果班主任不为空 就是分班结果
            }
        }
        $this->assign('cstatus', $group['cstatus']);
        $this->assign('rtype', $rtype);
        $this->assign('teahlist', $teahlist);
        $this->assign('fname', $fname);
        $this->assign('cname', $cname);
        $this->assign('slist', $slist);
        $this->assign('sname', $stype['schname']);
        return view();
    }
}