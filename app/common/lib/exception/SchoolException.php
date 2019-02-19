<?php

namespace app\common\lib\exception;


class SchoolException extends BaseException
{
    public $code = 401;
    public $msg = '学校数据不存在';
    public $errorCode = 60000;
}