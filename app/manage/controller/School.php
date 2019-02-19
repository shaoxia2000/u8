<?php

namespace app\manage\controller;

use app\common\model\School as SchoolModel;
use app\common\model\Teacher as TeacherModel;
use think\Session;

class School extends Common
{
    /**
     * 学校管理列表首页
     */
    public function index()
    {
        $checkt = TeacherModel::where('schid', Session::get('school_id'))->find();
        if (!$checkt) {
            $this->error('教师数据尚未导入!', url('teacher/index', ['ac1' => 1]));
        }

        if (Session::get('school_id') != 0) {
            $checks = db('students' . Session::get('school_id'))->select();
            if ($checks->isEmpty()) {
                $this->error('学生数据尚未导入!', url('students/index', ['ac2' => 1]));
            }
        }


        $name = input('name');
        $name && $map['schname'] = ['like', '%' . $name . '%'];
        Session::has('area_id') && $map['areaid'] = ['eq', Session::get('area_id')];
        Session::has('school_id') != 0 && $map['schid'] = ['eq', Session::get('school_id')];
        $query = ['query' => request()->param()];
        $data = SchoolModel::where($map)->paginate($query);
        $groups = GetGroupnums();
        $this->assign(array('data' => $data, 'groups' => $groups));
        return view();
    }


}
