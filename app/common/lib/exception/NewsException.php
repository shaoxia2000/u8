<?php

namespace app\common\lib\exception;


class NewsException extends BaseException
{
    public $code = 404;
    public $msg = '指定的新闻不存在，请检查参数';
    public $errorCode = 50000;
}