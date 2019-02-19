<?php
namespace app\check\controller;

use app\check\logic\CommonLogic;
class Index extends Common
{
    public function index()
    {
    	$areas = CommonLogic::getAreas();
    	$this->assign('areas', CommonLogic::getAreas());
    	$this->assign('data', CommonLogic::getCounts($areas));
    	return view();
    }

    public function ajaxGetAreaDetailsByAid(){
    	$aid = input('param.id/d');
    	return json(CommonLogic::getAreaDetails($aid));
    }
  
  	function checkIdNum(){
    	  $areas = db('area')->where(1)->column('area_id','id');
        $tabPre = config('database.prefix').'students';
        $change = [];
        foreach($areas as $aid){
            $sids = db('school')->where(['areaid'=>$aid,'schtype'=>2])->column('schid','schid');
            echo $aid.'-----------------';
          echo '<br>';
            foreach($sids as $sid){
                $table = $tabPre.$sid;
                if($this->tableExits($table)){
                   $has = db()->table($table)->where(['id_num'=>'230623200701161031'])->find();
                  echo db()->getLastSql();
                  	var_dump($has);
                  echo '<br>';
                }
            }
        }
    }
  
 	// 更新平均分
    function testb(){
      exit();
        $areas = db('area')->where(1)->column('area_id','id');
        
        $tabPre = config('database.prefix').'students';
        $change = [];
        foreach($areas as $aid){
            $sids = db('school')->where(['areaid'=>$aid,'schtype'=>2])->column('schid','schid');
            
            foreach($sids as $sid){
                $table = $tabPre.$sid;
                if($this->tableExits($table)){
                    $sql = "select id from ".$table ." where (`chinese` = '0' and `smath` = '0' and `english` = '0')  ";
                    $temp = db()->query($sql);
                    if(!empty($temp)){
                        $sql = "select AVG(chinese) chinese,AVG(smath) smath,AVG(english) english from ".$table."  where (`chinese` != '0' and `smath` != '0' and `english`!= '0') and  (`chinese` is not null and `smath` is not null  and `english` is not null )";
                        $avg = db()->query($sql);
                      
                      	foreach($temp as $v){
                      		$usql = 'update '.$table .' set chinese = "'.round($avg[0]['chinese'],1).'",smath = "'.round($avg[0]['smath'],1).'",english = "'.round($avg[0]['english'],1).'",zcj = "'.(round($avg[0]['english'],1)+round($avg[0]['smath'],1)+round($avg[0]['chinese'],1)).'" where id = '.$v['id'];
                          	// db()->query($usql);
                            $upSql[] = $usql;
                        }
                    }
                }
            }
        }
      	echo '<Pre>';
      	print_r($upSql);
    }

  
    private function tableExits($tableName)
    {
        $sql = 'SHOW TABLES LIKE \'' . $tableName . '\'';
        $has = db()->query($sql);
        return !empty($has);
    }
  
  	public function sbt(){
        $areas = db('area')->where(1)->column('area_name','area_id');
        
        $tabPre = config('database.prefix').'students';
        $change = [];
        foreach($areas as $aid => $area_name){
            echo '区名:'.$area_name.'  '.$aid;
            echo '<br>';
            $sids = db('school')->where(['areaid'=>$aid,'schtype'=>['in',[1,2]]])->column('schname','schid');
            foreach($sids as $sid => $schname){
                $table = $tabPre.$sid;
                if($this->tableExits($table)){
                    $sql = "select  from ".$table . ' where ';
                    $data = db()->table($table)->where('sbt', 'gt', '0')->column('sbt,id,name','id');
                    if(!empty($data)){
                        echo '学校名称:'.$schname;
                        echo '<br>';
                        echo '学校标识:'.$sid;
                        echo '<br>';
                        $ids = array_column($data, 'sbt');
                        $psbt = db()->table($table)->where('id','in', $ids)->column('sbt,id,name','id');
						
                        $bol = true;
                        foreach($psbt as $v){
                            if($v['sbt'] > 0){
                                $bol = false;
                                echo '双胞胎设置有误<br>';
                            }
                        }

                        if($bol){
                            $arr = array();
                            foreach($psbt as $v){
                                $arr[$v['id']][$v['id']] = $v['id'];
                                foreach($data as $dv){
                                    if($dv['sbt'] == $v['id']){
                                        $arr[$v['id']][] = $dv['id'];
                                    }
                                }
                            }
                        }
						
                      	echo '双胞胎关系<pre>';
                      	print_r($arr);
                      
                    }
                }
            }

            echo '--------------';
            echo '<br>';
            echo '<br>';
            echo '<br>';
        }
    }
  
