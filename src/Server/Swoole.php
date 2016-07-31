<?php
/**
 * Created by PhpStorm.
 * User: chenhong
 * Date: 16/7/22
 * Time: 下午10:40
 */

namespace Swoole\Server;
use Swoole\Server\Entry;
use Swoole\Server\Callback;

class Swoole implements Entry
{
    private $config;
    private $server;
    private $client;

    const SERVER_TCP       = 'tcp';
    const SERVER_UDP       = 'udp';
    const SERVER_HTTP      = 'http';
    const SERVER_HTTPS     = 'https';
    const SERVER_WEBSOCKET = 'ws';

    public function __construct(array $config)
    {
        if ( !extension_loaded('swoole') )
        {
            throw new \Exception("no swoole extension. get: https://github.com/swoole/swoole-src");
        }

        $this->config = $config;
        $socketType   = empty($this->config['server_type']) ? self::SERVER_TCP : strtolower($this->config['server_type']);
        $this->config['server_type'] = $socketType;
        switch ($this->config['server_type']) {

            case self::SERVER_TCP;
                $server = new \swoole_server(
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['work_mode'],
                    SWOOLE_SOCK_TCP
                );
                break;

            case self::SERVER_UDP:
                $server = new \swoole_server(
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['work_mode'],
                    SWOOLE_SOCK_UDP
                );
                break;

            case self::SERVER_HTTP:
                $server = new \swoole_http_server(
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['work_mode']
                );
                break;

            case self::SERVER_HTTPS:
                $server = new \swoole_http_server(
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['work_mode'],
                    \SWOOLE_SOCK_TCP | \SWOOLE_SSL
                );
                break;

            case self::SERVER_WEBSOCKET:
                $server = new \swoole_websocket_server(
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['work_mode']
                );
                break;
            default : break;
        }

        $this->server = $server;
        $this->server->set($config);
    }

    public function setClient($client)
    {
        if (!is_object($client) )
        {
            throw new \Exception("{$client} must is object");
        }

        switch ($this->config['server_type']) {
            case self::SERVER_TCP:
                if ( !$client instanceof Callback\SwooleTcp) {
                    throw new \Exception( " client must instanceof Callback\\swoole" );
                }
                break;
            case self::SERVER_UDP:
                if (!$client instanceof Callback\SwooleUdp) {
                    throw new \Exception( " client must instanceof Callback\\swooleUdp" );
                }
                break;
            case self::SERVER_HTTP:
            case self::SERVER_HTTPS:
                if ( !$client instanceof Callback\SwooleHttp) {
                    throw new \Exception("client must instanceof Callback\\swooleHttp");
                }
                break;
            case self::SERVER_WEBSOCKET:
                if ( !$client instanceof Callback\SwooleWebSocket) {
                    throw new \Exception("client must instanceof Callback\\swooleWebSocket");
                }
                break;
        }
        $this->client = $client;
    }

    public function run()
    {
        $handleArr = [
            'onTimer',
            'onWorkerStart',
            'onWorkerStop',
            'onReceive',
            'onPacket',
            'onTask',
            'onFinish',
            'onPipeMessage',
            'onWorkerError',
            'onManagerStart',
            'onManagerStop'
        ];

        $this->server->on('start',   [$this->client, 'onStart']);
        $this->server->on('shutdown',[$this->client, 'onShutdown']);
        $this->server->on('connect', [$this->client, 'onConnect']);
        $this->server->on('close',   [$this->client, 'onClose']);

        switch ($this->config['server_type']) {
            case self::SERVER_TCP:
                $this->server->on('receive',[$this->client,'doReceive']);
                break;
            case self::SERVER_UDP:
                $this->server->on('packet' ,[$this->client,'onPacket']);
                break;
            case self::SERVER_HTTPS:
            case self::SERVER_HTTP:
                $this->server->on('request',[$this->client,'doRequest']);
                break;
            case self::SERVER_WEBSOCKET:
                if ( method_exists($this->client,'onOpen') ) {
                    $this->server->on('open',[$this->client,'onOpen']);
                }

                if ( method_exists($this->client,'onHandShake') ) {
                    $this->server->on('HandShake',[$this->client,'onHandShake']);
                }

                if ( method_exists($this->client,'doRequest') ) {
                    $this->server->on('request',[$this->client,'doRequest']);
                }

                $this->server->on('message',[$this->client,'onMessage']);
                break;

        }

        //如果子类里有对应的方法
        foreach ($handleArr as $handle) {
            if ( method_exists($this->client,$handle) )
            {
                $this->server->on(substr($handle,2),[$this->client,$handle]);
            }
        }
        $this->server->start();
    }
}
