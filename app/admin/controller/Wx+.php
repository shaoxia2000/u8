<?php
namespace app\admin\controller;

use mikkle\tp_wechat\Wechat;
use think\Controller;
use think\paginator\driver\Bootstrap;
use think\session;

class Wx extends Common
{
    public function index()
    {
        $options = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', Session::get('schoolid'))->find();
        // dump($options);die;
        if (request()->isPost()) {
            if (input('do') == 'remove') {
                // 删除菜单
                $re = Wechat::menu($options)->deleteMenu();
                if ($re) {
                    // 删除成功
                    echo "<script>parent.replaceok('删除成功！');</script>";
                } else {
                    // 删除失败
                    echo "<script>parent.replaceFuck('删除失败！');</script>";
                }
            } else {
                $menu = urldecode(input('do'));
                $menu = json_decode($menu, true);
                $button['button'] = $menu;
                // dump($button);die;
                $re = Wechat::menu($options)->createMenu($button);
                if ($re) {
                    echo "<script>parent.replaceok('更新菜单成功！');</script>";
                } else {
                    echo "<script>parent.replaceFuck('更新菜单失败！');</script>";
                }
            }
            exit();
        }
        $menu = Wechat::menu($options)->getMenu();
        $this->assign('menu', $menu['menu']);
        return view();
    }

    public function setting()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $school_id = input('school_id');
            $res = db('wxsetting')->field('school_id')->where('school_id', Session::get('schoolid'))->find();
            if (!$res) {
                $kk = db('wxsetting')->insert($data);
                if ($kk) {
                    logs();
                    $this->success('添加成功');
                } else {
                    $this->error('添加失败');
                }
            } else {
                $kk = db('wxsetting')->where('school_id', $school_id)->update(['token' => $data['token'], 'appid' => $data['appid'], 'appsecret' => $data['appsecret'], 'encodingaeskey' => $data['encodingaeskey']]);
                if ($kk) {
                    logs();
                    $this->success('修改成功');
                } else {
                    $this->error('修改失败');
                }
            }

        }
        $res = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', Session::get('schoolid'))->find();
        $this->assign('res', $res);
        return view();
    }

    public function fs()
    {
        $options = db('wxsetting')->field('token,appid,appsecret,encodingaeskey')->where('school_id', Session::get('schoolid'))->find();
        // $re = Wechat::user($options)->getUserList();
        // $data = Wechat::user($options)->getUserBatchInfo($re['data']['openid']);
        // dump($data);die;
        // $this->assign('userlist', $data);
        // return view();

        $content = "5566";
        $data = array(
            "touser" => array(
                "oKIYi0gSd-crsN_1toe0qVfq5ysI",
                "oKIYi0tpABkd0iQtIWuBET8XmM90",
            ),
            "msgtype" => "text",
            "text" => array("content" => $content),

        );

        $data = json_encode($data);
        // dump($data);
        // $re = Wechat::Message($options)->sendMassMessage($data);

        dump($data);die;

    }
}
