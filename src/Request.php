<?php
namespace JSwoole;

use Illuminate\Http\Request as BaseRequest;
use Swoole\Http\Request as SwooleRequest;

class Request extends BaseRequest
{
    public static function createFromSwoole(SwooleRequest $swooleRequest)
    {
        $get=isset($swooleRequest->get)?$swooleRequest->get:[];
        $post=isset($swooleRequest->post)?$swooleRequest->post:[];
        $files=isset($swooleRequest->files)?$swooleRequest->files:[];
        $cookie=isset($swooleRequest->cookie)?$swooleRequest->cookie:[];

        $server = [
            'REQUEST_METHOD' => $swooleRequest->server['request_method'],
            'REQUEST_URI' => $swooleRequest->server['request_uri'],
            'PATH_INFO' => $swooleRequest->server['path_info'],
            'REQUEST_TIME' => $swooleRequest->server['request_time'],
            'GATEWAY_INTERFACE' => 'swoole/' . SWOOLE_VERSION,
            // Server
            'SERVER_PROTOCOL' => $swooleRequest->server['server_protocol'],
            'REQUEST_SCHEMA' => isset($swooleRequest->header['request_scheme']) ? $swooleRequest->header['request_scheme'] : explode('/', $swooleRequest->server['server_protocol'])[0],
            'SERVER_NAME' => isset($swooleRequest->server['server_name']) ? $swooleRequest->server['server_name'] : '',
            'SERVER_ADDR' => $swooleRequest->header['host'],
            'SERVER_PORT' => $swooleRequest->server['server_port'],
            'REMOTE_ADDR' => $swooleRequest->server['remote_addr'],
            'REMOTE_PORT' => $swooleRequest->server['remote_port'],
            'QUERY_STRING' => isset($swooleRequest->server['query_string']) ? $swooleRequest->server['query_string'] : '',
            // Headers
            'HTTP_HOST' => $swooleRequest->header['host'],
            'HTTP_USER_AGENT' => isset($swooleRequest->header['user-agent']) ? $swooleRequest->header['user-agent'] : '',
            'HTTP_ACCEPT' => isset($swooleRequest->header['accept']) ? $swooleRequest->header['accept'] : '*/*',
            'HTTP_ACCEPT_LANGUAGE' => isset($swooleRequest->header['accept-language']) ? $swooleRequest->header['accept-language'] : '',
            'HTTP_ACCEPT_ENCODING' => isset($swooleRequest->header['accept-encoding']) ? $swooleRequest->header['accept-encoding'] : '',
            'HTTP_CONNECTION' => isset($swooleRequest->header['connection']) ? $swooleRequest->header['connection'] : '',
            'HTTP_CACHE_CONTROL' => isset($swooleRequest->header['cache-control']) ? $swooleRequest->header['cache-control'] : '',
        ];

        foreach ($swooleRequest->header as $key=>$v) {
            $server['HTTP_'.$key]=$v;
        }

        $request=new self($get, $post, [], $cookie, $files, $server, $swooleRequest->rawContent());
        if (0 === strpos(@$swooleRequest->header['content-type'], 'application/x-www-form-urlencoded')
            && in_array(strtoupper($swooleRequest->server['request_method']), array('POST', 'PUT', 'DELETE', 'PATCH'))
            ) {
            parse_str($swooleRequest->rawContent(), $data);
            $request->request= new \Symfony\Component\HttpFoundation\ParameterBag($data);
        } elseif (0 === strpos(@$swooleRequest->header['content-type'], 'application/json')
            && in_array(strtoupper($swooleRequest->server['request_method']), array('POST', 'PUT', 'DELETE', 'PATCH'))
            ) {
            $data=json_decode($swooleRequest->rawContent(), true);
            if (!is_array($data)) {
                $data=[];
            }
            $request->request= new \Symfony\Component\HttpFoundation\ParameterBag($data);
        }
        
        return $request;
    }
}