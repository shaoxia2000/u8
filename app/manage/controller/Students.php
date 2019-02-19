<?php

namespace app\manage\controller;

use app\common\model\School as SchoolModel;
use app\common\model\Samename as SamenameModel;
use app\common\model\Setgroup as SetgroupModel;
use app\common\model\Teacher as TeacherModel;
use app\common\model\Recorddels as RecorddelsModel;
use app\common\model\Teachergroup as TeachergroupModel;
use Predis\Client as Redis;
use think\Config;
use think\Request;
use think\Session;

class Students extends Common
{
	/**
	 * 学校管理列表首页
	 */
	public function index()
	{
		if (Session::get('area_id') == "") {
			$this->redirect('http://yg.dqedu.net');
		}
		$name = input('name');
		$name && $map['schname'] = ['like', '%' . $name . '%'];
		$map['schtype'] = ['neq', 3];
		$schtype = input('schtype');
		$schtype && $map['schtype'] = ['eq', $schtype];
		$map['id'] = ['gt', 0];
		Session::has('area_id') && $map['areaid'] = ['eq', Session::get('area_id')];
		Session::get('school_id') != 0 && $map['schid'] = ['eq', Session::get('school_id')];
		$query = ['query' => request()->param()];
		$data = SchoolModel::where($map)->paginate($query);
		foreach ($data as $k => $v) {
			$stablename = 'bk_students' . $v['schid'];
			$this->CheckStudentsTable($stablename, $v['schtype']);
		}
		$this->assign(array('data' => $data));
		return view();
	}
	
