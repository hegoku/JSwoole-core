<?php
namespace JSwoole\Content;

class RequestContent
{
    protected $components=[];
    protected $worker_content;

    public function __construct($worker_content)
    {
        $this->worker_content=$worker_content;   
    }

    public function loadComponents()
    {
        foreach ($this->worker_content->getConfig('components') as $class=>$param) {
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