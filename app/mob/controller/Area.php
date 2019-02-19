<?php

namespace app\mob\controller;
use think\Cache;
use think\Controller;
use think\session;
use think\Request;
class Area extends Controller
{
    public function status()
    {

        $request = Request::instance();
        $areaid = $request->param('areaid');
        $subscribe = $request->param('subscribe');
        $type = $request->param('type');

        $referer = parse_url($_SERVER['HTTP_REFERER']);

        if($subscribe === '0' || $subscribe !== '1'){
                $this->assign('host',$_SERVER['HTTP_HOST']);
                $this->assign('areaid',$areaid);
                return view('erro');
        }else{
            if($referer['host'] != $_SERVER['SERVER_NAME'])
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
                    $group = db('teachergroup')->field('tnum,update_time')->where('status',1)->where('astatus',1)->where('schid',$value['schid'])->find();
                    if(!empty($group))
                    {
                        $schoollist[$key]['tnum'] = $group['tnum'];
                        $schoollist[$key]['time'] = date('Y-m-d', $group['update_time']);
                    }else
                    {
                        $schoollist[$key]['tnum'] = '';
                        $schoollist[$key]['time'] = '';
                    }
                    
                }
                $this->assign('host',$_SERVER['HTTP_HOST']);
                $this->assign('schoollist',$schoollist);
                $this->assign('areaid',$areaid);
                $this->assign('areaname',$areaname['area_name']);
                return view('index');
               
            }
        }

    }
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
        	$group = db('teachergroup')->field('tnum,update_time')->where('status',1)->where('astatus',1)->where('schid',$value['schid'])->find();
        	if(!empty($group))
        	{
        		$schoollist[$key]['tnum'] = $group['tnum'];
        		$schoollist[$key]['time'] = date('Y-m-d', $group['update_time']);
        	}else
        	{
        		$schoollist[$key]['tnum'] = '';
        		$schoollist[$key]['time'] = '';
        	}
        	
        }
        $this->assign('host',$_SERVER['HTTP_HOST']);
        $this->assign('schoollist',$schoollist);
        $this->assign('areaid',$areaid);
        $this->assign('areaname',$areaname['area_name']);
        return view();
    }
    public function chooes($schid)
    {
    	$sch = db('school')->field('schname,schtype')->where('schid',$schid)->find();
    	// if($sch['schtype']==1||$sch['schtype']==2)
     //    {
     //        $zulist = db('classes'.$schid)->field('cno')->group('cno')->select()->toArray();
          	
     //        foreach ($zulist as $key => $value) {
     //            $zulist[$key]['fz'] = '第 '.$value['cno']." 组";
     //            $nannum = db('classes'.$schid)->where('cno',$value['cno'])->where('sex','男')->count();
     //            $nvnum = db('classes'.$schid)->where('cno',$value['cno'])->where('sex','女')->count();

     //            $zulist[$key]['nan'] = $nannum;
     //            $zulist[$key]['nv'] = $nvnum;
     //            $zulist[$key]['cno'] = $value['cno'];
     //            $zulist[$key]['zong'] = $nannum+$nvnum;
     //        }
     //    }
     //    if($sch['schtype']==3)
     //    {
     //        $zulist = db('classes'.$schid)->distinct(true)->field('cno,fcengid')->order('fcengid asc')->select()->toArray();
     //        foreach ($zulist as $key => $value) {
     //            $zulist[$key]['fz'] = '第 '.($value['fcengid']+1).' - '.$value['cno'].' 组';
     //            $nannum = db('classes'.$schid)->where('cno',$value['cno'])->where('fceng',$value['fceng'])->where('sex','男')->count();
     //            $nvnum = db('classes'.$schid)->where('cno',$value['cno'])->where('fceng',$value['fceng'])->where('sex','女')->count();
     //            $zulist[$key]['nan'] = $nannum;
     //            $zulist[$key]['nv'] = $nvnum;
     //            $zulist[$key]['cno'] = $value['cno'];
     //            $zulist[$key]['fceng'] = $value['fceng'];
     //            $zulist[$key]['zong'] = $nannum+$nvnum;
     //        }
     //    }


        $tlist = db('teachergroup')->field('id,tnum')->where(['schid'=>$schid,'status'=>1,'astatus'=>1])->find();

        for ($i=0; $i <$tlist['tnum'] ; $i++) { 
        	$teacharr[$i][$i]=$i;
        	$teacharr[$i]['gid']=$tlist['id'];
        }
        
        foreach ($teacharr as $key => $value) {
        	$teacharr[$key]['fz']=$value[$key]+1;
        }
        // $this->assign('zulist',$zulist);
        $this->assign('host',$_SERVER['HTTP_HOST']);
        $this->assign('teacharr',$teacharr);
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
    		$zulist = db('classes'.$schid)->field('name,sex,id')->where('cno',$cno)->select()->toArray();
    		$fname =  '第 '.$cno." 组";
    	}else
    	{
    		$zulist = db('classes'.$schid)->field('name,sex,id')->where('cno',$cno)->where('fceng',$fceng)->select()->toArray();
    		$fname = '第 '.($fceng+1).' - '.$cno.' 组';
    	}
    	
    	foreach ($zulist as $key => $value) {
    		$num = db('students'.$schid)->field('id_num')->where('id',$value['id'])->find();
    		$idcard = $num['id_num'];
    		$idcard = strlen($idcard)==15?substr_replace($idcard,"****",8,4):(strlen($idcard)==18?substr_replace($idcard,"****",10,4):$idcard);
    		$zulist[$key]['idnum']=$idcard;
    	}
    	$this->assign('fname',$fname);
    	$this->assign('sname',$sch['schname']);
    	$this->assign('zulist',$zulist);
    	return view();
    }
    public function tdetails($schid,$gid,$tgname)
    {	
    	 $sch = db('school')->field('schname')->where('schid',$schid)->find();
    	 $teacherlist = db('tgaccess')->field('tid,isheader')->where('gid',$gid)->where('tgid',$tgname)->select()->toArray();

    	 foreach ($teacherlist as $key => $value) {
            if($value['isheader']==1)
            {
                $teacherlist[$key]['work'] = '班主任';
            }else
            {
                $teacherlist[$key]['work'] = '科任';
            }
    	 	$info = db('teacher')->field('name,thumb,sex,xueli,xueke')->where('id',$value['tid'])->find();

    	 	$teacherlist[$key]['name'] = $info['name'];

            if($info['sex']==1)
            {
                $teacherlist[$key]['sex'] = '男';
            }
            if($info['sex']==2)
            {
                $teacherlist[$key]['sex'] = '女';
            }
            
            $teacherlist[$key]['xueli'] = $info['xueli'];
            $teacherlist[$key]['xueke'] = $info['xueke'];
            if($info['thumb']=='')
            {
                $teacherlist[$key]['thumb'] = 'http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF,'/')+1).'/public/groupphoto/nullteacher.jpg';
            }else
            {
                $teacherlist[$key]['thumb'] = 'http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF,'/')+1).'/public/'.$info['thumb'];
            }
    	 	
    	 }
         $this->assign('host',$_SERVER['HTTP_HOST']);
    	 $this->assign('teacherlist',$teacherlist);
    	 $this->assign('sname',$sch['schname']);
    	 $this->assign('tgname',$tgname);
    	 return view();

    }
}