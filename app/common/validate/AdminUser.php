<?php

namespace app\common\validate;

class AdminUser extends BaseValidatepc
{
    protected $rule = [
        'name' => 'require|max:20',
        'password' => 'require|max:20'
    ];
	
	protected $message = [
		'name.max' => '用户名称长度不能超过20',
		'name.password' => '密码长度不能超过20'
	];
}
