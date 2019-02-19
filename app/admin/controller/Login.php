<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
class Login extends Controller
{
    public function index()
    {
        if (request()->isPost()) {
            $this->check(input('code'));
            $data = input('post.');
            $result = $this->login($data);
            if ($result == 2) {
                //存储日志
                $this->success('登陆成功', url('index/index'), 20);
            }
            if ($result == 1) {
                $this->error('用户名不存在');
            }
            if ($result == 3) {
                $this->error('密码错误');
            }

        }
        return view();

    }

    public function login($data)
    {
        $res = db('admin')->where('name', $data['name'])->find();
        if ($res) {
            if ($res['password'] == md5($data['password'])) {
                session('name', $res['name']);
                session('id', $res['id']);
                session('schoolid', $res['school_id']);
                
                return 2; //用户名密码正确情况
                
            } else {
                return 3; //密码错误情况
            }
        } else {
            return 1; //用户名不存在情况
        }
    }

    // 验证码检测
    public function check($code = '')
    {
        if (!captcha_check($code)) {
            $this->error('验证码错误');
        } else {
            return true;
        }
    }

}
