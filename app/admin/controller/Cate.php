<?php
namespace app\admin\controller;
use think\Controller;

class Cate extends Common
{
    protected $beforeActionList = [
        // 'first',
        // 'second' =>  ['except'=>'hello'],
        'delsoncate'  =>  ['only'=>'del'],
    ];

    public function lst()
    {
        if (request()->isPost()) {
            $sorts=input('post.');
            foreach ($sorts as $k => $v) {
                db('cate')->update(['id'=>$k,'sort'=>$v]);
            }
            $this->success('更新排序成功',url('lst'));
            // dump(input('post.'));die;
            return;
        }
    	$data=db('cate')->order('sort asc')->select();
        $res=$this->sort($data);
    	$this->assign('res',$res);
        return view();
    }

    public function sort($data,$pid=0,$level=0)
    {
        static $arr=array();
        foreach ($data as $k => $v) {
            if($v['pid']==$pid){
                $v['level']=$level;
                $arr[]=$v;
                $this->sort($data,$v['id'],$level+1);
            }
        }
        return $arr;
    }

    public function add()
    {
    	if (request()->isPost()) {
    		$data=input('post.');
            // dump($data);die;
    		$res=db('cate')->insert($data);
    		if($res){
    			$this->success('添加栏目成功',url('lst'));
    		}else{
    			$this->error('添加栏目失败');
    		}
    		return;
    	}
        $data=db('cate')->order('sort asc')->select();
        $res=$this->sort($data);
        $this->assign('res',$res);
    	return view();
    }

    public function edit($id)
    {
       if (request()->isPost()) {
       	$data=input('post.');
       	$res=db('cate')->update($data,['id'=>input('id')]);
       	if($res){
    			$this->success('修改栏目成功',url('lst'));
    		}else{
    			$this->error('修改栏目失败');
    		}
       	return;
       }

        $data=db('cate')->order('sort asc')->select();
        $resfl=$this->sort($data);
        $this->assign('resfl',$resfl);


    	$res=db('cate')->find($id);
    	if(!$res){
    		$this->error('该管理员不存在！');
    	}
    	$this->assign('res',$res);
    	return view();
    }

    public function del()
    {
    	$res=db('cate')->delete(input('id'));
    	if($res){
    	       $this->success('删除栏目成功',url('lst'));
    		}else{
    			$this->error('删除栏目失败');
    		}
    }

    public function delsoncate()
    {
        $cateid=input('id');
        $data=db('cate')->select();
        $res=$this->_getchilrenid($data,$cateid);
        if($res){
            db('cate')->delete($res);
        }
        // dump($res);die;
    }

    public function _getchilrenid($data,$cateid)
    {
        static $arr=array();
        foreach ($data as $k => $v) {
            if($v['pid']==$cateid){
                $arr[]=$v['id'];
                $this->_getchilrenid($data,$v['id']);
            }
        }
        return $arr;
    }

}
