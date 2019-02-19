<?php

namespace app\manage\controller;

use app\common\model\Teacher as TeacherModel;
use app\common\model\Teachergroup as TeachergroupModel;
use app\common\model\Tfaccess as TfaccessModel;
use app\common\model\Tgaccess as TgaccessModel;
use think\Request;
use think\Session;

class Teachergroup extends Common
{
	/**
	 * 教师组列表首页
	 */
	public function index()
	{
		$name = input('name');
		$schid = input('schid');
		$name && $map['tgroupname'] = ['like', '%' . $name . '%'];
		$schid && $map['schid'] = ['eq', $schid];
		$query = ['query' => request()->param()];
		$data = TeachergroupModel::where($map)->paginate($query);
		$this->assign('data', $data);
		return view();
	}
	
	/**
	 * 设置教师组首页
	 */
	public function setgroup()
	{
		$gid = input('id');
		$schid = input('schid');
		$res = TeachergroupModel::where('id', $gid)->find();
		$resfc = $res['fcengnum'];
		for ($a = 0; $a < $res['tnum']; $a++) {
			$map['tgid'] = ['eq', $a];
			$map['gid'] = ['eq', $gid];
			$list[$a]['bb'] = $a;
			$list[$a]['cc'] = TgaccessModel::where($map)->select();
		}
		for ($b = 0; $b < $res['fcengnum']; $b++) {
			$maps['fid'] = ['eq', $b];
			$maps['gid'] = ['eq', $gid];
			$listfc[$b]['bb'] = $b;
			$listfc[$b]['cc'] = TfaccessModel::where($maps)->select();
		}
		$where['schid'] = ['eq', $schid];
		$tdata = TeacherModel::where($where)->select();
		$this->assign(array('list' => $list, 'listfc' => $listfc, 'gid' => $gid, 'tdata' => $tdata, 'resfc' => $resfc, 'schid' => $schid));
		return view();
	}
	
