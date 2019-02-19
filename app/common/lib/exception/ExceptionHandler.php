<?php

namespace app\common\lib\exception;

use think\exception\Handle;
use think\Log;
use think\Request;

class ExceptionHandler extends Handle
{

    private $code;
    private $msg;
    private $errorCode;
    //还需要返回当前请求的url路径地址
    //框架里面所抛出的异常都由以下这个render来渲染
    public function render(\Exception $e)
    {
        if ($e instanceof BaseException) {
            //如果是自定义的异常
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;

        } else {
            if (config('app_debug')) {
                //return default error page
                return parent::render($e);
            } else {
                $this->code = 500;
                $this->msg = '服务器内部错误~_~';
                $this->errorCode = 999;
                $this->recordErrorLog($e);
            }

        }
        $request = Request::instance();
        $result = [
            'msg'         => $this->msg,
            'error_code'  => $this->errorCode,
            'request_url' => $request->url(),
        ];
        return json($result, $this->code);
    }

    private function recordErrorLog(\Exception $e)
    {
        Log::init([
            'type'  => 'File',
            'path'  => LOG_PATH,
            'level' => ['error'],
        ]);
        //调用tp5的日志record方法，里面有两个参数，1.错误的具体信息 2.错误级别这里定义为error(只有错误信息才被记录)
        Log::record($e->getMessage(), 'error');
    }
}
