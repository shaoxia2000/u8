<?php
/**
 * Created by shaoxia2018.
 * Date: 2018/6/5
 * Time: 14:26
 */

namespace app\common\model;


class Teachergroup extends Base
{
    protected $field = true;
    protected static function init()
    {
        Teachergroup::event('before_delete', function ($teachergroup) {
            Tgaccess::where('gid', $teachergroup->id)->delete();
            Tfaccess::where('gid', $teachergroup->id)->delete();
        });
    }
}