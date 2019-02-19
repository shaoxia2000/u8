<?php

namespace app\mob\controller;
use think\Cache;
use think\Controller;
use think\session;
use think\Request;
class School extends Controller
{
    public function index($areaid)
    {
    	if(input('post.schtype')!='')
    	{
    		$wheres['schtype']=input('post.schtype');
    	}else
    	{
    		$wheres='';
    	}
    	if(input('post.schname')!='')
    	{
    		$schname=input('post.schname');
    		$this->assign('schname',$schname);
    	}else
    	{
    		$schname='';
    	}
    	$areaname = db('area')->field('area_name')->where('area_id',$areaid)->find();
        $schoollist = db('school')->where('areaid',$areaid)->where($wheres)->where('schname','like','%'.$schname.'%')->select()->toArray();
        foreach ($schoollist as $key => $value) {
        	$group = db('teachergroup')->field('tnum,update_time')->where('schid',$value['schid'])->where('status',1)->where('astatus',1)->find();
        	if(!empty($group))
        	{
        		$schoollist[$key]['tnum'] = $group['tnum'];
        		$schoollist[$key]['time'] = date('Y-m-d H:i:s', $group['update_time']);
        	}else
        	{
        		$schoollist[$key]['tnum'] = '';
        		$schoollist[$key]['time'] = '';
        	}
        	
        }
        $this->assign('schoollist',$schoollist);
        $this->assign('areaname',$areaname['area_name']);
        return view();
    }
    public function chooes($schid)
    {
    	$sch = db('school')->field('schname,schtype')->where('schid',$schid)->find();
        if($sch['schtype']==1||$sch['schtype']==2)
        {
            $zulist = db('classes'.$schid)->field('cno')->group('cno')->select()->toArray();
            
            foreach ($zulist as $key => $value) {
                $zulist[$key]['fz'] = $value['cno']." 班";
                $nannum = db('classes'.$schid)->where('cno',$value['cno'])->where('sex','男')->count();
                $nvnum = db('classes'.$schid)->where('cno',$value['cno'])->where('sex','女')->count();

                $zulist[$key]['nan'] = $nannum;
                $zulist[$key]['nv'] = $nvnum;
                $zulist[$key]['cno'] = $value['cno'];
                $zulist[$key]['zong'] = $nannum+$nvnum;
            }
        }
        if($sch['schtype']==3)
        {
            $zulist = db('classes'.$schid)->distinct(true)->field('cno,fceng')->order('fceng asc')->select()->toArray();
            $iffc = db('teachergroup')->field('fcengnum')->where('status',1)->where('astatus',1)->where('schid',$schid)->find();
            foreach ($zulist as $key => $value) {
                 if($iffc['fcengnum']==0)
                {
                    $zulist[$key]['fz'] = $value['cno'].' 班';
                    $zulist[$key]['fceng'] = 'fceng';
                    $nannum = db('classes'.$schid)->where('cno',$value['cno'])->where('sex','男')->count();
                    $nvnum = db('classes'.$schid)->where('cno',$value['cno'])->where('sex','女')->count();
                }else
                {
                    $zulist[$key]['fz'] = ($value['fceng']+1).' 层 '.$value['cno'].' 班';
                    $zulist[$key]['fceng'] = $value['fceng'];
                    $nannum = db('classes'.$schid)->where('cno',$value['cno'])->where('fceng',$value['fceng'])->where('sex','男')->count();
                    $nvnum = db('classes'.$schid)->where('cno',$value['cno'])->where('fceng',$value['fceng'])->where('sex','女')->count();
                }
                
               
                $zulist[$key]['nan'] = $nannum;
                $zulist[$key]['nv'] = $nvnum;
                $zulist[$key]['cno'] = $value['cno'];
                $zulist[$key]['zong'] = $nannum+$nvnum;
            }
        }
        $this->assign('zulist',$zulist);
        $this->assign('schtype',$sch['schtype']);
        $this->assign('sname',$sch['schname']);
        $this->assign('schid',$schid);
    	return view();
    }
    public function sdetails($schid,$cno,$fceng)
    {
        $sch = db('school')->field('schname')->where('schid',$schid)->find();
        if($fceng=='fceng')
        {
            //中小学
            $zulist = db('classes'.$schid)->field('name,sex,id')->where('cno',$cno)->select()->toArray();
            $fname =  $cno." 班";

        }else
        {
            $zulist = db('classes'.$schid)->field('name,sex,id')->where('cno',$cno)->where('fceng',$fceng)->select()->toArray();
            $fname = '第 '.($fceng+1).' 层 '.$cno.' 班';
        }

        //学生信息
        foreach ($zulist as $key => $value) {
            $num = db('students'.$schid)->field('id_num')->where('id',$value['id'])->find();
            $idcard = $num['id_num'];
            $idcard = strlen($idcard)==15?substr_replace($idcard,"****",8,4):(strlen($idcard)==18?substr_replace($idcard,"****",10,4):$idcard);
            $zulist[$key]['idnum']=$idcard;
        }
         //教师
        if($fceng=='fceng')
        {
             $tlist = db('classes'.$schid)->field('tgroupid')->where('cno',$cno)->find();

            $group = db('teachergroup')->where('status',1)->where('astatus',1)->field('id')->where('schid',$schid)->find();

            $teahlist =  db('tgaccess')->field('tid,isheader')->where('gid',$group['id'])->where('tgid',$tlist['tgroupid'])->select()->toArray();
            foreach ($teahlist as $key => $value) {
                $arr = db('teacher')->field('name,thumb')->where('id',$value['tid'])->find();
                $teahlist[$key]['name'] = $arr['name'];
                if($arr['thumb']=='')
                {
                    $teahlist[$key]['thumb'] = 'http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF,'/')+1).'/public/groupphoto/nullteacher.jpg';
                }else
                {
                    $teahlist[$key]['thumb'] = 'http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF,'/')+1).'/public/'.$arr['thumb'];
                }
                

                if($value['isheader']==1)
                {
                    $teahlist[$key]['workname'] = '<span style=color:#1bbbac;padding-left:5px;>班主任</span>';
                }else
                {
                    $teahlist[$key]['workname'] = '<span style=padding-left:5px;>科任</span>';
                }
            }
        }else
        {
            $tlist = db('classes'.$schid)->field('tgroupid')->where('cno',$cno)->where('fceng',$fceng)->find();

            $group = db('teachergroup')->where('status',1)->where('astatus',1)->field('id')->where('schid',$schid)->find();

            $teahlist =  db('tgaccess')->field('tid,isheader')->where('gid',$group['id'])->where('tgid',$tlist['tgroupid'])->select()->toArray();
            foreach ($teahlist as $key => $value) {
            $arr = db('teacher')->field('name,thumb')->where('id',$value['tid'])->find();
            $teahlist[$key]['name'] = $arr['name'];
            if($arr['thumb']=='')
            {
                $teahlist[$key]['thumb'] = 'http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF,'/')+1).'/public/groupphoto/nullteacher.jpg';
            }else
            {
                $teahlist[$key]['thumb'] = 'http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF,'/')+1).'/public/'.$arr['thumb'];
            }
            

            if($value['isheader']==1)
            {
                $teahlist[$key]['workname'] = '<span style=color:#1bbbac;padding-left:5px;>班主任</span>';
            }else
            {
                $teahlist[$key]['workname'] = '<span style=padding-left:5px;>科任</span>';
            }
        }
        }
       
        $this->assign('teahlist',$teahlist);
        $this->assign('fname',$fname);
        $this->assign('sname',$sch['schname']);
        $this->assign('zulist',$zulist);
        return view();
    }
}