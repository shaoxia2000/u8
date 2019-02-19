<?php
namespace app\check\controller;

class DataCheck extends Common
{
    public function index()
    {
    	if(request()->isPost()){
    		session('last_post',input('post.schid/a'));
    		return view('result');
    	}else{
    		return view();
    	}
    }

}
