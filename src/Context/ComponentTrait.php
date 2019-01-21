<?php
namespace JSwoole\Context;

trait ComponentTrait
{
    protected $components=[];

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

    public function loadComponents($components_config)
    {
        foreach ($components_config as $class=>$param) {
            $class_name=$param['class'];
            $this->components[$class]=$this->buildInstance($class_name, $param['params']);
        }
    }

    public function getComponents()
    {
        return $this->components;
    }
}