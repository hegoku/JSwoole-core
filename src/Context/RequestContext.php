<?php
namespace JSwoole\Context;

class RequestContext
{
    protected $components=[];
    protected $worker_context;

    public function __construct($worker_context)
    {
        $this->worker_context=$worker_context;   
    }

    public function loadComponents()
    {
        foreach ($this->worker_context->getConfig('components') as $class=>$param) {
            $class_name=$param['class'];
            $this->components[$class]=$this->buildInstance($class_name, $param['params']);
        }
    }

    public function buildInstance($class_name, $params)
    {
        $dependencies = [];
        $reflection = new \ReflectionClass($class_name);

        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[$param->name] = $param->getDefaultValue();
                }else {
					$dependencies[$param->name] = null;
				}
            }
        }

        foreach ($params as $name=>$p) {
            $dependencies[$name]=$p;
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    public function __get($name)
    {
        if (isset($this->components[$name])) {
            return $this->components[$name];
        } else {
            return null;
        }
    }
}