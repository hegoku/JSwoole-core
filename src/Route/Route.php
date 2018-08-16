<?php
namespace JSwoole\Route;

class Route
{
    protected $routers=[
        'GET'=>[],
        'POST'=>[],
        'PUT'=>[],
        'DELETE'=>[]
    ];

    public function loadRouter($router_table){
        foreach($router_table as $v){
            $this->addRouter(strtoupper($v[0]),$v[1],$v[2]);
        }
    }

    protected function addRouter($method,$uri,$action){
        $this->routers[$method][$uri]=$action;
    }

    public function parseUri(string $method, string $request_uri)
    {
        if(!isset($this->routers[$method])){
            throw new RouteException("HTTP Method not found.");
        }

        foreach($this->routers[$method] as $uri=>$action){
            $pregUri=preg_quote($uri,"/");
            $pattern=preg_replace('/\\\{(\w+)\\\}/','(\w+)',$pregUri); // \/index\/\{fd\} replace \{fd\} to (w+)
            $res=preg_match_all('/'.$pattern.'$/',$request_uri);
            if($res>0){
                return explode("@",$action);
            }
        }
        throw new RouteException("Route not found.");
    }
}