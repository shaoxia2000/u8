<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;

class Common extends Controller
{
    public function _initialize()
    {
        if (!session('id') || !session('name')) {
            $this->error('你尚未登录！', url('login/index'));
        }

        $auth = new Auth();
        $request = REQUEST::instance();
        $con = $request->controller();
        $action = $request->action();
        $name = $con . '/' . $action;
        $notcheck = array('Index/index', 'Admin/logout', 'Login/index');
        if (session('id') != 20) {
            if (!in_array($name, $notcheck)) {

                if (!$auth->check($name, session('id'))) {
                    $this->error('没有权限访问此版块', url('index/index'), 1);
                }
            }

        }

    }

}
