<?php

namespace app\manage\controller;

use app\common\model\Secondfb as SecondfbModel;
use app\common\model\School as SchoolModel;
use app\common\model\Teachergroup as TeachergroupModel;
use think\Image;
use think\Session;

class Secondfb extends Common
{
	
	public function index()
	{
		$name = input('name');
		$name && $map['name'] = ['like', '%' . $name . '%'];
		$map['id'] = ['gt', 0];
		
		
		Session::get('school_id') != 0 && $map['schid'] = ['eq', Session::get('school_id')];
		Session::get('school_id') == 0 && $map['status'] = ['in', array(1, 2, 3)];
		if (Session::get('school_id') == 0) {
			$datas = SchoolModel::field('schid')->where('areaid', Session::get('area_id'))->select()->toArray();
			foreach ($datas as $k => $v) {
				$m[] = $v['schid'];
			}
			Session::get('school_id') == 0 && $map['schid'] = ['in', $m];
		}
		
		$query = ['query' => request()->param()];
		$data = SecondfbModel::where($map)->order('id desc')->paginate(30, $query);
		$this->assign(array('data' => $data));
		return view();
	}
	
	public function randfp()
	{
		$map['status'] = ['eq', 2];
		$map['classid'] = ['eq', 0];
		$map['schid'] = ['eq', Session::get('school_id')];
		$data = SecondfbModel::where($map)->column('*','id');
		if (!empty($data)) {
			$mapn['status'] = ['eq', 1];
			$mapn['astatus'] = ['eq', 1];
			$mapn['schid'] = ['eq', Session::get('school_id')];
			$classnum = TeachergroupModel::where($mapn)->value('tnum');
			$fcengnum = TeachergroupModel::where($mapn)->value('fcengnum');
			$gid = TeachergroupModel::where($mapn)->value('id');
			// $this->sortfb($data, $classnum);
			if ($fcengnum == 0) {
				$this->randclass(Session::get('school_id'), $classnum, $data);
				if(!empty($data)){
					$i = 1;
					foreach($data as $id => $v){
						
						model('Secondfb')->where(['id'=>$id])->update(['classid'=>$i, 'fbstatus' => 1]);
						$i++;
						if($i > $classnum) $i = 1;
					}
				}
			} else {
				for ($i = 0; $i < $fcengnum; $i++) {
					$fclassnum = Getfcmincno($gid, $i);
					$this->randclassfc(Session::get('school_id'), $fclassnum, $i);
				}
			}
			
			
		}
		return view();
	}
	
	
	/**
	 * 二次S型分班算法-蛇形分班排列
	 * @param $data
	 * @return array
	 */
	public function sortfb($data)
	{
		debugajax($data->toArray());
		foreach ($data as $k => $v) {
			$mdata = ['classid' => 1];
			$mwhere = ['id' => $v['id']];
			$field = ['classid'];
			SecondfbModel::update($mdata, $mwhere, $field);
		}
	}
	
	
	/**
	 * 二次分班均衡班级人数方法
	 */
	public function randclass($schid, $classnum ,& $data)
	{
//
		$datacount = db('classes' . $schid)->field('count(1) as ccno, cno,cid')->group('cno')->select();
		
		$Secondfb = model('Secondfb');
		$nowdata = $Secondfb->field('count(1) as ccno, classid')->where(['schid'=>$schid])->where('classid','gt','0')->where('fbstatus','eq',1)->group('classid')->select();
		$arr1 = [];
		if(!$nowdata->isEmpty()){
			$nowdata = $nowdata->toArray();
			foreach($nowdata as $v){
				$arr1[$v['classid']] = $v['ccno'];
			}
		}
		
		if($datacount->isEmpty()) return;
		$datacount = $datacount->toArray();
		$arr = array();
		
		foreach($datacount as $v){
			$arr[$v['cno']] = $v['ccno'] + $arr1[$v['cno']];
		}
		
		$calc = max($arr) - min($arr);
		
		if($calc <= 0 ) return '人数已经均衡';
		$max = max($arr);
		$keys = array_keys($data);
		$k = 0;
		
		
//		for($ic = 1;$ic<=$calc;$ic++){
			foreach($arr as $cno => $v){
				
				if($v != $max){
					$id = $keys[$k];
					unset($data[$keys[$k]]);
					$Secondfb->where(['id'=>$id])->where(['fbstatus'=>0])->update(['classid' => $cno, 'fbstatus' => 1]);
					
					if(empty($data)){
						return;
					}
					$k++;
					continue;
				}
			}
//		}
		return;
		
		$max = array_search(max($ar), $ar);
		$min = array_search(min($ar), $ar);
		$rem = $ar[$max] - $ar[$min];
		$newmax = $max + 1;
		$newmin = $min + 1;
		if ($rem > 1) {
			$Secondfb = model('Secondfb');
			
			$id = $Secondfb->where('schid', $schid)->where('classid', $newmax)->where('fbstatus', 'eq', 0)->limit(1)->order('id desc')->value('id');
			$Secondfb->where(['id'=>$id])->update(['classid' => $newmin, 'fbstatus' => 1]);
			unset($data[$id]);
			return $this->randclass($schid, $classnum);
		} else {
			return "人数已经均衡!";
		}
	}
	
	
	public function randclassfc($schid, $classnum, $fcengid)
	{
		for ($ii = 1; $ii <= $classnum; $ii++) {
			$datacount = db('classes' . $schid)->where('cno', $ii)->where('fcengid', $fcengid)->count();
			$datacountnew = SecondfbModel::where(['classid' => $ii, 'fcengid' => $fcengid])->count();
			$ar[] = $datacount + $datacountnew;
		}
		
		$max = array_search(max($ar), $ar);
		$min = array_search(min($ar), $ar);
		$rem = $ar[$max] - $ar[$min];
		$newmax = $max + 1;
		$newmin = $min + 1;
		if ($rem > 1) {
			$Secondfb = model('Secondfb');
			$Secondfb->where(['classid' => $newmax, 'fcengid' => $fcengid])->limit(1)->order('id desc')->update(['classid' => $newmin]);
			return $this->randclassfc($schid, $classnum, $fcengid);
		} else {
			return "人数已经均衡!";
		}
	}
	
	
	public function add()
	{
		if (request()->isPost()) {
			$data = input('post.');
			debugajax($data);
			$data['schid'] = Session::get('school_id');
			$count = count($data['pc_src']);//获取传过来有几张图片
			if ($count) {
				for ($i = 0; $i < $count; $i++) {
					$data['pics'][] = array("src" => $data['pc_src'][$i]);
				}
				$data['pics'] = serialize($data['pics']);
			}
			$result = SecondfbModel::Createsecondfb($data);
			if ($result) {
				$remsg['code'] = 100;
				$remsg['msg'] = "提交成功";
			} else {
				$remsg['code'] = 101;
				$remsg['msg'] = "提交失败";
			}
			return json($remsg);
		}
		return view();
	}
	
	
	public function edit()
	{
		if (request()->isPost()) {
			$data = input('post.');
			$count = count($data['pc_src']);//获取传过来有几张图片
			if ($count) {
				for ($i = 0; $i < $count; $i++) {
					$data['pics'][] = array("src" => $data['pc_src'][$i]);
				}
				$data['pics'] = serialize($data['pics']);
			}
			$where = ['id' => $data['id']];
			$field = ['name', 'classid', 'sex', 'fbstype', 'fcengid', 'id_num', 'content', 'pics'];
			$result = SecondfbModel::update($data, $where, $field);
			if ($result) {
				$remsg['code'] = 100;
				$remsg['msg'] = "修改成功";
			} else {
				$remsg['code'] = 101;
				$remsg['msg'] = "修改失败";
			}
			return json($remsg);
		}
		$res = SecondfbModel::where('id', input('id'))->find();
		if (!$res) {
			$this->error('该学生不存在！');
		}
		$this->assign(array('res' => $res, 'imgs' => unserialize($res['pics'])));
		return view();
	}
	
	
	public function upload()
	{
		if ($this->request->isPost()) {
			$res['code'] = 1;
			$res['msg'] = '上传成功！';
			$file = $this->request->file('file');
			$info = $file->move(ROOT_PATH . 'public' . DS . 'groupphoto');
			if ($info) {
				$res['name'] = $info->getFilename();
				$res['filepath'] = '/public/groupphoto/' . $info->getSaveName();
				$thumbs = ROOT_PATH . 'public' . DS . 'groupphoto' . '/' . $info->getSaveName();
				$image = Image::open($thumbs);
				$image->thumb(800, 800)->save($thumbs);
			} else {
				$res['code'] = 0;
				$res['msg'] = '上传失败！' . $file->getError();
			}
			return $res;
		}
	}
	
	
	public function delimg()
	{
		$data = input('post.');
		$thumbpath = $_SERVER['DOCUMENT_ROOT'] . $data['imgurl'];
		if (file_exists($thumbpath)) {
			@unlink($thumbpath);
		}
	}
	
	
	/**
	 * 批量删除方法
	 * @return bool
	 */
	public function del()
	{
		$id = input('id');
		$res = SecondfbModel::destroy($id);
		if ($res) {
			return true;
		} else {
			return false;
		}
	}
	
	
	public function checks()
	{
		$id = input('id');
		$status = input('status');
		// $res = SecondfbModel::destroy($id);
		$res = db("secondfb")->where('id', 'in', $id)->update(['status' => $status]);
		if ($res) {
			return true;
		} else {
			return false;
		}
	}
	
	
}
