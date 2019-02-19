<?php
/**
 * Created by PhpStorm.
 * User: baidu
 * Date: 17/7/28
 * Time: 上午12:27
 */

namespace app\lib;

use think\Cache;

/**
 * Iauth相关
 * Class IAuth
 */
class IAuth
{

    /**
     * 设置密码
     * @param string $data
     * @return string
     */
    public static function setPassword($data)
    {
        return md5($data . config('app.password_pre_halt'));
    }

    /**
     * 生成每次请求的sign
     * @param array $data
     * @return string
     */
    public static function setSign($data = [])
    {
        // 1 按字段排序
        ksort($data);
        // 2拼接字符串数据  &
        $string = http_build_query($data);
        // 3通过aes来加密
        $string = (new Aes())->encrypt($string);
        return $string;
    }

    /**
     * 检查sign是否正常
     * @param array $data
     * @param $data
     * @return boolen
     */
    public static function checkSignPass($data)
    {
        //接收传过来的header中的加密过的sign字符串，并进行解密！
        $str = (new Aes())->decrypt($data['sign']);
        //如果没有则直接返回false
        if (empty($str)) {
            return false;
        }

        // 将解密后did=xx&app_type=3这样的字符串转换成数组
        parse_str($str, $arr);
        //如果不是数组、或者数组里面appid为空、或者解析出来数组里面的appid不等于传过来的$data['appid']我们都认为它不通过验证
        if (!is_array($arr) || empty($arr['appid'])
            || $arr['appid'] != $data['appid']
        ) {
            return false;
        }

        if (!is_array($arr) || empty($arr['version'])
            || $arr['version'] != $data['version']
        ) {
            return false;
        }
        if (!config('app_debug')) {
            if ((time() - ceil($arr['time'] / 1000)) > config('app.app_sign_time')) {
                return false;
            }
             //唯一性判定，如果从缓存中能获取到说明已经被请求过了，return false否则继续
            if (Cache::get($data['sign'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * 设置登录的token  - 唯一性的
     * @param string $phone
     * @return string
     */
    public static function setAppLoginToken($phone = '')
    {
        $str = md5(uniqid(md5(microtime(true)), true));
        $str = sha1($str . $phone);
        return $str;
    }

}