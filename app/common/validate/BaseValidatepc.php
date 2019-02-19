<?php

namespace app\common\validate;

use think\Request;
use think\Validate;
use app\common\lib\exception\ParameterException;

class BaseValidatepc extends Validate
{

    public function gocheck()
    {
        $request = Request::instance();
        $params = $request->param();
        if (!$this->batch()->check($params)) {
                    $msg = is_array($this->error) ? implode(
                        ';', $this->error) : $this->error;
        }else{
        	$msg=1;
        }
	    return $msg;
   
    }

    protected function isZnumber($value, $rule = '', $data = '', $field = '')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        }
        return $field . '必须是正整数';
    }

    protected function isNotEmpty($value, $rule = '', $data = '', $field = '')
    {
        if (empty($value)) {
            return $field . '不允许为空';
        } else {
            return true;
        }
    }


}
