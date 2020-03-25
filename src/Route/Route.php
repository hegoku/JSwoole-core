<?php
namespace JSwoole\Route;

class Route
{
    protected static $routers=[
        'GET'=>[],
        'POST'=>[],
        'PUT'=>[],
        'DELETE'=>[],
        'OPTIONS'=>[],
        'HEAD'=>[],
        'TRACE'=>[],
        'CONNECT'=>[]
    ];

    public static function loadRouter($router_table){
        foreach($router_table as $v){
            if ($v[0]=='*') {
                foreach (static::$routers as $method=>$value) {
                    static::addRouter($method, $v[1], $v[2], $v['middlewares'] ?? []);
                }
            } elseif (stripos($v[0], '/')!==false) {
                $methods=explode('/', $v[0]);
                foreach ($methods as $value) {
                    static::addRouter(strtoupper($value), $v[1], $v[2], $v['middlewares'] ?? []); 
                }
            } else {
                static::addRouter(strtoupper($v[0]), $v[1], $v[2], $v['middlewares'] ?? []);
            }
        }
    }

    protected static function addRouter($method, $uri, $action, $middlewares=[]){
        $action=explode('@', $action);
        static::$routers[$method][$uri]=[
            'controller'=>$action[0],
            'action'=>$action[1],
            'middlewares'=>$middlewares
        ];
    }

    public static function parseUri(string $method, string $request_uri)
    {
        if(!isset(static::$routers[$method])){
            throw new RouteException("HTTP Method not found.");
        }

        foreach(static::$routers[$method] as $uri=>$item){
            $pregUri=preg_quote($uri, '/');
            $pattern=preg_replace('/\\\{(\w+)\\\}/', '(\w+)', $pregUri); // \/index\/\{fd\} replace \{fd\} to (w+)
            $res=preg_match_all('/^'.$pattern.'$/', $request_uri);
            if($res>0){
                $ret=$item;

                preg_match_all('/\{(\w+)\}/', $uri, $matches);
                $parametersKey= isset($matches[1]) ? $matches[1] : [];
                preg_match_all('/^'.$pattern.'$/', $request_uri, $matches);
                array_shift($matches);
                $parametersValue=[];
                foreach($matches as $value){
                    array_push($parametersValue, $value[0]);
                }
                $ret['params']=array_combine($parametersKey, $parametersValue);
                return $ret;
            }
        }
        throw new RouteException("Route not found.");
    }
}