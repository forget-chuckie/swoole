<?php
/**
 * Created by PhpStorm.
 * User: chenhong
 * Date: 16/7/24
 * Time: 下午3:47
 */

namespace Swoole\Server\Callback;
use Swoole\Server\Callback\Swoole;

abstract class SwooleUdp extends Swoole
{
    public function onReceive()
    {
        throw new \Exception('swoole udp server must onPacket');
    }

    abstract public function onPacket($server,$data,$clientInfo);
}