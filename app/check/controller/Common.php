<?php
namespace app\check\controller;

use think\Controller;
use think\View;

class Common extends Controller
{
    public function _initialize()
    {
      	if(!session('has_check')){
          if(input('param.token') != 'wanghaoyuanziqi'){
			exit();
          }else{
          	session('has_check','1');
          }
        }
        View::share(['user_name' => '市直属']);
    }
}
