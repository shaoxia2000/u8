<?php
namespace app\admin\controller;

use think\cache\driver\Redis;

class Authrule extends Common
{

    protected $beforeActionList = [
        // 'first',
        // 'second' =>  ['except'=>'hello'],
        'delsoncate' => ['only' => 'del'],
    ];

    public function lst()
    {
        if (request()->isPost()) {
            $sorts = input('post.');
            foreach ($sorts as $k => $v) {
                db('auth_rule')->update(['id' => $k, 'sort' => $v]);
            }
            $redis = new Redis(); //先删除redis缓存
            $redis->rm('types');
            $this->success('更新排序成功', url('lst'));
            return;
        }
        $data = $this->types(); //获取redis缓存返回的数据
        $res = $this->sort($data);
        $this->assign('res', $res);
        return view();
    }

    public function sort($data, $pid = 0, $level = 0)
    {
        static $arr = array();
        foreach ($data as $k => $v) {
            if ($v['pid'] == $pid) {
                $v['level'] = $level;
                $arr[] = $v;
                $this->sort($data, $v['id'], $level + 1);
            }
        }
        return $arr;
    }

    public function types()
    {
        $redis = new Redis();
        // $redis->rm('types');exit;
        if (!$redis->get('types')) {
            $data = db('auth_rule')->order('sort asc')->select();
            $redis->set('types', $data); //添加进redis缓存
            return $data;
        } else {
            return $redis->get('types'); //获取redis缓存
        }

    }

    public function add()
    {
        if (request()->isPost()) {
            $redis = new Redis(); //先删除redis缓存
            $redis->rm('types');
            $data = input('post.');
            $plevel = db('auth_rule')->where('id', $data['pid'])->field('level')->find();
            if ($plevel) {
                $data['level'] = $plevel['level'] + 1;
            } else {
                $data['level'] = 0;
            }
            $add = db('auth_rule')->insert($data);
            if ($add) {
                logs();
                $this->success('添加权限成功！', url('lst'));
            } else {
                $this->error('添加权限失败！');
            }
            return;
        }
        $data = db('auth_rule')->order('sort asc')->select();
        $res = $this->sort($data);
        $this->assign('res', $res);
        return view();
    }

    public function edit()
    {
        if (request()->isPost()) {
            $redis = new Redis();
            $redis->rm('types');
            $data = input('post.');
            $plevel = db('auth_rule')->where('id', $data['pid'])->field('level')->find();
            if ($plevel) {
                $data['level'] = $plevel['level'] + 1;
            } else {
                $data['level'] = 0;
            }
            $res = db('auth_rule')->update($data, ['id' => input('id')]);
            if ($res) {
                logs();
                $this->success('修改权限成功', url('lst'));
            } else {
                $this->error('修改权限失败');
            }
            return;
        }

        $data = db('auth_rule')->order('sort asc')->select();
        $resfl = $this->sort($data);
        $this->assign('resfl', $resfl);

        $res = db('auth_rule')->find(input('id'));
        if (!$res) {
            $this->error('该权限不存在！');
        }
        $this->assign('res', $res);
        return view();
    }

    public function del()
    {
        $res = db('auth_rule')->delete(input('id'));
        if ($res) {
            logs();
            $redis = new Redis();
            $redis->rm('types');
            $this->success('删除权限成功', url('lst'));
        } else {
            $this->error('删除权限失败');
        }
    }

    public function delsoncate()
    {
        $cateid = input('id');
        $data = db('auth_rule')->select();
        $res = $this->_getchilrenid($data, $cateid);
        if ($res) {
            db('auth_rule')->delete($res);
        }
        // dump($res);die;
    }

    public function _getchilrenid($data, $cateid)
    {
        static $arr = array();
        foreach ($data as $k => $v) {
            if ($v['pid'] == $cateid) {
                $arr[] = $v['id'];
                $this->_getchilrenid($data, $v['id']);
            }
        }
        return $arr;
    }

}
