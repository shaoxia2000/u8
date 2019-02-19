<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/4/25
 * Time: 20:51
 */

namespace app\index\controller;

use think\Request;

class Cajax
{
    public function upclass()
    {
        if (Request::instance()->isAjax()) {
            $data = input('post.');
            db($data['classesdb'])->where(['taskid' => $data['taskid'], "name" => $data['sedname']])->update(['jhnum' => 1]);
        }

    }


    public function upclasszero()
    {
        if (Request::instance()->isAjax()) {
            $data = input('post.');
            db($data['classesdb'])->where(['taskid' => $data['taskid']])->update(['jhnum' => 0]);
        }

    }

    public function bindteacher()
    {
        if (Request::instance()->isAjax()) {
            $data = input('post.');
            db($data['classesdb'])->where(['taskid' => $data['taskid'], 'cno' => $data['cno']])->update(['teacherid' => $data['fromid']]);
        }

    }
}