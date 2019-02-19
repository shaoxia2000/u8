<?php

namespace app\mob\controller;
use think\Cache;
use think\Controller;
use think\session;
use think\Request;
class Screen extends Controller
{
    public function index($areaid)
    {
        $alist = db('area')->field('area_name')->where('area_id',$areaid)->find();

        $schlist = db('school')->field('id,schid,schname,schtype,areaid')->where($wheres)->where('areaid',$areaid)->order('schtype desc')->select()->toArray();
        
        $fristschid = $schlist[0]['schid'];

        foreach ($schlist as $key => $value) 
        {
            $table = 'students'.$value['schid'];
            $exist = db()->query('show tables like "bk_'.$table.'"');
            if($exist)
            {
                //参与分班的的总学生数
                $stunum = db($table)->where('xstatus',null)->count(); 
                $list['stunum'] += $stunum; 

                //参与分班的的总教师数
                $teachernum = db('teacher')->where('schid',$value['schid'])->where('status',0)->count();
                $list['teachernum'] += $teachernum;     

                //参与的签到家长数
                $parent = db('sign')->where('schid',$value['schid'])->count();
                $list['pnum'] += $parent;                   
                    
            }else
            {
                //存在数据库没表的学校
                unset($schlist[$key]);
            }

        }
        $url='http://'.$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF,'/')+1);

        if($areaid==1)
        {
            //大同区
            $img = $url.'/public/static/screen/images/datongmap.png';

        }
        if($areaid==2)
        {
            //杜蒙县
            $img = $url.'/public/static/screen/images/zhaoyuanmap.png';
        }
        if($areaid==3)
        {
            //红岗区
            $img = $url.'/public/static/screen/images/honggangmap.png';
        }
        if($areaid==4)
        {
            //林甸县
            $img = $url.'/public/static/screen/images/lundianmap.png';
        }
        if($areaid==5)
        {
            //龙凤区
            $img = $url.'/public/static/screen/images/longfenmap.png';
        }
        if($areaid==6)
        {
            //让胡路区
            $img = $url.'/public/static/screen/images/ranghulumap.png';
        }
        if($areaid==7)
        {
            //萨尔图区
            $img = $url.'/public/static/screen/images/saertumap.png';
        }
        if($areaid==8)
        {
            //肇州县
            $img = $url.'/public/static/screen/images/zhaozhoumap.png';
        }
        if($areaid==9)
        {
            //肇源县
            $img = $url.'/public/static/screen/images/zhaoyuanmap.png';
        }
        if($areaid==10)
        {
            //市直属
            $img = $url.'/public/static/screen/images/daq-map.png';
        }

        $stype = db('school')->field('schtype')->where('schid',$fristschid)->find();
        if($alist['area_name']=='杜尔伯特蒙古族自治县')
        {
            $alist['area_name']='杜蒙县';
        }
        $this->assign('fristschid',$fristschid);
        $this->assign('schtype',$stype['schtype']);
        $this->assign('stunum',$list['stunum']);
        $this->assign('teachernum',$list['teachernum']);
        $this->assign('pnum',$list['pnum']);
        $this->assign('areaname',$alist['area_name']);
        $this->assign('areaid',$areaid);
        $this->assign('img',$img);
        return view();
    }

    //左侧
    public function left()
    {
        $areaid = input('post.areaid');
    	//0市直属 1小学 2中学 3高中
    	$schtype = input('post.type');
    	if(empty($schtype))
    	{
    		$wheres = '';
    	}
        if($schtype!=0)
    	{
    		$wheres['schtype'] = $schtype;
    	}else
        {
            $wheres = '';
        }
    	//取直属区的学校ID
    	$schlist = db('school')->field('id,schid,schname,schtype,areaid')->where($wheres)->where('areaid',$areaid)->order('schtype desc')->select()->toArray();

    	foreach ($schlist as $key => $value) 
    	{
    		$table = 'students'.$value['schid'];
    		$exist = db()->query('show tables like "bk_'.$table.'"');
	        if($exist)
	        {
	        	//参与分班的的学生数
	            $stunum = db($table)->where('xstatus',null)->count(); 
    			$schlist[$key]['stunum'] = $stunum;					

    			//教师团队数、学生分组数
    			$tarry = db('teachergroup')->field('tnum')->where('schid',$value['schid'])->where('status',1)->where('astatus',1)->find();
    			if(empty($tarry))
    			{
    				$schlist[$key]['tnum'] = 0;
    				$schlist[$key]['snum'] = 0;
    			}else
    			{
    				$schlist[$key]['tnum'] = $tarry['tnum'];	
    				$schlist[$key]['snum'] = $tarry['tnum'];
    			}

    			//教师数
    			$teachernum = db('teacher')->where('schid',$value['schid'])->where('status',0)->count();
    			$schlist[$key]['teachernum'] = $teachernum;	

    				
	        }else
	        {
	        	//存在数据库没表的学校
	        	unset($schlist[$key]);
	        }

    	}
    	// $fristschid = $schlist[0]['schid'];
        return $schlist;
    }
    //中间
    // public  function center()
    // {
    //     $areaid = input('post.areaid');
    // 	$schlist = db('school')->field('id,schid,schname,schtype,areaid')->where($wheres)->where('areaid',$areaid)->select()->toArray();
    // 	foreach ($schlist as $key => $value) 
    // 	{
    // 		$table = 'students'.$value['schid'];
    // 		$exist = db()->query('show tables like "bk_'.$table.'"');
	   //      if($exist)
	   //      {
	   //      	//参与分班的的总学生数
	   //          $stunum = db($table)->where('xstatus',null)->count(); 
    // 			$list['stunum'] += $stunum;	

    // 			//参与分班的的总教师数
    // 			$teachernum = db('teacher')->where('schid',$value['schid'])->where('status',0)->count();
    // 			$list['teachernum'] += $teachernum;		

    // 			//参与的签到家长数
    // 			$parent = db('sign')->where('schid',$value['schid'])->count();
    // 			$list['pnum'] += $parent;					
    				
	   //      }else
	   //      {
	   //      	//存在数据库没表的学校
	   //      	unset($schlist[$key]);
	   //      }

    // 	}
    // 	return $list;
    // }
    //右侧
    public function right()
    {
    	$fristschid = input('post.fristschid');
    	$table = 'students'.$fristschid;
		$exist = db()->query('show tables like "bk_'.$table.'"');
     
        if($exist)
        {
        	//参与分班的的学生数
            $stunum = db($table)->where('xstatus',null)->count(); 
			
            //学校类型
            $stype = db('school')->field('schname,schtype')->where('schid',$fristschid)->find();
            if($stype['schtype']==1)
            {
            	$arr['chinese'] = 0;
            	$arr['smath'] = 0;
            	$arr['english'] = 0;
            }
            if($stype['schtype']==2)
            {
            	$zchinese = db($table)->where('xstatus',null)->sum('chinese'); 
            	$zsmath = db($table)->where('xstatus',null)->sum('smath'); 
            	$zenglish = db($table)->where('xstatus',null)->sum('english'); 

            	if($stunum!=0)
            	{
            		$arr['chinese'] = round($zchinese/$stunum,2);
	            	$arr['smath'] = round($zchinese/$stunum,2);
	            	$arr['english'] = round($zchinese/$stunum,2);
            	}else
            	{
            		$arr['chinese'] = 0;
	            	$arr['smath'] = 0;
	            	$arr['english'] = 0;
            	}
            	
            }
            if($stype['schtype']==3)
            {
            	$zchinese = db($table)->where('xstatus',null)->sum('chinese'); 
            	$zsmath = db($table)->where('xstatus',null)->sum('smath'); 
            	$zenglish = db($table)->where('xstatus',null)->sum('english'); 

            	$zphysics = db($table)->where('xstatus',null)->sum('physics');
            	$zchemistry = db($table)->where('xstatus',null)->sum('chemistry');
            	$zgeography = db($table)->where('xstatus',null)->sum('geography');
            	$zbiologic = db($table)->where('xstatus',null)->sum('biologic');
            	$zhistory = db($table)->where('xstatus',null)->sum('history');
            	$zpolitics = db($table)->where('xstatus',null)->sum('politics');
            	$zsports = db($table)->where('xstatus',null)->sum('sports');

            	if($stunum!=0)
            	{
	            	$arr['chinese'] = round($zchinese/$stunum,2);
	            	$arr['smath'] = round($zchinese/$stunum,2);
	            	$arr['english'] = round($zchinese/$stunum,2);
	            	$arr['physics'] = round($zphysics/$stunum,2);
	            	$arr['chemistry'] = round($zchemistry/$stunum,2);
	            	$arr['geography'] = round($zgeography/$stunum,2);
	            	$arr['biologic'] = round($zbiologic/$stunum,2);
	            	$arr['history'] = round($zhistory/$stunum,2);
	            	$arr['politics'] = round($zpolitics/$stunum,2);
	            	$arr['sports'] = round($zsports/$stunum,2);
	            }else
	            {
	            	$arr['chinese'] = 0;
	            	$arr['smath'] = 0;
	            	$arr['english'] = 0;
	            	$arr['physics'] = 0;
	            	$arr['chemistry'] = 0;
	            	$arr['geography'] = 0;
	            	$arr['biologic'] = 0;
	            	$arr['history'] = 0;
	            	$arr['politics'] = 0;
	            	$arr['sports'] = 0;
	            }
                
            }	
            $arr['sname'] = $stype['schname'];	
        }
        return $arr;
    }
    public function rightcenter()
    {
        $sid = input('post.fristschid');

        $table = 'classes'.$sid;
        $exist = db()->query('show tables like "bk_'.$table.'"');
     
        if($exist)
        {
            $slist = db('school')->field('schname,schtype')->where('schid',$sid)->find();

            if($slist['schtype']==1||$slist['schtype']==2)
            {
                $snum = db($table)->count();
                $brr['fcengid']=0;
                $brr['num']=$snum;
                $arr[0]=$brr;
            }else
            {
                $fceng = db($table)->field('fcengid')->group('fcengid')->select()->toArray();
                foreach ($fceng as $key => $value) {
                    $studentnum = db($table)->where('fcengid',$value['fcengid'])->count();
                    $fceng[$key]['num'] = $studentnum;
                }
                $arr=$fceng;
            }
            foreach ($arr as $key => $value) 
            {
                $num = $value['fcengid']+1;
                $newArr[$num."层"] = $value['num'];
            }   

        }
        $str = json_encode($newArr,JSON_UNESCAPED_UNICODE );
        return $str;

    }

    public function ajaxGetSchoolInfo(){
        $id = input('post.sid/d');
        $res = array();
        $res['jzcyrs'] = db('sign')->where(['schid'=>$id])->count();
        $res['school'] = db('school')->where(['schid'=>$id])->field('schtype,schname')->find();


        // $table = 'classes'.$id;
        // $tableExist = db()->query('show tables like "bk_'.$table.'"');

        // if(!$tableExist){
        //     return $res;
        // }
        $table = 'students'.$id;
        if ($res['school']['schtype'] == '3') 
        {
            $exist = db()->query('show tables like "bk_'.$table.'"');
            if(!$exist)
            {
                $arr['语文'] = 0;
                $arr['数学'] = 0;
                $arr['英语'] = 0;
                $arr['物理'] = 0;
                $arr['化学'] = 0;
                $arr['地理'] = 0;
                $arr['生物'] = 0;
                $arr['历史'] = 0;
                $arr['政治'] = 0;
                $arr['体育'] = 0;
            }else
            {
                //参与分班的的学生数
                $stunum = db($table)->where('xstatus',null)->count(); 
                // 高中
                $zchinese = db($table)->where('xstatus',null)->sum('chinese'); 
                $zsmath = db($table)->where('xstatus',null)->sum('smath'); 
                $zenglish = db($table)->where('xstatus',null)->sum('english'); 

                $zphysics = db($table)->where('xstatus',null)->sum('physics');
                $zchemistry = db($table)->where('xstatus',null)->sum('chemistry');
                $zgeography = db($table)->where('xstatus',null)->sum('geography');
                $zbiologic = db($table)->where('xstatus',null)->sum('biologic');
                $zhistory = db($table)->where('xstatus',null)->sum('history');
                $zpolitics = db($table)->where('xstatus',null)->sum('politics');
                $zsports = db($table)->where('xstatus',null)->sum('sports');

                if($stunum!=0)
                {
                    $arr['语文'] = round($zchinese/$stunum,2);
                    $arr['数学'] = round($zchinese/$stunum,2);
                    $arr['英语'] = round($zchinese/$stunum,2);
                    $arr['物理'] = round($zphysics/$stunum,2);
                    $arr['化学'] = round($zchemistry/$stunum,2);
                    $arr['地理'] = round($zgeography/$stunum,2);
                    $arr['生物'] = round($zbiologic/$stunum,2);
                    $arr['历史'] = round($zhistory/$stunum,2);
                    $arr['政治'] = round($zpolitics/$stunum,2);
                    $arr['体育'] = round($zsports/$stunum,2);
                }else
                {
                    $arr['语文'] = 0;
                    $arr['数学'] = 0;
                    $arr['英语'] = 0;
                    $arr['物理'] = 0;
                    $arr['化学'] = 0;
                    $arr['地理'] = 0;
                    $arr['生物'] = 0;
                    $arr['历史'] = 0;
                    $arr['政治'] = 0;
                    $arr['体育'] = 0;
                }
            }

        }else{
            $exist = db()->query('show tables like "bk_'.$table.'"');
            if(!$exist)
            {
                $arr['语文'] = 0;
                $arr['数学'] = 0;
                $arr['英语'] = 0;
            }else
            {
                if($res['school']['schtype'] == '1')
                {
                    //小学
                    $arr['语文'] = 0;
                    $arr['数学'] = 0;
                    $arr['英语'] = 0;
                }
                if($res['school']['schtype'] == '2')
                {
                    // 中学
                    $zchinese = db($table)->where('xstatus',null)->sum('chinese'); 
                    $zsmath = db($table)->where('xstatus',null)->sum('smath'); 
                    $zenglish = db($table)->where('xstatus',null)->sum('english'); 

                    if($stunum!=0)
                    {
                        $arr['语文'] = round($zchinese/$stunum,2);
                        $arr['数学'] = round($zchinese/$stunum,2);
                        $arr['英语'] = round($zchinese/$stunum,2);
                    }else
                    {
                        $arr['语文'] = 0;
                        $arr['数学'] = 0;
                        $arr['英语'] = 0;
                    }
                }
            }
            
            
        }
        $res['right_top'] = $arr;

        $table = 'classes'.$id;
        $exist = db()->query('show tables like "bk_'.$table.'"');
     
        if($exist)
        {
            if($res['school']['schtype'] != '3')
            {
                $brr['fcengid']=0;
                $brr['num']=db($table)->count();
                $arr[0]=$brr;
            }else
            {
                $fceng = db($table)->field('fcengid')->group('fcengid')->select()->toArray();
                foreach ($fceng as $key => $value) {
                    $studentnum = db($table)->where('fcengid',$value['fcengid'])->count();
                    $fceng[$key]['num'] = $studentnum;
                }
                $arr=$fceng;
            }
            foreach ($arr as $key => $value) 
            {
                $newArr[($value['fcengid'] + 1)."层"] = $value['num'];
            }   

            
        }else
        {
            $newArr[0]=0;
        }
        $res['fcdata'] = $newArr;

        //二次分班
        //1.复读生 2.后转入 3.补报生 4.未报到
        $brr['fstu'] = db('secondfb')->where('schid',$id)->where('fbtype',1)->count();
        $brr['hstu'] = db('secondfb')->where('schid',$id)->where('fbtype',2)->count();
        $brr['bstu'] = db('secondfb')->where('schid',$id)->where('fbtype',3)->count();
        $brr['wstu'] = db('secondfb')->where('schid',$id)->where('fbtype',4)->count();


        $res['secondfb'] = $brr;

        $stable = 'students'.$id;
        $exist = db()->query('show tables like "bk_'.$stable.'"');

        if($exist)
        {
            //男女学生比例以及双胞胎
            if ($res['school']['schtype'] == '3') 
            {
                $crr['nan'] = db('students'.$id)->where('sex','男')->where('xstatus',null)->count();
                $crr['nv'] = db('students'.$id)->where('sex','女')->where('xstatus',null)->count();
                $crr['twins'] = 0;
            }else
            {
                $crr['nan'] = db('students'.$id)->where('sex','男')->where('xstatus',null)->count();
                $crr['nv'] = db('students'.$id)->where('sex','女')->where('xstatus',null)->count();
                $ctable = 'classes'.$id;
                $cexist = db()->query('show tables like "bk_'.$ctable.'"');
                if($cexist)
                {
                    $crr['twins'] = db('classes'.$id)->where('sbt','not null')->count();
                }else
                {
                    $crr['twins'] = 0;
                }
                
            }
        }else
        {
            $crr['nan'] = 0;
            $crr['nv'] = 0;
            $crr['twins'] = 0;
        }
        

        $res['bili'] = $crr;


        $newlist = db('teachergroup')->field('status,astatus,xstatus,gstatus')->where('schid',$id)->find();


        //1.学生导入 2.教师团队 3.区学生分组 4.学校分班+结果公示
        if(empty($newlist))
        {
            //第一步学生导入
            $drr['status'] = 1;
        }else
        {
            if($newlist['status']==1&&$newlist['astatus']==1)
            {
                //教师团队完成
                if($newlist['gstatus']==1)
                {
                    if($newlist['xstatus']==1)
                    {
                        $drr['status'] = 4;
                    }else
                    {
                        $drr['status'] = 3;
                    }
                }else
                {
                    $drr['status'] = 2;
                }
            }else
            {
                $drr['status'] = 1;
            }
        }
        $res['status'] = $drr;
        return json($res);
    }

}