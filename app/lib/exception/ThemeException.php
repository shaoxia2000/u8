<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/22/022
 * Time: 23:15
 */

namespace app\lib\exception;


class ThemeException extends BaseException
{
    public $code = 404;
    public $msg = '指定的主题不存在，请检查主题ID';
    public $errorCode = 30000;
}