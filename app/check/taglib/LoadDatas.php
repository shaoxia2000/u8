<?php
namespace app\check\taglib;

use think\template\TagLib;

class LoadDatas extends Taglib
{
    protected $tags = [
        'areas' => ['attr' => 'areaid', 'close' => 0],
    ];

    public function tagAreas($tag)
    {
        return file_get_contents(__DIR__ . DS . '..' . DS . 'boot' . DS . 'areaAndSchoolList.php');
    }

}
