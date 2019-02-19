<?php

namespace app\api\validate;

class StudentInfoValidate extends BaseValidate
{

    protected $rule = [
        'id' => 'require|isZnumber'
    ];


    protected $message = [
        'id.require' => '学校ID不得为空！'
    ];


}
