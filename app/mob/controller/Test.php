<?php
namespace app\mob\controller;
use think\Cache;
use think\Controller;
use think\session;
use think\Request;
class Test extends Controller
{
 public function index($areaid,$type)
    {
        $a = $this->togeturl("http://bm.dqedu.net/wx/getAccessToken.php?aid=10");
   var_dump($a);
 }
  function togeturl($url)
    {
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $a = curl_exec($ch);
    $error = curl_error($ch);
    var_dump($error);
        return $a;
    }
}