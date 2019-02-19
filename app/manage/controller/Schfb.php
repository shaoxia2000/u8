<?php

namespace app\manage\controller;

use app\common\model\Bingo as BingoModel;
use app\common\model\Sign as SignModel;
use app\common\model\Teacher as TeacherModel;
use app\common\model\Teachergroup as TeachergroupModel;
use app\common\model\Tfaccess as TfaccessModel;
use app\common\model\Tgaccess as TgaccessModel;
use think\Session;

class Schfb extends Common
{
    /**
     * 后台管理首页
     */
    public function sone()
    {
        $checklock = Checkfblock();
        if ($checklock == 1) {
            $this->error('该学校已经分班！', url('schfb/stwo'));
        }

        $data = TeachergroupModel::where(['status' => 1, 'astatus' => 1, 'gstatus' => 1, 'schid' => Session::get('school_id')])->find();
        if ($data['fcengnum'] == 0) {
            $this->assign(array('data' => $data['tnum'], 'gid' => $data['id']));
            return view();
        } else {
            if (input('fcengid')) {
                $fid = input('fcengid');
            } else {
                $fid = 0;
            }
            $pagenumber = $fid + 1;
            $zulist = db('classes' . Session::get('school_id'))->distinct(true)->field('cno,fcengid')->where('fcengid', $fid)->order('fcengid asc')->select()->toArray();
            foreach ($zulist as $key => $value) {
                $zulist[$key]['cno'] = $value['cno'];
                $zulist[$key]['fcengid'] = $value['fcengid'];
            }
            $tgroupdata = TfaccessModel::where(['gid' => $data['id'], 'fid' => $fid])->select();
            $this->assign(array('gid' => $data['id'], 'zulist' => $zulist, 'fcengnum' => $data['fcengnum'], 'tgroupdata' => $tgroupdata, 'pagenumber' => $pagenumber));
            return view('sonefc');
        }
    }

    public function stwo()
    {
        $data = TeachergroupModel::where(['status' => 1, 'astatus' => 1, 'schid' => Session::get('school_id')])->find();
        $this->assign(array('data' => $data['tnum'], 'gid' => $data['id'], 'schid' => $data['schid']));
        return view();
    }

    public function sonestep()
    {
        $cno = input('id');
        $data = SignModel::where(['schid' => Session::get('school_id'), 'cno' => $cno])->select();
        $this->assign(array('data' => $data));
        return view();
    }


    public function sonestepfc()
    {
        $cno = input('id');
        $fcengid = input('fcengid');
        $data = SignModel::where(['schid' => Session::get('school_id'), 'cno' => $cno, 'fceng' => $fcengid])->select();
        $this->assign(array('data' => $data));
        return view();
    }

    public function dianzi()
    {

        $data = TeachergroupModel::where(['status' => 1, 'astatus' => 1, 'schid' => Session::get('school_id')])->find();
        if ($data['fcengnum'] == 0) {
            $res = BingoModel::where('schid', Session::get('school_id'))->find();
            if ($res) {
                $this->assign(array('xsz' => $res['xsz'], 'jsz' => $res['jsz']));
                for ($ii = 1; $ii <= $data['tnum']; $ii++) {
                    if ($ii != $res['jsz'] + 1) {
                        $ar[] = $ii;
                    }
                }
            } else {
                for ($ii = 1; $ii <= $data['tnum']; $ii++) {
                    $ar[] = $ii;
                }
            }


        } else {
            if (input('fcengid')) {
                $fid = input('fcengid');
            } else {
                $fid = 0;
            }
            $tgroupdata = TfaccessModel::where(['gid' => $data['id'], 'fid' => $fid])->select();
            foreach ($tgroupdata as $key => $value) {
                $ar[] = $value['tgid'] + 1;
            }
        }
        $this->assign(array('data' => $data['tnum'], 'gid' => $data['id'], 'datat' => json_encode($ar)));
        return view();
    }


