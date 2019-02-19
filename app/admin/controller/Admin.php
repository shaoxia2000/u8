<?php

namespace app\admin\controller;

use app\common\model\Admin as AdminModel;
use app\common\model\Authgroup as AuthgroupModel;
use app\common\validate\AdminUser;


class Admin extends Common
{
    public function lst()
    {
        $name = input('name');
        $name && $map['name'] = ['like', '%' . $name . '%'];
        $query = ['query' => request()->param()];
        $data = AdminModel::where($map)->paginate($query);
        $res = $this->sort($data);
        $this->assign(array('data' => $data, 'res' => $res));
        return view();
    }

// 向结果集里面增加一个数组属性并返回 ，比较经典
    public function sort($data)
    {
        $auth = new Auth();
        static $arr = array();
        foreach ($data as $k => $v) {
            $gtitle = $auth->getGroups($v['id']);
            $groupname = $gtitle[0]['title'];
            $v['groupname'] = $groupname;
            $arr[] = $v;
        }
        return $arr;
    }


    public function add()
    {
        if (request()->isPost()) {
            $data = input('post.');
            if ((new AdminUser())->gocheck() != 1) {
                $this->error((new AdminUser())->gocheck());
            }
            $result = AdminModel::CreateAdmin($data);
            if ($result) {
                $this->success('新增用户成功');
            } else {
                $this->error('新增用户失败');
            }
            return;
        }
        $res = AuthgroupModel::select();
        $this->assign('res', $res);
        $ressc = db('school')->select();
        $this->assign('ressc', $ressc);
        return view();
    }

    public function edit($id)
    {
        if (request()->isPost()) {
            $data = input('post.');
            // dump($data);die;
            if ($data['password']) {
                $data['password'] = md5($data['password']);
            }
            $res = db('admin')->where('id', $id)->update(['name' => $data['name'], 'password' => $data['password'], 'school_id' => $data['school_id']]);
            if ($res) {
                db('auth_group_access')->where('uid', $id)->update(['group_id' => $data['group_id']]);
                logs();
                $this->success('修改管理员成功', url('lst'));
            } else {
                $this->error('修改管理员失败');
            }
            return;
        }

        $res = db('admin')->field('id,name,school_id')->find($id);
        if (!$res) {
            $this->error('该管理员不存在！');
        }
        $this->assign('res', $res);

        $gid = db('auth_group_access')->where('uid', $id)->find();
        $this->assign('gid', $gid['group_id']);

        $ressc = db('school')->select();
        $this->assign('ressc', $ressc);

        $resfl = db('auth_group')->select();
        $this->assign('resfl', $resfl);
        return view();
    }

    public function del($id)
    {
        $where['uid'] = $id;
        $re = db("auth_group_access")->where($where)->delete();
        $res = db('admin')->delete($id);
        if ($re) {
            logs();
            if ($res) {
                logs();
                $this->success('删除管理员成功', url('lst'));
            } else {
                $this->error('删除管理员失败');
            }
        } else {
            $this->error('删除管理员失败');
        }
    }

    public function logout()
    {
        //存储日志他人
        logs();
        session(null);
        $this->success('退出登录成功', url('login/index'));
    }

}
