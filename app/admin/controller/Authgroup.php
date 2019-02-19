<?php
namespace app\admin\controller;
use think\Controller;

class Authgroup extends Common
{

    public function lst()
    {
        $res=db('auth_group')->paginate(50);
    	$this->assign('res',$res);
        return view();
    }

    public function add()
    {
    	if (request()->isPost()) {
    		$data=input('post.');
        if($data['rules']){
          $data['rules']=implode(',',$data['rules']);
        }
    		$res=db('auth_group')->insert($data);
    		if($res){
          logs();
    			$this->success('添加用户组成功',url('lst'));
    		}else{
    			$this->error('添加用户组失败');
    		}
    		return;
    	}
        $data=db('auth_rule')->order('sort asc')->select();
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
                $v['dataid']=$this->getparentid($v['id']);
                $arr[]=$v;
                $this->sort($data,$v['id'],$level+1);
            }
        }
        return $arr;
    }

    public function edit($id)
    {
       if (request()->isPost()) {
       	$data=input('post.');
       	// dump($data);die;
       	$_data=array();
       	foreach ($data as $k => $v) {
       		$_data[]=$k;
       	}
       	if(!in_array('status',$_data)) {
       		$data['status']=0;
       	}

        if($data['rules']){
          $data['rules']=implode(',',$data['rules']);
        }

       	$res=db('auth_group')->update($data);
       	if($res){
          logs();
    			$this->success('修改用户组成功',url('lst'));
    		}else{
    			$this->error('修改用户组失败');
    		}
       	return;
       }


    	$res=db('auth_group')->field('id,title,status,rules,isshow')->find($id);
    	if(!$res){
    		$this->error('该用户组不存在！');
    	}
    	$this->assign('res',$res);


      $data=db('auth_rule')->order('sort asc')->select();
      $resfl=$this->sort($data);
      $this->assign('resfl',$resfl);
    	return view();
    }

    public function del()
    {
        $res=db('auth_group')->delete(input('id'));
      	$res_ac=db('auth_group_access')->where('group_id',input('id'))->delete();
        //删除组下人员
      	if($res){
            logs();
            $this->success('删除用户组成功',url('lst'));
  		}else{
  			$this->error('删除用户组失败');
  		}
    }

    public function getparentid($id)
    {
      $res=db('auth_rule')->select();
      return $this->_getparentid($res,$id,True);
    }


    public function _getparentid($data,$cateid,$clear=False)
    {
        static $arr=array();
        if($clear){
          $arr=array();
        }
        foreach ($data as $k => $v) {
            if($v['id']==$cateid){
                $arr[]=$v['id'];
                $this->_getparentid($data,$v['pid'],False);
            }
        }
        asort($arr);
        // dump($arr);die;
        $arrStr=implode("-",$arr);
        return $arrStr;
    }



}