    public function Getbigtname()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $map = ['gid' => $data['gid'], 'tgid' => $data['tgid'], 'isheader' => 1];
            $res = TgaccessModel::where($map)->find();
            $tname = TeacherModel::where('id', $res['tid'])->find();
            $remsg['thumb'] = $tname['thumb'];
            $remsg['tgname'] = $tname['name'] . '-' . $res['tgname'];
            return $remsg;
        }
    }

    public function bindclasses()
    {
        if (request()->isPost()) {
            $datacheck = TeachergroupModel::where(['status' => 1, 'astatus' => 1, 'schid' => Session::get('school_id')])->find();
            $data = input('post.');
            if ($datacheck['fcengnum'] == 0) {
                $map['cno'] = ['eq', $data['cno']];
            } else {
                $map['cno'] = ['eq', $data['cno']];
                $map['fcengid'] = ['eq', $data['fceng']];
            }
            db('classes' . Session::get('school_id'))->where($map)->update(['tgroupid' => $data['tgid']]);
            return 1;
        }
    }


//	public function exportresult()
//	{
//		$name = GetSchoolname(Session::get('school_id')) . "-分组匹配结果" . ".xls";
//		$header = ['姓名', '性别', '身份证号码', '所属教师组'];
//		$data = db('classes' . Session::get('school_id'))->order('cno')->select();
//		foreach ($data as $k => $v) {
//			$m['name'] = $v['name'];
//			$m['sex'] = $v['sex'];
//			$sfz = db('students' . Session::get('school_id'))->where('id', $v['id'])->value('id_num');
//			$m['sfz'] = ' 　' . $sfz . '';
//			$map = ['schid' => Session::get('school_id'), 'status' => 1, 'astatus' => 1];
//			$res = TeachergroupModel::where($map)->find();
//			$m['tname'] = $v['tgroupid'] + 1 . '班-' . Getbigtname($res['id'], $v['tgroupid']);
//			$arr[] = $m;
//		}
//		excelExport($name, $header, $arr);
//	}


    public function exportsingle()
    {
        $tgroupid = input('tgroupid');
        $classid = $tgroupid + 1;
        $name = GetSchoolname(Session::get('school_id')) . $classid . "班-分组匹配结果" . ".xls";
        $header = ['姓名', '性别', '身份证号码', '班级', '班主任'];
        $data = db('classes' . Session::get('school_id'))->where('tgroupid', $tgroupid)->select();
        foreach ($data as $k => $v) {
            $m['name'] = $v['name'];
            $m['sex'] = $v['sex'];
            $sfz = db('students' . Session::get('school_id'))->where('id', $v['id'])->value('id_num');
            $m['sfz'] = ' 　' . $sfz . '';
            $map = ['schid' => Session::get('school_id'), 'status' => 1, 'astatus' => 1];
            $res = TeachergroupModel::where($map)->find();
            $m['tname'] = $v['tgroupid'] + 1;
            $m['bzr'] = Getbigtname($res['id'], $v['tgroupid']);
            $arr[] = $m;
        }
        excelExport($name, $header, $arr);
    }


    public function exportresultfc()
    {
        $name = GetSchoolname(Session::get('school_id')) . "-分组匹配结果" . ".xls";
        $header = ['姓名', '性别', '身份证号码', '所属学生组', '所属教师组'];
        $data = db('classes' . Session::get('school_id'))->order('cno')->select();
        foreach ($data as $k => $v) {
            $m['name'] = $v['name'];
            $m['sex'] = $v['sex'];
            $sfz = db('students' . Session::get('school_id'))->where('id', $v['id'])->value('id_num');
            $m['sfz'] = ' 　' . $sfz . '';
            $m['cno'] = $v['cno'];
            $map = ['schid' => Session::get('school_id'), 'status' => 1, 'astatus' => 1];
            $res = TeachergroupModel::where($map)->find();
            $m['tname'] = $v['tgroupid'] + 1 . '班-' . Getbigtname($res['id'], $v['tgroupid']);
            $arr[] = $m;
        }
        excelExport($name, $header, $arr);
    }

    public function lockclass()
    {
        if (request()->isPost()) {
            $datas = input('post.');
            $data = ['cstatus' => 1];
            $where = ['schid' => $datas['schid']];
            $filed = ['cstatus'];
            TeachergroupModel::update($data, $where, $filed);
        }
    }


}
