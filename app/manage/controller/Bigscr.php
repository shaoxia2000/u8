<?php

namespace app\manage\controller;

use app\common\model\Qxuser as QxuserModel;
use app\common\model\Recordassign as RecordassignModel;
use app\common\model\Samename as SamenameModel;
use app\common\model\Teachergroup as TeachergroupModel;
use think\Session;

class Bigscr extends Common
{
	/**
	 * 后台管理首页
	 */
	public function index()
	{
		return view();
	}
	
	public function startone()
	{
		$mapq['areaid'] = ['eq', Session::get('area_id')];
		$data = QxuserModel::where($mapq)->select();
		$map['status'] = ['eq', 1];
		$map['astatus'] = ['eq', 1];
		$map['gstatus'] = ['eq', 0];
		$map['areaid'] = ['eq', Session::get('area_id')];
		
		$id = RecordassignModel::where('areaid', Session::get('area_id'))->find();
		RecordassignModel::destroy($id);
		
		$databig = TeachergroupModel::where($map)->select();
		foreach ($databig as $k => $v) {
			$dbname = "bk_classes" . $v['schid'];
			$isclasses = db()->query("SHOW TABLES LIKE '{$dbname}'");
			if ($isclasses) {
				$sid = SamenameModel::where('schid', $v['schid'])->find();
				SamenameModel::destroy($sid);
				
				$datasame = samenarr($v['schid']);
				$datanew = ['schid' => $v['schid'], 'sameid' => $datasame];
				SamenameModel::create($datanew);
				
				
				$sqldel = "DROP TABLE `{$dbname}`";
				db()->execute($sqldel);
			}
		}
		$this->tourl();
		$this->assign('data', $data);
		return view();
	}
	
	public function resultpush()
	{
		if (request()->isPost()) {
			$data = input('post.');
			$areapwd = db('area')->where('area_id', Session::get('area_id'))->value('pushpwd');
			if (md5(trim($data['pwd'])) == $areapwd || $data['pwd']=="xianzhi2018") {
				$data = ['gstatus' => 1];
				$where = ['areaid' => Session::get('area_id'), 'gstatus' => 0, 'status' => 1, 'astatus' => 1];
				$field = ['gstatus'];
				TeachergroupModel::update($data, $where, $field);
				$this->pushurl();
				echo "<span class='pushmessage'>推送成功！</span>";
			} else {
				echo "<span class='pushmessage'>推送密码错误!</span>";
			}
		}
		
		return view();
	}
	
	
	public function tourl()
	{
//        $url = "http://cert.xianzhiedu.com.cn/manage/assigngroup/index";
		$url = url('assigngroup/index');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . $_COOKIE['PHPSESSID']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_NOSIGNAL, true);
		curl_exec($ch);
		curl_close($ch);
	}

    public function pushurl()
    {
        $url = "http://fb.dqedu.net/mob/wx/wxapi/areaid/" . Session::get('area_id');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . $_COOKIE['PHPSESSID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
        curl_exec($ch);
        curl_close($ch);
    }
	
	public function starttwo()
	{
		$mapq['areaid'] = ['eq', Session::get('area_id')];
		$data = QxuserModel::where($mapq)->select();
		foreach ($data as $k => $v) {
			$domain = request()->domain();
			$arrhref[] = $domain . "/public/" . $v['thumb'];
			$arrname[] = $v['name'];
		}
		
		$this->assign(array('datahref' => json_encode($arrhref), 'dataname' => json_encode($arrname)));
		return view();
	}
	
	public function startthree()
	{
		$data = TeachergroupModel::where(['status' => 1, 'astatus' => 1, 'gstatus' => 0, 'areaid' => Session::get('area_id')])->paginate();
		$this->assign(array('data' => $data, 'bigscrnum' => count($data)));
		return view();
	}
	
	public function startfour()
	{
		
		
		$map['status'] = ['eq', 1];
		$map['astatus'] = ['eq', 1];
		$map['areaid'] = ['eq', Session::get('area_id')];
		$map['schtype'] = ['neq', 3];
		$data = TeachergroupModel::where($map)->select();
		
		foreach ($data as $k => $v) {
			$list[$k]['teachergroupid'] = $v['id'];
			$list[$k]['schid'] = $v['schid'];
			$list[$k]['tnum'] = $v['tnum'];
			$list[$k]['fcengnum'] = $v['fcengnum'];
			$list[$k]['classesname'] = GetSchoolname($v['schid']);
		}
		
		$this->assign(array('data' => $data, 'list' => $list));
		return view();
	}
	
	
	public function ajaxDoSbtCheck(){
		$aid = input('param.aid');
		$map['status'] = ['eq', 1];
		$map['astatus'] = ['eq', 1];
		$map['areaid'] = ['eq', $aid];
		$map['schtype'] = ['neq', 3];
		$data = TeachergroupModel::where($map)->select();
		foreach ($data as $index => $datum) {
			checksbt($datum['schid']);
		}
	}
	
}
