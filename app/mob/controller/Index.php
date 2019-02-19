<?php

namespace app\mob\controller;
use think\Cache;
use think\Controller;
use think\session;
use think\Request;
class Index extends Controller
{
    public function index($id)
    {
        $table = 'bk_classes'.$id;
        $exist = db()->query('show tables like "'.$table.'"');
        if(!$exist)
        {
            $this->assign('id',$id);
            return view('no');
        }

        $schoolname = db('school')->field('schname,schtype')->where('schid',$id)->find();

        if($schoolname['schtype']==1||$schoolname['schtype']==2)
        {
            $zulist = db('classes'.$id)->field('cno')->group('cno')->select()->toArray();
            foreach ($zulist as $key => $value) {
                $zulist[$key]['fz'] = '第 '.IntToChr($value['cno']-1)." 组";
            }
        }
        if($schoolname['schtype']==3)
        {
            $zulist = db('classes'.$id)->distinct(true)->field('cno,fcengid')->order('fcengid asc')->select()->toArray();
            foreach ($zulist as $key => $value) {
                $zulist[$key]['fz'] = '第 '.($value['fcengid']+1).' - '.IntToChr($value['cno']-1).' 组';
            }
        }
        $this->assign('schname',$schoolname['schname']);
        $this->assign('id',$id);
        $this->assign('schtype',$schoolname['schtype']);
        $this->assign('zulist',$zulist);
        return view();
    }
    //高中
    public function Gcheck($schid,$schtype,$cno,$fcengid)
    {
        $schoolname = db('school')->field('schname,schtype')->where('schid',$schid)->find();
        $this->assign('schname',$schoolname['schname']);
        $this->assign('schid',$schid);
        $this->assign('schtype',$schtype);
        $this->assign('cno',IntToChr($cno-1));
        $this->assign('fcengid',$fcengid);
        $this->assign('cnot',$cno);
        return view('check');
    }
    //中、小学
    public function Xcheck($schid,$schtype,$cno)
    {
        $schoolname = db('school')->field('schname,schtype')->where('schid',$schid)->find();
        $this->assign('schname',$schoolname['schname']);
        $this->assign('schid',$schid);
        $this->assign('schtype',$schtype);
        $this->assign('cno',IntToChr($cno-1));
        $this->assign('cnot',$cno);
        return view('check');
    }
    //签到验证
    public function checkform()
    {
        $sname = input('post.sname');//学生姓名
        $snum = input('post.snum');//学生身份证
        $pname = input('post.pname');//家长姓名
        $tel = input('post.tel');//家长电话号
        $schid = input('post.schid');//学校ID
        $schtype = input('post.schtype');//学校类型
        $cno = input('post.cno');//学校分组(英文)
        $cnot = input('post.cnot');//学校分组(数字)
        $fcengid = input('post.fcengid');//学校分层
        $studentlist = db('students'.$schid)->field('id')->where('id_num',$snum)->find();

        $gst = db('teachergroup')->field('gstatus')->where('schid',$schid)->find();
        if($gst['gstatus']!=1)
        {
            return 6;//分组没被确认
        }
        if(empty($studentlist))
        {
            return 3;//该学校无此学生
        }else
        {

            if($schtype==3)//是否为高中
            {
                $rt = db('classes'.$schid)->where('id',$studentlist['id'])->where('cno',$cnot)->where('fcengid',$fcengid)->find();
                if(empty($rt))
                {
                    return 4;//该学生不在该分组内
                }else
                {
                    $ini['sname'] = $sname;
                    $ini['snum'] = $snum;
                    $ini['pname'] = $pname;
                    $ini['studentid'] = $studentlist['id'];
                    $ini['schid'] = $schid;
                    $ini['cno'] = $cnot;
                    $ini['fceng'] = $fcengid;
                    $ini['tel'] = $tel;
                    

                    $rtt = db('sign')->where($ini)->find();
                    
                    if($rtt)
                    {
                        return 5;//重复签到
                    }else
                    {
                        $ini['time'] = date('Y-m-d H:i:s',time());
                        $conf = db('sign')->insert($ini);
                        if($conf)
                        {
                            return 1;//签到成功
                        }else
                        {
                            return 2;//签到失败
                        }
                    }
                }
            }else
            {

                $rt = db('classes'.$schid)->where('id',$studentlist['id'])->where('cno',$cnot)->find();
                if(empty($rt))
                {
                    return 4;//该学生不在该分组内
                }else
                {
                    $ini['sname'] = $sname;
                    $ini['snum'] = $snum;
                    $ini['pname'] = $pname;
                    $ini['studentid'] = $studentlist['id'];
                    $ini['schid'] = $schid;
                    $ini['cno'] = $cnot;
                    $ini['fceng'] = 'no';
                    $ini['tel'] = $tel;
                    

                    $rtt = db('sign')->where($ini)->find();

                    if($rtt)
                    {
                        return 5;//重复签到
                    }else
                    {
                        $ini['time'] = date('Y-m-d H:i:s',time());
                        $conf = db('sign')->insert($ini);
                        if($conf)
                        {
                            return 1;//签到成功
                        }else
                        {
                            return 2;//签到失败
                        }
                    }
                }
            }
            
        }
    }
    public function msg($msg,$schid,$schtype,$cno,$fcengid)
    {
        $schoolname = db('school')->field('schname')->where('schid',$schid)->find();
        $this->assign('schname',$schoolname['schname']);
        $this->assign('msg',$msg);
        $this->assign('schid',$schid);
        $this->assign('schtype',$schtype);
        $this->assign('cno',$cno);
        $this->assign('fcengid',$fcengid);
        return view();
    }
}