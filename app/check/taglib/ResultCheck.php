<?php
namespace app\check\taglib;

use think\template\TagLib;

class ResultCheck extends Taglib
{
    protected $tags = [
        'show' => ['attr' => 'sid', 'close' => 0],
    ];

    public function tagShow($tag)
    {
        return file_get_contents(__DIR__ . DS . '..' . DS . 'boot' . DS . 'resultCheck.php');
    }

}

