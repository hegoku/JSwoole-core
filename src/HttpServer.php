<?php
namespace JSwoole;

use Swoole\Http\Server as SwooleServer;

class HttpServer
{
    public $swoole_http_server;
    public $host;
    public $port;
    public $request_callback;
    public $worker_start_callback;
    public $manager_start_callback;

    public function __construct(string $host, int $port)
    {
        $this->host=$host;
        $this->port=$port;
    }

    public function run()
    {
        $this->swoole_http_server=new SwooleServer($this->host, $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        if ($this->manager_start_callback) {
            $this->swoole_http_server->on('ManagerStart', $this->manager_start_callback);
        }
        if ($this->worker_start_callback) {
            $this->swoole_http_server->on('WorkerStart', $this->worker_start_callback);
        }
        $this->swoole_http_server->on('request', $this->request_callback);
        $this->swoole_http_server->start();
    }
}