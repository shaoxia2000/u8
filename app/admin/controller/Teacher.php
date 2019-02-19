<?php
namespace app\admin\controller;

use think\Controller;

header("content-type:text/html;charset=utf-8");
class Teacher extends Common
{
    public function index()
    {

        if (request()->isGet()) {
            // 关键字
            $find = input('get.find');
            if (!empty($find)) {
                $find = trim($find);
            } else {
                $find = '';
            }
            // 下拉菜单

            if (session("schoolid") != 0) {
                $findxq = session("schoolid");
            } else {
                $findxq = input('get.findxq');
                if (empty($findxq)) {
                    $findxq = '';
                }
            }

        } else {

            $find = '';
            if (session("schoolid") != 0) {
                $findxq = session("schoolid");
            } else {
                $findxq = '';
            }
        }

        // echo $findxq;exit();
        // echo $find;

        if (!empty($find) and !empty($findxq)) {
            $data = db('Teacher')->where('name', 'like', '%' . $find . '%')->where('sch', $findxq)->where('status', 0)->paginate(20, false, ['query' => request()->param()]);

        } elseif (!empty($find) and empty($findxq)) {
            $data = db('Teacher')->where('name', 'like', '%' . $find . '%')->where('status', 0)->paginate(20, false, ['query' => request()->param()]);

        } elseif (empty($find) and !empty($findxq)) {
            $data = db('Teacher')->where('sch', $findxq)->where('status', 0)->paginate(20, false, ['query' => request()->param()]);

        } else {
            $data = db('Teacher')->where('status', 0)->paginate(20);
        }

        $datatype = array();
        foreach ($data as $key => $value) {

            $arr = db('school')->where('id', '=', $value['sch'])->find();
            $datatype[$key]['sch'] = $arr['name'];
            if ($value['sex'] == 1) {
                $datatype[$key]['sex'] = '男';
            } else {
                $datatype[$key]['sex'] = '女';
            }
        }

        if (empty($data)) {
            $page = '';
        } else {
            $page = $data->render();
        }

        $this->assign('data', $data);
        $this->assign('page', $page);
        $this->assign('datatype', $datatype);
        $this->assign('find', $find);
        $this->assign('findxq', $findxq);

        // $res = db('school')->select();

        $sch = session("schoolid");
        if ($sch != 0) {
            $res = db('school')->where('id', $sch)->where('status', 0)->select();
        } else {
            $res = db('school')->where('status', 0)->select();
        }

        $this->assign('res', $res);

        return $this->fetch();
    }