	/**
	 * @param 根据传过来的表名字来检查是否存在该表，如果不存在则创建
	 */
	public function CheckStudentsTable($stablename, $schtype)
	{
		$mm = db();
		$isstudents = $mm->query("SHOW TABLES LIKE '{$stablename}'");
		if (!$isstudents) {
			$sqlstudents1 = "CREATE TABLE `{$stablename}` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `name` varchar(15) DEFAULT NULL COMMENT '姓名',
            `sex` varchar(10) DEFAULT NULL COMMENT '性别 1男  2女',
            `birthday` varchar(60) DEFAULT NULL COMMENT '出生日期',
            `nation` varchar(21) DEFAULT NULL COMMENT '民族',
            `address` varchar(150) DEFAULT NULL COMMENT '户籍地址',
            `in_time` varchar(60) DEFAULT NULL COMMENT '落户时间',
            `id_type` varchar(60) DEFAULT NULL COMMENT '身份证类型 1居民身份证 2香港特区护照',
            `id_num` varchar(25) DEFAULT NULL COMMENT '身份证件号',
            `single_is` varchar(18) DEFAULT NULL COMMENT '独生子女  1是  2否',
            `disabled_is` varchar(18) DEFAULT NULL COMMENT '残疾人类型  1无残疾  2残疾',
            `house_owner` varchar(50) DEFAULT NULL COMMENT '房屋产权人',
            `house_relation` varchar(15) DEFAULT NULL COMMENT '房屋产权人与新生关系 1父亲 2母亲 3祖父母 4外祖父母',
            `house_address` varchar(150) DEFAULT NULL COMMENT '房屋产权地址',
            `house_type` varchar(15) DEFAULT NULL COMMENT '房屋产权性质  1私产 2公产 3其它',
            `buy_time` varchar(60) DEFAULT NULL COMMENT '房屋产权购买时间',
            `name_two` varchar(15) DEFAULT NULL COMMENT '姓名 2 ',
            `relation_two` varchar(15) DEFAULT NULL COMMENT '关系 name_two 1父亲 2母亲 3祖父母 4外祖父母',
            `job_two` varchar(150) DEFAULT NULL COMMENT '工作单位 name_two',
            `tel_two` varchar(25) DEFAULT NULL COMMENT '电话 2',
            `name_three` varchar(15) DEFAULT NULL COMMENT '姓名 3 ',
            `relation_three` varchar(15) DEFAULT NULL COMMENT '关系 name_3 1父亲 2母亲 3祖父母 4外祖父母',
            `job_three` varchar(150) DEFAULT NULL COMMENT '工作单位 name_3',
            `tel_three` varchar(25) DEFAULT NULL COMMENT '电话 3',
            `writer` varchar(15) DEFAULT NULL COMMENT '填表人',
            `writer_relation` varchar(15) DEFAULT NULL COMMENT '填表人关系 1父亲 2母亲 3祖父母 4外祖父母',
            `sbt` int(2) DEFAULT NULL COMMENT '是否是双胞胎,有值为是',
            `xstatus` int(2) DEFAULT NULL COMMENT '是否参与分班',
            `bingo` int(2) DEFAULT NULL COMMENT '备留字段',
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sqlstudents2 = "CREATE TABLE `{$stablename}` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `name` varchar(15) DEFAULT NULL COMMENT '姓名',
            `chinese` varchar(10) DEFAULT NULL COMMENT '语文',
            `smath` varchar(10) DEFAULT NULL COMMENT '数学',
            `english` varchar(10) DEFAULT NULL COMMENT '英语',
            `zcj` varchar(10) DEFAULT NULL COMMENT '总成绩',
            `sex` varchar(10) DEFAULT NULL COMMENT '性别 1男  2女',
            `graduate` varchar(150) DEFAULT NULL COMMENT '毕业学校',
            `graduate_num` varchar(20) DEFAULT NULL COMMENT '毕业学校标识码',
            `student_code` varchar(25) DEFAULT NULL COMMENT '学籍号',
            `nation` varchar(21) DEFAULT NULL COMMENT '民族',
            `address` varchar(150) DEFAULT NULL COMMENT '户籍地址',
            `in_time` varchar(60) DEFAULT NULL COMMENT '落户时间',
            `id_type` varchar(60) DEFAULT NULL COMMENT '身份证类型',
            `id_num` varchar(25) DEFAULT NULL COMMENT '身份证件号',
            `house_relation` varchar(15) DEFAULT NULL COMMENT '户主与学生关系 1父亲 2母亲 3祖父母 4外祖父母',
            `zj_type` varchar(15) DEFAULT NULL COMMENT '证件类型  1居住证 2产权证',
            `zj_address` varchar(150) DEFAULT NULL COMMENT '证件地址',
            `zj_time` varchar(60) DEFAULT NULL COMMENT '证件时间',
            `zj_relation` varchar(15) DEFAULT NULL COMMENT '持证人与学生关系 1父亲 2母亲 3祖父母 4外祖父母',
            `x_name` varchar(15) DEFAULT NULL COMMENT '姓名',
            `x_relation` varchar(15) DEFAULT NULL COMMENT '关系 1父亲 2母亲 3祖父母 4外祖父母',
            `x_tel` varchar(25) DEFAULT NULL COMMENT '电话',
            `writer` varchar(15) DEFAULT NULL COMMENT '填表人',
            `writer_relation` varchar(15) DEFAULT NULL COMMENT '填表人关系 1父亲 2母亲 3祖父母 4外祖父母',
            `type` varchar(30) DEFAULT NULL COMMENT '新生类型 1随迁子女及其他 2两证不统一 3两证统一',
            `xstatus` int(2) DEFAULT NULL COMMENT '是否参与分班',
            `sbt` int(2) DEFAULT NULL COMMENT '是否是双胞胎,有值为是',
            `bingo` int(2) DEFAULT NULL COMMENT '备留字段',
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$sqlstudents3 = "CREATE TABLE `{$stablename}` (
           `id` int(10) NOT NULL AUTO_INCREMENT,
            `high_school` varchar(60) DEFAULT NULL COMMENT '录取高中',
            `name` varchar(15) DEFAULT NULL COMMENT '姓名',
            `sex` varchar(10) DEFAULT NULL COMMENT '性别 1男  2女',
            `junior_school` varchar(60) DEFAULT NULL COMMENT '初中学校',
            `id_num` varchar(25) DEFAULT NULL COMMENT '身份证件号',
            `art` varchar(60) DEFAULT NULL COMMENT '艺体专项',
            `zcj` varchar(10) DEFAULT NULL COMMENT '文化课总成绩',
            `sk_zcj` varchar(10) DEFAULT NULL COMMENT '术科总成绩',
            `luqu` varchar(10) DEFAULT NULL COMMENT '录取批次',
            `chinese` varchar(10) DEFAULT NULL COMMENT '语文',
            `smath` varchar(10) DEFAULT NULL COMMENT '数学',
            `english` varchar(10) DEFAULT NULL COMMENT '英语',
            `physics` varchar(10) DEFAULT NULL COMMENT '物理',
            `chemistry` varchar(10) DEFAULT NULL COMMENT '化学',
            `geography` varchar(10) DEFAULT NULL COMMENT '地理',
            `biologic` varchar(10) DEFAULT NULL COMMENT '生物',
            `history` varchar(10) DEFAULT NULL COMMENT '历史',
            `politics` varchar(10) DEFAULT NULL COMMENT '政治',
            `sports` varchar(10) DEFAULT NULL COMMENT '体育',
            `graduate_num` varchar(20) DEFAULT NULL COMMENT '毕业学校标识码',
            `student_code` varchar(25) DEFAULT NULL COMMENT '学籍号',
            `nation` varchar(21) DEFAULT NULL COMMENT '民族',
            `parents` varchar(15) DEFAULT NULL COMMENT '家长姓名',
            `relation` varchar(15) DEFAULT NULL COMMENT '与学生关系 1父亲 2母亲 3祖父母 4外祖父母',
            `tel` varchar(25) DEFAULT NULL COMMENT '家长电话',
            `xstatus` int(2) DEFAULT NULL COMMENT '是否参与分班',
            `fcengid` int(2) DEFAULT NULL COMMENT '分层标识字段',
            `zcjbk` varchar(10) DEFAULT NULL COMMENT '文化课总成绩',
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			switch ($schtype) {
				case 1:
					$mm->execute($sqlstudents1);
					break;
				case 2:
					$mm->execute($sqlstudents2);
					break;
				case 3:
					$mm->execute($sqlstudents3);
					break;
			}
		}
		
	}
	
	
	/**
	 * 在学校列表上点击跳入教师列表展示
	 */
	public function sview()
	{
		return view();
	}
	
