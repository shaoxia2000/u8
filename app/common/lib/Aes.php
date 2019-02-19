<?php

namespace app\lib;

use think\Config;

/**
 * aes 加密 解密类库
 * @by singwa
 * Class Aes
 * @package app\common\lib
 */
class Aes
{
    /**
     * 加密
     * @param String input 加密的字符串
     * @param String key   解密的key
     * @return HexString
     */
    public function encrypt($input = '')
    {
        $key = Config::get('app.aeskey');
//        $data['iv'] = base64_encode(substr('fdakinel;injajdji', 0, 16));
        $data['iv'] = base64_encode(substr($key, 0, 16));
        $data['value'] = openssl_encrypt($input, 'AES-256-CBC', $key, 0, base64_decode($data['iv']));
        $encrypt = base64_encode(json_encode($data));
        return $encrypt;
    }


    /**
     * 解密
     * @param String input 解密的字符串
     * @param String key   解密的key
     * @return String
     */

    public function decrypt($encrypt)
    {
        $key = Config::get('app.aeskey');
        $encrypt = json_decode(base64_decode($encrypt), true);
        $iv = base64_decode($encrypt['iv']);
        $decrypt = openssl_decrypt($encrypt['value'], 'AES-256-CBC', $key, 0, $iv);
        return $decrypt;

    }
}