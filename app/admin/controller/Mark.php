<?php
namespace app\admin\controller;
use think\Controller;
use think\session;
header("content-type:text/html;charset=utf-8");
class Mark extends Common
{
	public function index()
	{
		//学校下来菜单
		$schoolid = session('schoolid');
		//判断登陆人是否为教育局用户
		if($schoolid ==0)
		{
			$newlist = db('school')->where('status',0)->select();
		}else
		{
			$newlist = db('school')->where('status',0)->where('id',$schoolid)->select();
		}
		//有无 post
		if (request()->isGET()) 
		{
            $findxq = input('get.findxq');  // 学校
            $taskid = input('get.task');	// 任务
            $find   = input('get.find');	// 关键字
            //判断学校是否为空
            if (!empty($findxq)) 
            {
            	//判断任务搜索是否为空
            	if(!empty($taskid))
            	{
            		$wheres['taskid']=$taskid;
            	}
            	//判断姓名搜索是否为空
            	if(!empty($find))
            	{
            		$wheres['name']=['like','%'.$find.'%'];
            	}
                //任务下拉菜单
	            $tname = 'students'.$findxq;
				$list = db($tname)->alias('s')
								  ->distinct(true)	
								  ->field('s.taskid,t.title')
								  ->join('task'.$findxq.' t','s.taskid = t.id')
								  ->select();
				//数据分页
				$datalist = db($tname)->alias('s')
									  ->field('s.*,t.title')
									  ->join('task'.$findxq.' t','s.taskid = t.id')
									  ->where($wheres)
									  ->paginate(10,false,[
                        // 'type'     => 'Bootstrap',
                        // 'var_page' => 'page',
                        //  'query' => ['task'=>$taskid,'findxq'=>$findxq],
                        //第二种方法，使用函数助手传入参数
                       'query' => request()->param(),
                     ]);	
            }
            $this->assign('datalist',$datalist);
			$this->assign('list',$list);
			$this->assign('taskid',$taskid);
			$this->assign('findxq', $findxq);
			$this->assign('find', $find);
        }
		$this->assign('newlist',$newlist);
        return view();
	}
	public function choose()
	{
		$id = input('id');
		$tablename = "bk_students".$id;  //表名
		$isTable = db()->query("SHOW TABLES LIKE '".$tablename."';");
		//判断是否存在此表
		if($isTable)
		{
			$tname = 'students'.$id;
			$list = db($tname)->distinct(true)->field('taskid')->select();
			foreach ($list as $key => $value) {
				$taskname = db('task'.$id)->field('title')->where('id',$value['taskid'])->find();
				$txt[$value['taskid']] = $taskname['title'];
			}
			$end['txt'] = $txt;
			$end['exist'] = '1';
		}else
		{
			$end['txt'] = '无分班任务';
			$end['exist'] = '2';
		}
		return $end;
	}
}