    public function add()
    {
        if (request()->isPost()) {

            // $data = input('post.');
            // $adata = array();
            // $adata['name'] = trim($data['name']);
            // $adata['sch'] = $data['sch'];
            // $adata['sex'] = $data['sex'];
            // $adata['duty'] = $data['duty'];
            // $adata['remark'] = $data['remark'];
            // $adata['school_id'] = session('schoolid');

            // $adata['school'] = $data['school'];
            // $adata['age'] = $data['age'];
            // $adata['jobtitle'] = $data['jobtitle'];
            // $adata['teachage'] = $data['teachage'];
            // $adata['honor'] = $data['honor'];
            // $adata['subject'] = $data['subject'];


            $data = input('post.');
            $adata = array();
            $adata['name'] = trim($data['name']);
            $adata['sch'] = $data['sch'];
            $adata['sex'] = $data['sex'];
            $adata['duty'] = trim($data['duty']);
            $adata['remark'] = trim($data['remark']);
            $adata['school_id'] = session('schoolid');  //管理员学校

            $adata['school'] = trim($data['school']); //毕业学校
            $adata['age'] = trim($data['age']);
            $adata['jobtitle'] = trim($data['jobtitle']);
            $adata['tel'] = trim($data['tel']);
            $adata['teachage'] = trim($data['teachage']);
            $adata['honor'] = trim($data['honor']);
            $adata['subject'] = trim($data['subject']);


            //判断如果有图片上传的话
            if ($_FILES['thumb']['tmp_name']) {

                $file = request()->file('thumb');
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                if ($info) {
                    $thum = 'uploads' . '/' . $info->getSaveName();

                    $thum = str_replace("\/","/",$thum);

                    $adata['thumb'] = $thum;
                }
            }

            $rt = db('Teacher')->where('name', $adata['name'])
                ->where('school_id', $adata['school_id'])
                ->where('sex', $adata['sex'])
                ->where('status', 0)
                ->find();

            // echo db("Teacher")->getLastSql();exit();

            //判断老师姓名是否重复
            if (!empty($rt)) {
                $this->error('教师姓名重复,请区分重名教师！');
            } else {
                $res = db('Teacher')->insert($adata);
                // 新增用户密码 admin表
                $s['name'] = trim($data['name']);
                $s['password'] = md5(trim($data['password']));
                $s['school_id'] = session('schoolid');
                $ress = db('Admin')->insert($s);
                $userId = db('Admin')->getLastInsID();

                // 新增关联 auth_group_access表
                $d['group_id'] = 14;
                $d['uid'] = $userId;
                $ress = db('Auth_group_access')->insert($d);

                if ($res) {
                    if ($ress) {
                        logs();
                        $this->success('添加教师信息成功', url('index'));
                    } else {

                        $this->error('添加教师信息失败');
                    }
                } else {

                    $this->error('添加教师信息失败');
                }
            }
            return;
        }

        if (session('schoolid') != 0) {
            $res = db('school')->where('id', session('schoolid'))->select();
        } else {
            $res = db('school')->select();
        }
        $this->assign('res', $res);
        return view();
    }

    public function edit($id)
    {

        if (request()->isPost()) {
            $data = input('post.');
            //判断如果有图片上传的话
            if ($_FILES['thumb']['tmp_name']) {

                $file = request()->file('thumb');
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                if ($info) {
                    $thum = 'uploads' . '/' . $info->getSaveName();
                    $thum = str_replace("\\","/",$thum);
                    $data['thumb'] = $thum;
                }
                $res = db('Teacher')->where('id', $id)->update(['name' => $data['name'], 'sch' => $data['sch'], 'sex' => $data['sex'], 'duty' => $data['duty'], 'remark' => $data['remark'], 'thumb' => $data['thumb'], 'jobtitle' => $data['jobtitle'], 'school' => $data['school'], 'age' => $data['age'],'tel' => $data['tel'], 'teachage' => $data['teachage'], 'honor' => $data['honor'], 'subject' => $data['subject']]);
            } else {
                $res = db('Teacher')->where('id', $id)->update(['name' => $data['name'], 'sch' => $data['sch'], 'sex' => $data['sex'], 'duty' => $data['duty'], 'remark' => $data['remark'], 'jobtitle' => $data['jobtitle'], 'school' => $data['school'], 'age' => $data['age'],'tel' => $data['tel'], 'teachage' => $data['teachage'], 'honor' => $data['honor'], 'subject' => $data['subject']]);
            }

            if ($res) {
                logs();
                $this->success('修改教师信息成功', url('index'));
            } else {
                $this->error('修改教师信息失败');
            }
            return;
        }

        $res = db('Teacher')->find($id);
        if (!$res) {
            $this->error('该教师不存在！');
        }
        $this->assign('res', $res);

        $gid = db('Teacher')->where('id', $id)->find();
        $this->assign('gid', $gid['sch']);

        // $resfl = db('school')->select();
        if (session('schoolid') != 0) {
            $resfl = db('school')->where('id', session('schoolid'))->select();
        } else {
            $resfl = db('school')->select();
        }
        $this->assign('resfl', $resfl);
        return view();
    }

    public function del($id)
    {
        $res = db('Teacher')->where('id', $id)->update(['status' => 1]);
        if ($res) {
            logs();
            $this->success('删除教师信息成功', url('index'));
        } else {
            $this->error('删除教师信息失败');
        }
    }

}
