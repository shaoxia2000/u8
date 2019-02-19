<?php

namespace app\manage\controller;

use think\Cache;

class Debug extends Common
{
    public function index()
    {
        ini_set('memory_limit', '-1');
        $yearmonth = str_replace('-', '', date('Y-m'));
        $date = date('d');
        $domain = request()->domain();
        $file = ROOT_PATH . 'log/' . $yearmonth . '/' . $date . '.log';
        if (file_exists($file)) {
            $data = file($file);
            foreach ($data as $v) {
                $datanew[] = trim($v);
            }
            $datanews = array_filter($datanew);//过滤为空的数组值
            $line = array_slice($datanews, -4, 4);
        }
        $this->assign(array('domain' => $domain, 'line' => $line));
        return view();
    }

    public function delfile()
    {
        $yearmonth = str_replace('-', '', date('Y-m'));
        $date = date('d');
        $file = ROOT_PATH . 'log/' . $yearmonth . '/' . $date . '.log';
        unlink($file);
    }

    public function deldir()
    {
        Cache::clear();//清除模版缓存 不删除cache目录
        array_map('unlink', glob(TEMP_PATH . '/*.php'));
        array_map( 'unlink', glob( TEMP_PATH.DS.'.php' ) );
        rmdir(TEMP_PATH);
    }


}
