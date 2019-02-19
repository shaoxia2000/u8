<?php

namespace app\common\lib\exception;


class ApiException extends BaseException
{
    public $code = 401;
    public $msg = 'sign不存在';
    public $errorCode = 50000;
}