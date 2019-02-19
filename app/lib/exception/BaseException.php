<?php
namespace app\lib\exception;

use think\Exception;

class BaseException extends Exception
{
    public $code = 400; //状态码
    public $msg = '参数错误'; //错误具体信息
    public $errorCode = 10000; //自定义的错误码

    public function __construct($params = [])
    {
        //防御性代码，用来判断外部传过来的参数是不是一个数组，如果不是就直接打回去，是的话就执行
        if (!is_array($params)) {
            return;
            // throw new Exception('参数必须是数组');
        }
        if (array_key_exists('code', $params)) {
            $this->code = $params['code'];
        }
        if (array_key_exists('msg', $params)) {
            $this->msg = $params['msg'];
        }
        if (array_key_exists('errorCode', $params)) {
            $this->errorCode = $params['errorCode'];
        }
    }
}