	/**
	 * 在学校列表上点击跳入教师列表展示
	 */
	public function cview()
	{
		return view();
	}
	
	
	/**
	 * 在学校列表上点击跳入教师列表展示
	 */
	public function gview()
	{
		return view();
	}
	
	/**
	 * @layui table返回数据
	 */
	public function pagedata()
	{
		$map['id'] = ['gt', 0];
		$keyname = input('name');
		$keysfz = input('sfz');
		$keysbt = input('sbtval');
		$keyname && $map['name'] = ['like', '%' . $keyname . '%'];
		$keysfz && $map['id_num'] = ['eq', $keysfz];
		$keysbt == 1 && $map['sbt'] = ['gt', 0];
		//获取总条数
		$list = db('students' . input('schid'))->where($map)->select();
		$count = count($list);
		//获取每页显示的条数
		$limit = Request::instance()->param('limit');
		//获取当前页数
		$page = Request::instance()->param('page');
		//计算出从那条开始查询
		$begin = ($page - 1) * $limit;
		// 查询出当前页数显示的数据
//		$map['id'] = ['egt', $begin];
		$list = db('students' . input('schid'))->where($map)->limit($begin, $limit)->order('id asc')->select();
		foreach ($list as $k => $v) {
			if ($v['xstatus'] == "") {
				$v['xstatus'] = '是';
			} else {
				$v['xstatus'] = '否';
			}
			if ($v['sbt']) {
				$sbtname = db('students' . input('schid'))->where('id', $v['sbt'])->value('name');
				$v['sbt'] = '与' . $sbtname . '为双胞胎';
			} else {
				$v['sbt'] = '无';
			}
			
			$arr[] = $v;
		}
		//返回数据
		return ["code" => "0", "msg" => "", "count" => $count, "data" => $arr];
	}
	