      public function getSchid(){
        $areas = db('area')->where('area_id', input('aid'))->column('area_name','area_id');
        $sids = db('school')->where(['areaid'=>input('aid/d',0),'schtype'=>['in',[1,2]]])->column('schid');
        echo implode(',', $sids);
    }
  
	public function sbtSameCno(){
      echo <<<html
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<title>Document</title>
</head>
<body>
html;
        $aid = input('param.aid/d',1);
        if($aid){
            $where = ['area_id'=>$aid];
        }else{
            $where = 1;
        }
        $areas = db('area')->where($where)->column('area_name','area_id');
        
        $tabPre = config('database.prefix').'students';
        $classPres = config('database.prefix').'classes';
        $change = [];
        foreach($areas as $aid => $area_name){
            echo '区名:'.$area_name;
            echo '<br>';
            $schools = db('school')->where(['areaid'=>$aid,'schtype'=>['in',[1,2]]])->column('schname,schtype,schid','schid');
            foreach($schools as $school){
                $sid = $school['schid'];
                $table = $tabPre.$sid;
                if($this->tableExits($table)){
                    $sql = "select  from ".$table . ' where ';
                    $data = db()->table($table)->where('sbt', 'gt', '0')->column('sbt,id,name','id');
                    if(!empty($data)){
                        echo '***************************************<br>';
                        echo '学校名称:'.$school['schname'];
                        echo '<br>';
                        echo '学校标识:'.$sid;
                        echo '<br>';
                        $ids = array_column($data, 'sbt');
                        $psbt = db()->table($table)->where('id','in', $ids)->column('sbt,id,name','id');
                        
                        $bol = true;
                        foreach($psbt as $v){
                            if($v['sbt'] > 0){
                                $bol = false;
                                echo '<span style="color: red;">双胞胎设置有误</span><br>';
                            }
                        }

                        if($bol){
                            $arr = array();
                            foreach($psbt as $v){
                                $arr[$v['id']][$v['id']] = $v['id'];
                                foreach($data as $dv){
                                    if($dv['sbt'] == $v['id']){
                                        $arr[$v['id']][] = $dv['id'];
                                    }
                                }
                            }
                        }

                        if(empty($arr)) continue;

                        $classTable = $classPres.$sid;
                        if(!$this->tableExits($classTable)){
                            echo  '<span style="color: red;">没有classes表</span><br>';
                            continue;
                        }else{
                            foreach($arr as $v){
                                $cnos = db()->table($classTable)->where('id','in', $v)->column('cno','id');
                                if( count( array_unique($cnos) ) >1 ){
                                    echo  '<span  style="color: red;">双胞胎不在一班</span><br>';
                                    echo  'ID集合'.implode(",", $v);
                                    echo '<br>';
                                    echo '分班情况:'.json_encode($cnos);
                                    echo '<br>';
                                }
                            }
                        }

                        $sql = 'SELECT sex, count(sex) as nsex, cno FROM `'.$classTable.'` WHERE 1 group by cno,sex';

                        $data = db()->query($sql);

                        echo '男女均衡情况';
						
                      
                        echo "<pre>";
                        print_r($data);
                        echo "</pre>";

                        echo '<br>';

                        if($school['schtype'] == '2'){
                            $sql = 'select cno, avg(zcj) as av  from `'.$classTable.'` group by cno';

                            $data = db()->query($sql);

                            echo '成绩均衡情况';

                            echo '<pre>';
                            print_r($data);
                        	echo "</pre>";

                            $avs = array_column($data, 'av');

                            echo '<br>差值:'.(max($avs)-min($avs));

                        }
                      
						$stuCount = db()->table($table)->count();
                        $clCount = db()->table($classTable)->count();

                        if($stuCount != $clCount){
                          	
                            echo '<br>';
                            echo '<span  style="color: red;">分班后总人数结果不相同</span>';
                            echo '<br>';
                          	echo 'studente:'.$stuCount.'    ;classes'.$clCount;
                            echo '<br>';
                        }

                        echo '*************************************';
                        echo '<br>';
                        echo '<br>';
                        echo '<br>';
                        echo '<br>';
                    }
                }
            }

            echo '--------------';
            echo '<br>';
            echo '<br>';
            echo '<br>';
        }
    }
}
