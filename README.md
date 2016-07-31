# [swoole网络通信引擎](http://www.swoole.com)

找了一圈也没找着一个比较好并且支持 laravel 框架的 swoole 服务。有的有但是文档也不全。所以就想着自己写一个吧!高手忽喷。

> 支持跟各种框架结合。

- Q Q  :`747825455`
- 微信:`weixinvipch`

## 特征

- 基于 composer , 一键安装。
- 只需要设置对应服务的回调，即可实现业务逻辑代码的实现。
- 符合 [PSR](https://github.com/php-fig/fig-standards) 标准。

## 环境要求
1. php-5.3.10 或更高版本。
2. gcc-4.4 或更高版本.
3. **[composer](https://getcomposer.org/)** 
4. swoole 扩展。不会安装的同学请看 [环境安装](https://linkeddestiny.gitbooks.io/easy-swoole/content/book/chapter01/install.html) 配置教程。

## 安装
```shell
			composer require forget-chuckie/swoole
```

## 示例

> 服务使用示例

```php
<?php
use Swoole\Server\Swoole;
use Swoole\Server\Callback\SwooleWebSocket;

$swoole = new Swoole([
            'server_type' => 'ws',
            'host'        => '127.0.0.1',
            'port'        =>  9502,
            'work_mode'   =>  SWOOLE_PROCESS,
            'pid_path' => './masterPid/',//进程ID保存文件路径
            'project_name' => 'sokcet',
            'worker_num'  => 4
        ]);

//这里设服务要回调的类。必须继承 Callback 下对应的服务类型。
$swoole->setClient($obj);
//服务启动
$swoole->run();
```
## 更多

>目前还处在开发状态，如果有兴趣的小伙伴可以一起来。