	/*
		新增二次分班学生
	*/
	public function add()
	{
		if (request()->isPost()) {
			$data = input('post.');
			$fileimg = request()->file('img');
			if (isset($fileimg)) {
				foreach ($fileimg as $key => $value) {
					$infopic = $value->move(ROOT_PATH . 'public/uploads/pic');
					$pic = $infopic->getSaveName();
					$pict = str_replace("\\", "/", $pic);
					$new[$key] = 'uploads/pic/' . $pict;
				}
				$data['image'] = json_encode($new, JSON_UNESCAPED_SLASHES);
			}
			
			$result = db('students')->insert($data);
			
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
	
	
	public function lst()
	{
		$where['schid'] = session('school_id');
		$where['classid'] = array('eq', 0);
		$data = db('students')->where($where)->paginate();
		
		foreach ($data as $key => $value) {
			$datas[$key] = json_decode($value['image'], true);
		}
		
		$this->assign('data', $data);
		$this->assign('datas', $datas);
		return view();
	}
	
	
	public function edit()
	{
		if (request()->isPost()) {
			$data = input('post.');
			$where = ['id' => $data['id']];
			$field = ['name', 'sex', 'duty', 'school', 'age', 'teachage', 'tel', 'thumb'];
			$result = TeacherModel::update($data, $where, $field);
			if ($result) {
				$this->success('修改教师成功', url('tview', ['schid' => $data['schid']]));
			} else {
				$this->error('修改教师失败');
			}
			return;
		}
		$res = TeacherModel::where('id', input('id'))->find();
		if (!$res) {
			$this->error('该教师不存在！');
		}
		$this->assign(array('res' => $res,));
		return view();
	}
	
	
	public function save_status()
	{
		$resdata = input('post.');
		if ($resdata['schtype'] == 1) {
			$data = db('students' . $resdata['schid'])->select();
			if ($data->isEmpty()) {
				return '当前学校数据未导入!';
			}
		} else {
			$sex = db('students' . $resdata['schid'])->limit(1)->order('id', 'desc')->value("sex");
			if (!$sex) {
				return '当前学校数据未整合!';
			}
		}
		
		
		$tgroup = TeachergroupModel::where(['schid' => $resdata['schid'], 'status' => 1])->find();
		
		if ($tgroup['astatus'] == 1) {
			$data = ['astatus' => 0, 'status' => 0];
			$where['id'] = ['eq', $tgroup['id']];
			$field = ['astatus', 'status'];
			TeachergroupModel::update($data, $where, $field);
			return 1;
		} else {
			$data = ['astatus' => 1];
			$where['id'] = ['eq', $tgroup['id']];
			$field = ['astatus'];
			TeachergroupModel::update($data, $where, $field);
			$datasame = samenarr($resdata['schid']);
			$datanew = ['schid' => $resdata['schid'], 'sameid' => $datasame];
			SamenameModel::create($datanew);
			return 1;
		}
		
	}
	
	
	/**
	 * 批量删除方法
	 * @return bool
	 */
	public function del()
	{
		$id = input('id');
		$schid = input('schid');
		$dbname = "students" . $schid;
		$res = db($dbname)->delete($id);
		if ($res) {
			$remsg['code'] = 0;
		} else {
			$remsg['code'] = 1;
		}
		return $remsg;
	}
	
	
	/**
	 * @layui 批量删除
	 */
	public function pdel()
	{
		if (request()->isPost()) {
			$config = Config::get('redis');
			$redis = new Redis($config);
			$key = 'newsfz';
			$datas = input('post.');
			$dbname = "students" . $datas['schid'];
			foreach ($datas['id'] as $v) {
				$sfzls = db($dbname)->where('id', $v)->value('id_num');
				$new = preg_replace('/\"/', '', trim($sfzls));
				$newsfz = $new . "==||==" . $datas['schid'];
				$redis->srem($key, $newsfz);
				
				$resid=TeachergroupModel::where(['status' => 1, 'astatus' => 1, 'gstatus' => 1, 'schid' => $datas['schid']])->value('id');
				if ($resid) {
					$data = [
						'schid' => $datas['schid'],
						'areaid' => Session::get('area_id'),
						'xstatus' => TeachergroupModel::where(['status' => 1, 'astatus' => 1, 'gstatus' => 1, 'schid' => $datas['schid']])->value('xstatus'),
						'sid'=>$v,
						'sname'=>db('classes'.$datas['schid'])->where('id',$v)->value('name'),
						'scno'=>db('classes'.$datas['schid'])->where('id',$v)->value('cno'),
						'sfz'=>db('students'.$datas['schid'])->where('id',$v)->value('id_num')
					];
					RecorddelsModel::create($data);
				}
				
				db($dbname)->where('id', $v)->delete();
			}
			$data['code'] = 0;
			return $data;
		}
	}
	
	/**
	 * layui 批量设置不分班
	 */
	public function pset()
	{
		if (request()->isPost()) {
			$datas = input('post.');
			$dbname = "students" . $datas['schid'];
			foreach ($datas['id'] as $v) {
				db($dbname)->where('id', $v)->update(['xstatus' => 1]);
			}
			$data['code'] = 0;
			return $data;
		}
	}


//	public function exportgstudents()
//	{
//		if (Session::get('area_id') == "") {
//			$this->redirect('http://yg.dqedu.net');
//		}
//
//		$name = GetSchoolname(input('schid')) . "-学生数据表" . ".xls";
//
//		if (GetSchooltype(input('schid')) == 1) {
//			$header = ['姓名', '性别', '生日', '民族', '户籍地址', '落户时间', '身份证类型', '身份证号码', '是否独生子女', '残疾人类型', '房屋产权人', '房屋产权人与新生关系', '房屋产权地址', '房屋产权性质', '房屋产权购买的时间', '姓名', '
//关系', '工作单位', '电话', '姓名', '关系', '工作单位', '电话', '填表人', '填表人关系'];
//			$data = db('students' . input('schid'))->select();
//			foreach ($data as $k => $v) {
//				$m['name'] = $v['name'];
//				$m['sex'] = $v['sex'];
//				$m['birthday'] = $v['birthday'];
//				$m['nation'] = $v['nation'];
//				$m['address'] = $v['address'];
//				$m['in_time'] = $v['in_time'];
//				$m['id_type'] = $v['id_type'];
//				$m['id_num'] = $v['id_num'] . ' ';
//				$m['single_is'] = $v['single_is'];
//				$m['disabled_is'] = $v['disabled_is'];
//				$m['house_owner'] = $v['house_owner'];
//				$m['house_relation'] = $v['house_relation'];
//				$m['house_address'] = $v['house_address'];
//				$m['house_type'] = $v['house_type'];
//				$m['buy_time'] = $v['buy_time'];
//				$m['name_two'] = $v['name_two'];
//				$m['relation_two'] = $v['relation_two'];
//				$m['job_two'] = $v['job_two'];
//				$m['tel_two'] = $v['tel_two'] . ' ';
//				$m['name_three'] = $v['name_three'];
//				$m['relation_three'] = $v['relation_three'];
//				$m['job_three'] = $v['job_three'];
//				$m['tel_three'] = $v['tel_three'] . ' ';
//				$m['writer'] = $v['writer'];
//				$m['writer_relation'] = $v['writer_relation'];
//				$arr[] = $m;
//			}
//		}
//
//		if (GetSchooltype(input('schid')) == 2) {
//			$header = ['姓名', '性别', '总成绩', '语文', '数学', '外语', '毕业院校', '毕业标识码', '学籍号', '民族', '户籍地址', '落户时间', '身份证类型', '身份证件号', '户主与学生关系', '证件类型', '证件地址', '证件时间', '持证人与学生关系', '姓名', '关系', '电话', '填表人', '填表人关系', '新生类型'];
//			$data = db('students' . input('schid'))->select();
//			foreach ($data as $k => $v) {
//				$m['name'] = $v['name'];
//				$m['sex'] = $v['sex'];
//				$m['zcj'] = $v['zcj'];
//				$m['chinese'] = $v['chinese'] . ' ';
//				$m['smath'] = $v['smath'];
//				$m['english'] = $v['english'];
//				$m['graduate'] = $v['graduate'];
//				$m['graduate_num'] = $v['graduate_num'];
//				$m['student_code'] = $v['student_code'];
//				$m['nation'] = $v['nation'];
//				$m['address'] = $v['address'];
//				$m['in_time'] = $v['in_time'];
//				$m['id_type'] = $v['id_type'];
//				$m['id_num'] = $v['id_num'] . ' ';
//				$m['house_relation'] = $v['house_relation'];
//				$m['zj_type'] = $v['zj_type'];
//				$m['zj_address'] = $v['zj_address'];
//				$m['zj_time'] = $v['zj_time'];
//				$m['zj_relation'] = $v['zj_relation'];
//				$m['x_name'] = $v['x_name'];
//				$m['x_relation'] = $v['x_relation'];
//				$m['x_tel'] = $v['x_tel'];
//				$m['writer'] = $v['writer'];
//				$m['writer_relation'] = $v['writer_relation'];
//				$m['type'] = $v['type'];
//				$arr[] = $m;
//			}
//		}
//
//		if (GetSchooltype(input('schid')) == 3) {
//			$header = ['姓名', '性别', '毕业初中', '身份证号', '总成绩', '语文', '数学', '英语', '物理', '化学', '地理', '生物', '历史', '政治', '体育'];
//			$data = db('students' . input('schid'))->select();
//			foreach ($data as $k => $v) {
//				$m['name'] = $v['name'];
//				$m['sex'] = $v['sex'];
//				$m['junior_school'] = $v['junior_school'];
//				$m['id_num'] = $v['id_num'] . ' ';
//				$m['zcj'] = $v['zcj'];
//				$m['chinese'] = $v['chinese'];
//				$m['smath'] = $v['smath'];
//				$m['english'] = $v['english'];
//				$m['physics'] = $v['physics'];
//				$m['chemistry'] = $v['chemistry'];
//				$m['geography'] = $v['geography'];
//				$m['biologic'] = $v['biologic'];
//				$m['history'] = $v['history'];
//				$m['politics'] = $v['politics'];
//				$m['sports'] = $v['sports'];
//				$arr[] = $m;
//			}
//		}
//
//
//		$this->excelgstudents($name, $header, $arr);
//	}

//	public function excelgstudents($fileName = '', $headArr = [], $data = [])
//	{
//		vendor("PHPExcel.PHPExcel.PHPExcel");
//		vendor("PHPExcel.PHPExcel.IOFactory");
//		$objPHPExcel = new \PHPExcel();
//		$objPHPExcel->getProperties();
//		$key = 0; // 设置表头
//		$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setWidth(7);
//		$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setWidth(5);
//		$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setWidth(20);
//		$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setWidth(20);
//		foreach ($headArr as $v) {
//			$colum = \PHPExcel_Cell::stringFromColumnIndex($key);
//			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
//			$key += 1;
//		}
//		$column = 2;
//		$objActSheet = $objPHPExcel->getActiveSheet();
//		foreach ($data as $key => $rows) { //行写入
//			$span = 0;
//			foreach ($rows as $keyName => $value) {// 列写入
//				$j = \PHPExcel_Cell::stringFromColumnIndex($span);
//				$objActSheet->setCellValue($j . $column, $value);
//				$span++;
//			}
//			$column++;
//		}
//		$fileName = iconv("utf-8", "gb2312", $fileName); // 重命名表
//		$objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表
//		header('Content-Type: application/vnd.ms-excel');
//		header("Content-Disposition: attachment;filename='$fileName'");
//		header('Cache-Control: max-age=0');
//		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
//		$objWriter->save('php://output'); // 文件通过浏览器下载
//		exit();
//	}
	
	
	public function resetdata()
	{
		$data = input('post.');
		$schtype = GetSchooltype($data['schid']);
		if ($schtype != 2) {
			$dbname = "students" . $data['schid'];
			$config = Config::get('redis');
			$redis = new Redis($config);
			$key = 'newsfz';
			$datas = db($dbname)->field('id_num')->select();
			foreach ($datas as $k => $v) {
				$new = preg_replace('/\"/', '', trim($v['id_num']));
				$newsfz = $new . "==||==" . $data['schid'];
				$redis->srem($key, $newsfz);
			}
			$state = db($dbname)->where('id', 'gt', 0)->delete();
			if ($state !== false) {
				return 1;
			} else {
				return '删除失败!';
			}
		} else {
			if (Session::get('school_id') == 0) {
				$dbname = "students" . $data['schid'];
				$state = db($dbname)->where('id', 'gt', 0)->delete();
				if ($state !== false) {
					return 1;
				} else {
					return '删除失败!';
				}
			} else {
				$dbname = "students" . $data['schid'];
				$map = ['chinese' => '', 'smath' => '', 'english' => '', 'zcj' => ''];
				db($dbname)->where('id', 'gt', 0)->update($map);
			}
			return 1;
		}
	}
	
	public function confirmschoolf($schid)
	{
		$data = ['confirmnum' => 0];
		$where = ['schid' => $schid];
		$field = ['confirmnum'];
		SchoolModel::update($data, $where, $field);
	}
	
	public function bandsbt()
	{
		if (request()->isPost()) {
			$data = input('post.');
			if ($data['sfzold'] == $data['sfz']) {
				$remsg['code'] = 101;
				$remsg['msg'] = "不能与自己绑定！";
				return $remsg;
			}
			$map['id_num'] = ['eq', $data['sfz']];
			$map['sbt'] = ['gt', 0];
			$newid = db('students' . $data['schid'])->where('id_num', $data['sfz'])->value('id');
			db('students' . $data['schid'])->where('id', $data['id'])->update(['sbt' => $newid]);
			$remsg['code'] = 100;
			$remsg['msg'] = "双胞胎绑定成功";
			return $remsg;
		}
		return view();
	}
	
	public function sbtcancel()
	{
		if (request()->isPost()) {
			$data = input('post.');
			db('students' . $data['schid'])->where('id', $data['id'])->update(['sbt' => null]);
		}
	}
	
	
	public function setwhere()
	{
		$schid = input('schid');
		$stype = GetSchooltype($schid);
		$res = SetgroupModel::where('schid', $schid)->find();
		$this->assign(array('res' => $res, 'stype' => $stype));
		return view();
	}
	
	public function saveset()
	{
		if (request()->isPost()) {
			$data = input('post.');
			if (!$data['knowledge']) {
				$data['knowledge'] = 'off';
			}
			if (!$data['nofb']) {
				$data['nofb'] = 'off';
			}
			$where = ['schid' => $data['schid']];
			$field = ['sbt', 'knowledge', 'nofb'];
			$res = SetgroupModel::where($where)->find();
			if ($res) {
				$result = SetgroupModel::update($data, $where, $field);
			} else {
				$result = SetgroupModel::create($data);
			}
//			RecordassignModel::where('areaid', Session::get('area_id'))->setDec('assigngroupnums');
//			if ($result) {
//				$cdbname = "bk_classes" . $data['schid'];
//				$isclasses = db()->query("SHOW TABLES LIKE '{$cdbname}'");
//				if ($isclasses) {
//					$delsql = "DROP TABLE `{$cdbname}`";
//					db()->execute($delsql);
//				}
//			}
			$remsg['code'] = 100;
			$remsg['msg'] = "设置成功";
			return $remsg;
		}
	}
	
	/**
	 * 小学区管理员新增学生数据
	 */
	public function xiaoxueadd()
	{
		if (request()->isPost()) {
			$data = input('post.');
			$dbname = $data['schid'];
			unset($data['schid']);
			db('students' . $dbname)->insert($data);
			$remsg['code'] = 100;
			$remsg['msg'] = "新增成功";
			return $remsg;
		}
		return view();
	}
	
	/**
	 * 小学区管理员修改学生数据
	 */
	public function xiaoxueedit()
	{
		if (request()->isPost()) {
			$data = input('post.');
			if ($data['field'] == 'sex') {
				if (trim($data['value']) != '男' && trim($data['value']) != '女') {
					$remsg['code'] = 101;
					return $remsg;
				}
			}
			
			if ($data['field'] == 'zcj' || $data['field'] == 'chinese' || $data['field'] == 'smath' || $data['field'] == 'english') {
				if (!is_numeric($data['value']) || $data['value'] < 0) {
					$remsg['code'] = 101;
					return $remsg;
				}
			}
			
			if ($data['field'] == 'name') {
				if (trim($data['value']) == '') {
					$remsg['code'] = 101;
					return $remsg;
				}
			}
			
			
			db('students' . $data['schid'])->where('id', $data['id'])->update([$data['field'] => $data['value']]);
			$remsg['code'] = 100;
			return $remsg;
		}
	}
	
	
	/**
	 * 初中学区管理员新增学生数据
	 */
	public function chuzhongadd()
	{
		if (request()->isPost()) {
			$data = input('post.');
			$dbname = $data['schid'];
			unset($data['schid']);
			db('students' . $dbname)->insert($data);
			$remsg['code'] = 100;
			$remsg['msg'] = "新增成功";
			return $remsg;
		}
		return view();
	}
	
	
}
