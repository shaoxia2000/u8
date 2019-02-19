<?php
namespace app\admin\controller;

use think\Controller;

class Placement extends Common
{
	public function index()
	{
		//搜索条件
		$findxq = input('post.findxq');

		//获取学校ID
		$schoollist = db('school') ->field('id') -> select();

		foreach ($schoollist as $key => $value) {
			
			//通过学校ID+表名拼成数据表并查询表是否存在
			$tablename = "bk_students".$value['id'];
			$isTable = db()->query("SHOW TABLES LIKE '".$tablename."';");

			if($isTable)
			{
				//如果存在，将有任务的学校ID生成一个字符串
				$new .= $value['id'].",";
				$str = substr($new,0,strlen($new)-1); 
			}
		}
		//取出下拉菜单数据
		$newlist = db('school')->where('id','in',$str)->select();

		//是否有搜索条件
		if (empty($findxq)) 
		{

            $findxq = '';
            $data = db('school')->where('id','in',$str)->paginate(10);
        }else
        {
        	$data = db('school')->where('id',$findxq)->paginate(10);
        }
        $this ->assign('data',$data);
		$this ->assign('newlist',$newlist);
		$this ->assign('findxq', $findxq);
		return view();
	}
	//任务详情
	public function details($id)
	{
		//拼出数据表
		$tablename = "task".$id;
		$list = db($tablename) ->paginate(10);
		//把时间戳转化成时间格式
		foreach ($list as $key => $value) 
		{
			$rex[$key]['date'] = date( "Y-m-d H:i:s", $value['time'] );
			$rex[$key]['classnum'] = $value['classnum'];
		}
		//获取分班数
		foreach ($rex as $key => $value) {
			$arr = '';
			for ($x=1; $x<=$value['classnum']; $x++) {
				  $arr .= $x.",";
			} 
			$rex[$key]['str']=explode(",",substr($arr,0,strlen($arr)-1)); 
		}
		$this->assign('pid',$id);
		$this->assign('rex',$rex);
		$this->assign('list',$list);
		return view();
	}
	//分班结果
	public function classend($id,$pid,$rid)
	{
		//关键字搜索
		$find = input('post.find');
        if (!empty($find)) {
            $find = trim($find);
        } else {
            $find = '';
        }
		//$id为班级ID、$pid为数据表后缀数、$rid为任务ID
		$tablename = "students".$pid;

		//男同学
		$nanlist = db($tablename) ->alias('s') 
							   ->join('bk_classes'.$pid.' c','c.id = s.id')
							   ->where('c.taskid',$rid)
							   ->where('c.cno',$id)
							   ->where('s.sex',"男")
							   ->where('s.name', 'like', '%' . $find . '%')
							   ->select();
		//女同学
		$nvlist = db($tablename) ->alias('s') 
							   ->join('bk_classes'.$pid.' c','c.id = s.id')
							   ->where('c.taskid',$rid)
							   ->where('c.cno',$id)
							   ->where('s.sex',"女")
							   ->where('s.name', 'like', '%' . $find . '%')
							   ->select();
		$this->assign('nanlist',$nanlist);
		$this->assign('nvlist',$nvlist);
		$this->assign('find',$find);
		return view();
	}

}