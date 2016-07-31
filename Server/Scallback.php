<?php
/**
 * Created by PhpStorm.
 * User: chenhong
 * Date: 16/7/22
 * Time: 下午10:34
 */

namespace Swoole\Server;


interface Scallback
{
    //服务启动
    public function onStart();

    //服务连接
    public function onConnect();

    //接收数据
    public function onReceive();

    //连接关闭
    public function onClose();

    //服务关闭
    public function onShutdown();
}