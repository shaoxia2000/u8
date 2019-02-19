<?php

namespace app\common\lib\exception;


class StudentInfoException extends BaseException
{
    public $code = 404;
    public $msg = '指定的学校不存在，请检查参数';
    public $errorCode = 50000;
}