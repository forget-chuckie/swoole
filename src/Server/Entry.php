<?php
/**
 * Created by PhpStorm.
 * User: chenhong
 * Date: 16/7/31
 * Time: 上午10:38
 */

namespace Swoole\Server;


interface Entry
{
    //设置服务
    public function setClient($client);

    //服务启动
    public function run();
}