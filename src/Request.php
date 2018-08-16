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
            'REQUEST_METHOD' => $request->server['request_method'],
            'REQUEST_URI' => $request->server['request_uri'],
            'PATH_INFO' => $request->server['path_info'],
            'REQUEST_TIME' => $request->server['request_time'],
            'GATEWAY_INTERFACE' => 'swoole/' . SWOOLE_VERSION,
            // Server
            'SERVER_PROTOCOL' => isset($request->header['server_protocol']) ? $request->header['server_protocol'] : $request->server['server_protocol'],
            'REQUEST_SCHEMA' => isset($request->header['request_scheme']) ? $request->header['request_scheme'] : explode('/', $request->server['server_protocol'])[0],
            'SERVER_NAME' => isset($request->header['server_name']) ? $request->header['server_name'] : $host,
            'SERVER_ADDR' => $host,
            'SERVER_PORT' => isset($request->header['server_port']) ? $request->header['server_port'] : $request->server['server_port'],
            'REMOTE_ADDR' => $host,
            'REMOTE_PORT' => isset($request->header['remote_port']) ? $request->header['remote_port'] : $request->server['remote_port'],
            'QUERY_STRING' => isset($request->server['query_string']) ? $request->server['query_string'] : '',
            // Headers
            'HTTP_HOST' => $host,
            'HTTP_USER_AGENT' => isset($request->header['user-agent']) ? $request->header['user-agent'] : '',
            'HTTP_ACCEPT' => isset($request->header['accept']) ? $request->header['accept'] : '*/*',
            'HTTP_ACCEPT_LANGUAGE' => isset($request->header['accept-language']) ? $request->header['accept-language'] : '',
            'HTTP_ACCEPT_ENCODING' => isset($request->header['accept-encoding']) ? $request->header['accept-encoding'] : '',
            'HTTP_CONNECTION' => isset($request->header['connection']) ? $request->header['connection'] : '',
            'HTTP_CACHE_CONTROL' => isset($request->header['cache-control']) ? $request->header['cache-control'] : '',
        ];

        $request=new self($get, $post, [], $cookie, $files, $server, $swooleRequest->rawContent());
        if (0 === strpos($swooleRequest->header['content-type'], 'application/x-www-form-urlencoded')
            && in_array(strtoupper($swooleRequest->server['request_method']), array('POST', 'PUT', 'DELETE', 'PATCH'))
            ) {
            parse_str($swooleRequest->rawContent(), $data);
            $request->request= new \Symfony\Component\HttpFoundation\ParameterBag($data);
        } elseif (0 === strpos($swooleRequest->header['content-type'], 'application/json')
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