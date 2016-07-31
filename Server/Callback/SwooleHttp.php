<?php
/**
 * Created by PhpStorm.
 * User: chenhong
 * Date: 16/7/24
 * Time: 下午4:24
 */

namespace Swoole\Server\Callback;
use Swoole\Server\Callback\Swoole;

abstract class SwooleHttp extends Swoole
{
    public function onReceive()
    {
        throw new \Exception('http server must use onRequest');
    }
    
    //TODO 需要做文件 header 处理
    public function doRequest($request,$response)
    {
        $extArr = include "mimes.php";
        $extArr = array_flip($extArr);
        $fileName = $request->server['path_info'];
        $fielExt  = pathinfo($fileName,PATHINFO_EXTENSION);
        $response->header('Content-Type',$extArr[$fielExt]);
        if ( is_file($fileName) && file_exists($fileName) )
        {
            $response->status(200);
        } else{
            $response->status(404);
        }
        $this->onRequest($request,$response);
    }

    abstract public function onRequest($request,$response);
}