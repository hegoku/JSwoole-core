<?php
namespace JSwoole\Context;

use Illuminate\Container\Container;

class RequestContext
{
    protected $worker_context;
    protected $components=[];
    public $co_uid;
    public $container;

    public function __construct($co_uid, $worker_context)
    {
        $this->worker_context=$worker_context;
        $this->co_uid=$co_uid;
        $this->container=new Container();
    }

    public function loadComponents()
    {
        $components_config=$this->worker_context->getConfig('components');
        foreach ($components_config as $class=>$param) {
            $this->components[$class]=$this->container->make($param['class'], $param['params']);
        }
    }

    public function getComponents()
    {
        return $this->components;
    }

    public function __get($name)
    {
        if (isset($this->components[$name])) {
            return $this->components[$name];
        } else {
            throw new \Exception('Component '.$name.' not exist');
        }
    }

    public function __call($name, $arguments)
    {
        if (isset($this->components[$name]) && is_callable($this->components[$name])) {
            return call_user_func_array($this->components[$name], $arguments);
        } else {
            throw new \Exception('Method or Component '.$name.' not exist');
        }
    }
}