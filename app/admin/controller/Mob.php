<?php
namespace app\admin\controller;
use think\Controller;
use think\session;
class Mob extends Common
{
	public function index()
	{
		$res = db('instructions')->order('time desc')->select();
		$this->assign('list',$res);
        return view();
    }
    public function add()
    {
    	if (request()->isPost()) 
    	{
            $data = input('post.');
            $file = request()->file('image');               
            if($file)
            {
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                if ($info) 
                {
                    $thum = 'uploads' . '/' . $info->getSaveName();
                    $thum = str_replace("\\","/",$thum);
                    $data['img'] = $thum;
                }
            }
            $data['time'] = date('Y-m-d H:i:s');
            $res = db('instructions')->insert($data);            
            if ($res) 
            {
                $this->success('添加说明成功', url('index'));
            } else 
            {
                $this->error('添加说明失败');
            }
        }
    	return view();
    }
    public function edit()
    {
    	if (request()->isPost()) 
    	{
            $data = input('post.');
            $file = request()->file('image');                
            if($file)
            {
                $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                if ($info) 
                {
                    $thum = 'uploads' . '/' . $info->getSaveName();
                    $thum = str_replace("\\","/",$thum);
                    $data['img'] = $thum;
                }
            }
            $data['time'] = date('Y-m-d H:i:s');
            $res = db('instructions')->where('id', input('id'))->update($data);
            if ($res) 
            {
                $this->success('修改说明成功', url('index'));
            }else 
            {
                $this->error('修改说明失败');
            }
            return;
        }
        $res = db('instructions')->find(input('id'));
        if (!$res) {
            $this->error('该说明不存在！');
        }
        $this->assign('res', $res);
        return view();
    }
    public function del()
    {
        $res = db('instructions')->delete(input('id'));
        if ($res) {
            $this->success('删除说明成功', url('index'));
        } else {
            $this->error('删除说明失败');
        }
    }
}