<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/6/11
 * Time: 17:05
 */

namespace app\manage\controller;

use app\common\model\Teacher as TeacherModel;
use app\common\model\Teachergroup as TeachergroupModel;
use app\common\model\Tfaccess as TfaccessModel;
use app\common\model\Tgaccess as TgaccessModel;
use think\Session;


class Assignresult extends Common
{
    public function index()
    {
        $name = input('name');
        $name && $map['tgroupname'] = ['like', '%' . $name . '%'];
	    $map['schtype'] = ['neq', 3];
        $schtype = input('schtype');
        $schtype && $map['schtype'] = ['eq', $schtype];
        $map['status'] = ['eq', 1];
        $map['astatus'] = ['eq', 1];
        $map['gstatus'] = ['eq', 1];
        $map['areaid'] = ['eq', Session::get('area_id')];
        $data = TeachergroupModel::where($map)->paginate();
        if (count($data) == 0) {
            $this->error('不存在分组结果！', url('students/index', ['ac2' => 1]));
        }
        $datanmus = TeachergroupModel::count();
        $this->assign(array('data' => $data, 'datanums' => $datanmus));
        return view();
    }

    /**
     * 设置教师组首页
     */
    public function view()
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
            $list[$a]['dd'] = db('classes' . $schid)->where('cno', $a + 1)->select();
        }
        for ($b = 0; $b < $res['fcengnum']; $b++) {
            $maps['fid'] = ['eq', $b];
            $maps['gid'] = ['eq', $gid];
            $listfc[$b]['bb'] = $b;
            $listfc[$b]['cc'] = TfaccessModel::where($maps)->select();
            $listfc[$b]['ee'] = TfaccessModel::where($maps)->count();
        }
        $where['schid'] = ['eq', $schid];
        $tdata = TeacherModel::where($where)->select();
        $this->assign(array('list' => $list, 'listfc' => $listfc, 'gid' => $gid, 'tdata' => $tdata, 'resfc' => $resfc, 'schid' => $schid, 'fbnum' => $res['tnum']));
        return view();
    }


    public function save_status()
    {
        $id = $_POST['id'];
        $xstatus = TeachergroupModel::getFieldById($id, 'xstatus');
        if ($xstatus == 1) {
            $data = ['xstatus' => 0];
            $where['id'] = ['eq', $id];
            $field = ['xstatus'];
            TeachergroupModel::update($data, $where, $field);
        } else {
            $data = ['xstatus' => 1];
            $where['id'] = ['eq', $id];
            $field = ['xstatus'];
            TeachergroupModel::update($data, $where, $field);
        }
        return 1;

    }

}