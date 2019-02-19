<?php
use think\Route;
Route::domain('www.test-api.com', 'api');
Route::post('user', 'user/login');
//获取验证码
Route::get('code/:time/:token/:username/:is_exist', 'code/getcode');

return [
    //主站规则
    'news/:id' => 'index/index/info',
    //api规则
    'user/:id' => 'user/index',
];