	/**
	 * 增加教师组方法
	 */
	public function add()
	{
		if (request()->isPost()) {
			$data = input('post.');
			$data['areaid'] = Session::get('area_id');
			$data['schid'] = Session::get('school_id');
			$data['schtype'] = GetSchooltype(Session::get('school_id'));
			$data['tgroupname'] = GetSchoolname($data['schid']) . '-' . $data['tgroupname'];
            if ($data['fcengnum'] != 0) {
//                $avgnum = intval(floor(CountStudents($data['schid']) / $data['fcengnum']));
			//取得所选学科数组
                foreach ($data['knowledge'] as $k => $v) {
                    $countzcj[] = $k;
                }
			//根据所选学科更新总成绩
                $studentsdata = db('students' . $data['schid'])->where('xstatus is null')->select();
                foreach ($studentsdata as $k => $v) {
                    $sum = 0;
                    foreach ($countzcj as $key => $item) {
                        $sum += $v[$item];
                    }
	                db('students' . $data['schid'])->where('id', $v['id'])->update(['zcjbk' => $v['zcj']]);
                    db('students' . $data['schid'])->where('id', $v['id'])->update(['zcj' => $sum]);
                }
                $data['knowledge'] = serialize($data['knowledge']);
			//取出数据按总分高低排列形成新数组
//				$resetsdata = db('students' . $data['schid'])->field('id')->where('xstatus is null')->order('zcj desc')->select()->toArray();
//				foreach ($resetsdata as $k => $v) {
//					$arr[] = $v['id'];
//				}
			//将新数组分成$data['fcengnum']份，每份$avgnum人形成二维数组循环后重置为一维数组并加上分层标识
//				$newdata = $this->array_group($arr, $data['fcengnum'], $avgnum);
//				for ($i = 0; $i <= $data['fcengnum'] - 1; $i++) {
//					foreach ($newdata[$i] as $key => $vos) {
//						db('students' . $data['schid'])->where('id', $vos)->update(['fcengid' => $i]);
//					}
//				}
			//将每份衔接处相同总分选出并更新分层标识
//				for ($j = 0; $j <= $data['fcengnum'] - 1; $j++) {
//					$fcengpid = $j + 1;
//					if ($fcengpid < ($data['fcengnum'] - 1)) {
//						$minzcj = db('students' . $data['schid'])->where('fcengid', $j)->limit(1)->order('zcj asc')->value('zcj');
//						db('students' . $data['schid'])->where(['zcj' => $minzcj, 'fcengid' => $fcengpid])->order('zcj desc')->update(['fcengid' => $j]);
//					}
//				}
            }
			
			
			$result = TeachergroupModel::create($data);
			if ($result) {
				$remsg['code'] = 100;
				$remsg['msg'] = "提交成功";
			} else {
				$remsg['code'] = 101;
				$remsg['msg'] = "提交失败";
			}
			return json($remsg);
		}
		$schid = input('schid');
		$schnum = CountStudents($schid);
		$schtype = GetSchooltype($schid);
		$this->assign(array('schnum' => $schnum, 'schtype' => $schtype, 'schid' => $schid));
		return view();
	}
	
	
	/**
	 * SUM查询单条件下各个字段的和
	 * access public
	 * @param string $field [] 字段名数组 多个字段用逗号分隔
	 * return float|int[]
	 */
	public function sumany($field = '')
	{
		if ($field = '') return $this->sum();
		if (!is_array($field)) {
			$field = explode(',', $field);
		}
		$str = "";
		$str2 = "";
		foreach ($field as $k => $v) {
			$str .= 'SUM(' . $field[$k] . ') AS tp_sum' . $k . ',';
			$str2 .= 'tp_sum' . $k . ',';
		}
		$str = substr($str, 0, -1);
		$str2 = substr($str, 0, -1);
		return $this->field($str2)->find();
	}
	
	
	/**
	 * 批量删除方法
	 * @return bool
	 */
	public function del()
	{
		$id = input('id');
		$res = TeachergroupModel::destroy($id);
		if ($res) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 教师被拖拽进组内时，动态入库
	 */
	public function subgroup()
	{
		if (Request::instance()->isAjax()) {
			$data = input("post.");
			$data['tgname'] = IntToChr($data['tgid']);
			TgaccessModel::CreateTgaccess($data);
		}
	}
	
	/**
	 * 组被拖入分层时，动态入库
	 */
	public function subsgroup()
	{
		if (Request::instance()->isAjax()) {
			$data = input("post.");
			TfaccessModel::CreateTfaccess($data);
		}
	}
	
	
	/**
	 * 前端拖拽后的按钮删除操作
	 * @gid, tgid, tid, isheader/组的总id/组内id/教师id/是否班主任
	 */
	public function delgroup()
	{
		if (Request::instance()->isAjax()) {
			$data = input("post.");
			TgaccessModel::where($data)->delete();
		}
	}
	
	/**
	 * 前端拖拽后的按钮删除操作
	 * @gid, tgid, tid, isheader/组的总id/组内id/教师id/是否班主任
	 */
	public function delgroups()
	{
		if (Request::instance()->isAjax()) {
			$data = input("post.");
			TfaccessModel::where($data)->delete();
		}
	}
	
	/**
	 * 从库中读取的记录的按钮删除操作
	 * 只传id
	 */
	public function delgroupsingle()
	{
		$where['id'] = input("id");
		TgaccessModel::where($where)->delete();
	}
	
	public function save_status()
	{
		$id = $_POST['id'];
		$schid = TeachergroupModel::getFieldById($id, 'schid');
		$status = TeachergroupModel::getFieldById($id, 'status');
		$tnum = TeachergroupModel::getFieldById($id, 'tnum');
		$fcengnum = TeachergroupModel::getFieldById($id, 'fcengnum');
		if ($status == 1) {
			$data = ['status' => 0];
			$where['id'] = ['eq', $id];
			$field = ['status'];
			TeachergroupModel::update($data, $where, $field);
			return 1;
		} else {
			if ($fcengnum > 0) {
				for ($i = 0; $i < $fcengnum; $i++) {
					$this->tjs($schid, $i, $tnum, $id);
				}
			}
			
			$resdata = Getcheckbzr($id, $tnum, $fcengnum);
			if ($resdata == 1) {
				$data = ['status' => 1];
				$dataf = ['status' => 0];
				$where['id'] = ['eq', $id];
				$wheref['id'] = ['neq', $id];
				$wheref['schid'] = ['eq', $schid];
				$field = ['status'];
				TeachergroupModel::update($data, $where, $field);
				TeachergroupModel::update($dataf, $wheref, $field);
				return 1;
			} else {
				return $resdata;
			}
		}
		
	}
	
	
	public function tjs($schid, $i, $tnum, $id)
	{
		$studentscount = db('students' . $schid)->where('xstatus is null')->count();
		$fcengnum = TeachergroupModel::getFieldById($id, 'fcengnum');
		$avgnumz = $studentscount / $tnum;
		$avgnum = sprintf("%.1f", $avgnumz);
		if ($i == $fcengnum - 1) {
			db('students' . $schid)->where('xstatus is null')->where('fcengid is null')->order('zcjbk desc')->update(['fcengid' => $i]);
		} else {
			$limitvalue = GetClassescnum($id, $i) * $avgnum;
			if (floor($limitvalue) == $limitvalue) {
			
			} else {
				$limitvalue = intval(floor($limitvalue)) + 1;
			}
			db('students' . $schid)->where('xstatus is null')->where('fcengid is null')->order('zcjbk desc')->limit($limitvalue)->update(['fcengid' => $i]);
		}
		$minzcj = db('students' . $schid)->where('fcengid is not null')->limit(1)->order('zcjbk asc')->value('zcjbk');
		db('students' . $schid)->where('fcengid is null')->where('zcjbk', $minzcj)->order('zcjbk desc')->update(['fcengid' => $i]);
	}
	
	
	/**
	 * 从库中读取的记录的按钮删除操作
	 * 只传id
	 */
	public function delgroupsingles()
	{
		$where['id'] = input("id");
		TfaccessModel::where($where)->delete();
	}
	
	public function checkt()
	{
		if (Request::instance()->isAjax()) {
			$data = input("post.");
			$map['gid'] = ['eq', $data['gid']];
			$map['tid'] = ['eq', $data['tid']];
			$map['isheader'] = ['eq', 1];
			$res = TgaccessModel::where($map)->find();
			if ($res) {
				$mapnew['gid'] = ['eq', $data['gid']];
				$mapnew['tgid'] = ['eq', $data['tgid']];
				$mapnew['isheader'] = ['eq', 1];
				$resnew = TgaccessModel::where($mapnew)->find();
				if ($resnew) {
					return $resnew;
				} else {
					return 2;
				}
				
			} else {
				$where['gid'] = ['eq', $data['gid']];
				$where['tgid'] = ['eq', $data['tgid']];
				TgaccessModel::where($where)->update(["isheader" => 0]);
				$wheren['gid'] = ['eq', $data['gid']];
				$wheren['tgid'] = ['eq', $data['tgid']];
				$wheren['tid'] = ['eq', $data['tid']];
				TgaccessModel::where($wheren)->update(["isheader" => 1]);
				return 1;
			}
		}
	}
	
	/**
	 * @param $arrF
	 * @param $user_count 分组数量
	 * @param $group_num 每组多少个
	 * @return array
	 */
	public function array_group($arrF, $user_count, $group_num)
	{
		for ($i = 0; $i < $user_count; $i++) {
			if ($i == $user_count - 1) {
				$arrT[] = array_slice($arrF, $i * $group_num);
			} else {
				$arrT[] = array_slice($arrF, $i * $group_num, $group_num);
			}
		}
		return $arrT;
	}
	
}
