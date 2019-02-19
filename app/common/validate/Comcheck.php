<?php

namespace app\common\validate;

class Comcheck extends BaseValidate
{

    protected $rule = [
        'id' => 'require|isZnumber'
    ];


    protected $message = [
        'id.require' => 'ID不得为空！'
    ];


}
