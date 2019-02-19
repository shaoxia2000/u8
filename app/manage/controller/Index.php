<?php

namespace app\manage\controller;

use think\Session;

class Index extends Common
{
	/**
	 * 后台管理首页
	 */
	public function index()
	{
        if (Session::get('area_id') == "") {
            $this->redirect('http://yg.dqedu.net');
        }
//		Session::set('area_id', 10);
//		Session::set('school_id', 0);
		//337 338 340
		return view();
	}
	
	public function sign()
	{
		$this->loadSync();
		$user_id = input('post.user_id');
		$user = db('admin')->where(['user_id' => $user_id])->find();
		session('area_id', $user['area_id']);
		session('school_id', $user['school_id']);
		$this->redirect('index');
	}
	public function insertFees()
	{
      	exit();
		$sids = db('school')->where(['schtype'=>2])->column('schid,schname');
		$num = [];
		foreach($sids as $v => $val){
			$tableName = 'students'.$v;
			$arr = [];
			$has = db()->query('SHOW TABLES LIKE \'' . config('database.prefix') . $tableName . '\'');
			if(!empty($has)){
				$isNull = db()->field('chinese')->name($tableName)->find();
				if(is_null($isNull)){
					$arr['sql'][] = 'update '. config('database.prefix') . $tableName.' set chinese =  FLOOR(1 + RAND() * (100)), smath = FLOOR(1 + RAND() * (100)), `english` = FLOOR(1 + RAND() * (100)) where 1';
					$arr['exe'][] =db()->query($arr['sql'][0]);
					$arr['sql'][] = 'update '. config('database.prefix') . $tableName.' set zcj = (chinese + smath + english) where 1;';
					$arr['exe'][] = db()->query($arr['sql'][1]);
				}
			}
			$arr['id'] = $v;
			$arr['name'] = $val;
			$num[] = $arr;
		}
		foreach($num as $s){
			echo '学校id：'.$s['id'] . '---学校名：'.$s['name'].'--- sql：'.implode(';',$s['sql']); //. '---结果：'.implode($s['exe']);
			echo '<br>';
		}
	}
}
