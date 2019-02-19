<?php

namespace app\push\controller;

use think\worker\Server;
use Workerman\Lib\Timer;


class Worker extends Server
{
    protected $socket = 'websocket://0.0.0.0:2888';

    /**
     * 收到信息
     * @Author: 296720094@qq.com chenning
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        $connection->send($data);
    }

    /**
     * 当连接建立时触发的回调函数
     * @Author: 296720094@qq.com chenning
     * @param $connection
     */
    public function onConnect($connection)
    {

    }

    /**
     * 当连接断开时触发的回调函数
     * @Author: 296720094@qq.com chenning
     * @param $connection
     */
    public function onClose($connection)
    {

    }

    /**
     * 当客户端的连接上发生错误时触发
     * @Author: 296720094@qq.com chenning
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }


    /**
     * 每个进程启动
     * @Author: 296720094@qq.com chenning
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
        Timer::add(0.5, function () use ($worker) {
            $data = db('wxuser')->select();
            foreach ($worker->connections as $connection) {
                $connection->send(json_encode($data));
            }
        });
    }
}
