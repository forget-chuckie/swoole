<?php
/**
 * Created by PhpStorm.
 * User: chenhong
 * Date: 16/7/24
 * Time: 下午5:17
 */
namespace Swoole\Server\Callback;
use Swoole\Server\Callback\SwooleHttp;

abstract class SwooleWebSocket extends SwooleHttp
{

    public function onRequest($request, $response)
    {
        $response->header("Content-Type", "text/html; charset=utf-8");
        $response->send("hello");
    }

    abstract public function onMessage($server,$frame);
}