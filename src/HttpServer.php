<?php
namespace JSwoole;

use Swoole\Http\Server as SwooleHttpServer;
use JSwoole\Route\Route;
use Illuminate\Pipeline\Pipeline;

class HttpServer
{
    protected $server;
    public $app_config=[];
    protected $daemonize=false;
    protected $host;
    protected $port;

    public function __construct($path, $host, $port, $daemonize=false)
    {
        ini_set('memory_limit','-1');
        $this->host=$host;
        $this->port=$port;
        $this->daemonize=$daemonize;
        JSwoole::$base_path=$path['base_path'];
        $this->app_config=require_once($path['app_config']);;

        \Swoole\Runtime::enableCoroutine();
        $this->server=new SwooleHttpServer($host, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);

        $server_params=[
            'max_request' => env('SERVER_MAX_REQUEST', 1000),
            'daemonize' => $this->daemonize,
            'dispatch_mode' => env('SERVER_DISPATCH_MODE', 3),
            'reload_async' => true,
            'pid_file' =>  JSwoole::$base_path.'/runtime/server.pid',
            'log_file'=> JSwoole::$base_path.'/runtime/server.log'
        ];

        if (!empty(env('SERVER_REACTOR_NUM', ''))) {
            $server_params['reactor_num']=env('SERVER_REACTOR_NUM');
        }
        if (!empty(env('SERVER_WORKER_NUM', ''))) {
            $server_params['worker_num']=env('SERVER_WORKER_NUM');
        }
        if (!empty(env('SERVER_BACKLOG', ''))) {
            $server_params['backlog']=env('SERVER_BACKLOG');
        }
        if (!empty(env('SERVER_MAX_CONNECTION', ''))) {
            $server_params['max_connection']=env('SERVER_MAX_CONNECTION');
        }
        $this->server->set($server_params);

        $this->server->on("start", function ($server) {
            if (!$this->daemonize) {
                echo "Swoole http server is started at http://$this->host:$this->port\n";
            }
        });
        
        $this->server->on('WorkerStart', function(SwooleHttpServer $server, int $worker_id){
            if(function_exists('apc_clear_cache')){
                apc_clear_cache();
            }
            if(function_exists('opcache_reset')){
                opcache_reset();
            }

            JSwoole::initWorkerContext($worker_id, $this->app_config);
            Route::loadRouter(\JSwoole\JSwoole::getWorkerContext()->getConfig('route'));
        });

        $this->server->on('request', function($swooleRequest, $swooleResponse) {
            JSwoole::addRequestContext();
            try {
                JSwoole::app()->loadComponents();
        
                $controller='';
                $route_ret=[];
                try {
                    if (is_null($swooleRequest->server['request_method']) || is_null($swooleRequest->server['request_uri'])) {
                        throw new \JSwoole\Route\RouteException('server的request_method或者request_uri为null');
                    }
                    $route_ret=Route::parseUri($swooleRequest->server['request_method'], $swooleRequest->server['request_uri']);
                } catch (\JSwoole\Route\RouteException $e) {
                    $swooleResponse->status(404);
                    return $swooleResponse->end(json_encode(['code'=>404, 'msg'=>'请求不存在']));
                }
            
                $controller='\\'.JSwoole::getWorkerContext()->getConfig('controller_namespace').$route_ret['controller'];
                $request=Request::createFromSwoole($swooleRequest);
                $controllerInstance=new $controller($request);
                $response=(new Pipeline(JSwoole::app()->container))
                    ->send($request)
                    ->through($route_ret['middlewares'])
                    ->then(function ($request) use ($controllerInstance, $route_ret) {
                        return call_user_func_array([$controllerInstance, $route_ret['action']], $route_ret['params']);
                });
            
                $response_headers=$response->getHeaders();
                foreach ($response_headers as $name=>$values) {
                    $swooleResponse->header($name, implode(', ', $values));
                }

                $response_cookies=$response->getCookies();
                foreach ($response_cookies as $name=>$values) {
                    $swooleResponse->cookie($name, $values['value'], $values['expire'], $values['path'], $values['domain'], $values['secure'], $values['httponly'], $values['samesite']);
                }
                
                if ($response->getStatusCode()==302) {
                    $swooleResponse->redirect($response->getRedirectUrl());
                } else {
                    $swooleResponse->status($response->getStatusCode());
                    $swooleResponse->end($response->getBody());
                }
                
                
            } catch (\Exception $e) {
                if (!$this->daemonize) {
                    echo Date("Y-m-d H:i:s ");
                    var_dump($e->getMessage());
                }
                JSwoole::app()->log->log($e->getMessage(), \JSwoole\Log\Log::LEVEL_ERROR, 'app');
                $swooleResponse->status(500);
                $swooleResponse->end(json_encode(['code'=>500, 'msg'=>'内部服务器错误']));
            } finally {
                JSwoole::app()->log->flush();
                JSwoole::removeRequestContext();
            }
        });
    }

    public function run()
    {
        $this->server->start();
    }
}