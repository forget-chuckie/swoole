<?php
/**
 * Created by PhpStorm.
 * User: chenhong
 * Date: 16/7/22
 * Time: 下午11:43
 */

namespace Swoole\Server\Callback;
use Swoole\Server\Scallback;

abstract class Swoole implements Scallback
{
    /**
     * $desc 在此事件之前Swoole Server已进行了如下操作
     * 1.已创建了manager进程
     * 2.已创建了worker子进程
     * 3.已监听所有TCP/UDP端口
     * 4.已监听了定时器
     *
     * 5.接下来要做
     * 6.主Reactor开始接收事件，客户端可以connect到Server
     *
     * 此方法主要实现在服务启动的时候把 master_pid 和 manage_pid 保存下来方便日后编写脚本来 向这两个PID发送信号来实现关闭和重启的操作。
     *
     * 注:
     * 1.这个方法里不只能 echo 打印 log 修改进程名称。不得执行其他操作
     * 2.onWorkerStart和onStart回调是在不同进程中并行执行的，不存在先后顺序。
     */
    public function onStart()
    {
        $server       = func_get_args()[0];
        $masterId     = $server->master_pid;
        $logDir = $server->setting['pid_path'];
        swoole_set_process_name(
            $server->setting['project_name'] . '_' .
            $server->setting['server_type'] .
            '_master.pid'
        );
        if ( !is_dir($logDir) ) mkdir($logDir,0777,true);
        $masterPidPath  = $logDir . $server->setting['server_type'] . '_master.pid';
        \swoole_async_writefile($masterPidPath,$masterId);
    }

    public function onConnect() {}

    abstract public function onReceive();

    //调用子类的 onReceive 在子类里 用 fun_get_argvs()接收参数
    public function doReceive($server, $fd, $data, $from_id){
        $this->onReceive($server,$fd,$from_id,$data);
    }

    public function onClose(){}

    /**
     * 此事件在Server结束时发生
     * 在此之前Swoole Server已进行了如下操作
     * 1.已关闭所有线程
     * 2.已关闭所有worker进程
     * 3.已close所有TCP/UDP监听端口
     * 4.已关闭主Rector
     * TODO 服务关闭了为什么不执行这个程序
     */
    public function onShutdown() {}

    /**
     * @desc  管理服务启动 设置 manager 进程 ID
     * @param $server
     */
    public function onManagerStart($server)
    {
        $logDir = $server->setting['pid_path'];
        if ( !is_dir($logDir) ) mkdir($logDir,0777,true);
        $managerId    = $server->manager_pid;
        swoole_set_process_name(
            $server->setting['project_name'] . '_' .
            $server->setting['server_type'] . '_manager.pid'
        );
        $managerPidPath = $logDir . $server->setting['server_type'] . '_manager.pid';
        \swoole_async_writefile($managerPidPath,$managerId);
    }

    /**
     * @desc  管理服务停止 删除管理进程文件
     * @param $server
     */
    public function onManagerStop($server)
    {
        $logDir = $server->setting['pid_path'];
        $managerPidPath = $logDir . $server->setting['server_type'] . '_manager.pid';
        $masterPidPath  = $logDir . $server->setting['server_type'] . '_master.pid';
        if ( is_file($managerPidPath) ) unlink($managerPidPath);
        if ( is_file($masterPidPath) )  unlink($masterPidPath);
    }

    /**
     * @desc  worker 进程启动时 设置 worker 进程名。
     *        当 $work_id >= 设置里的 worker_num 里这表示这个是 task 进程. mac 不支持修改进程名。
     * @param $server
     * @param $work_id
     */
    public function onWorkerStart($server,$work_id)
    {
        if ( $work_id >= $server->setting['worker_num'] )
        {
            swoole_set_process_name(
                $server->setting['project_name'] . '_' .
                $server->setting['server_type'] . '_task_worker.pid'
            );
        } else {
            swoole_set_process_name(
                $server->setting['project_name'] . '_' .
                $server->setting['server_type'] . '_worker_num.pid'
            );
        }
    }

    /**
     * @desc  worker 进程关闭的时候
     * @param $server
     * @param $work_id
     */
    public function onWorkerStop($server,$work_id){}

    /**
     * @desc  worker 发生异常时会触发
     * @param $server
     * @param $worker_id  异常进程的编号
     * @param $worker_pid 异常进程的ID
     * @param $exit_code  退出的状态码，范围是 1 ～255
     */
    public function onWorkerError($server,$worker_id,$worker_pid,$exit_code) {

    }
}