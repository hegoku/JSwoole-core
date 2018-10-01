<?php
namespace JSwoole\Log;

class Log
{
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_INFO = 'INFO';
    const LEVEL_TRACE = 'TRACE';
    
    public $messages = [];

    public $targets=[];

    public function __construct($targets)
    {
        foreach ($targets as $target) {
            if (!isset($this->targets[$target['category']])) {
                $this->targets[$target['category']]=$this->buildInstance($target['target'], $target['params']);
            }
        }
    }

    public function log($message, $level, $category)
    {
        $time=microtime(true);
        $this->messages[$category][]=[
            $time,
            $level,
            $message
        ];
    }

    public function info($message, $category)
    {
        $this->log($message, self::LEVEL_INFO, $category);
    }

    public function flush()
    {
        $messages = $this->messages;
        $this->messages = [];
        foreach ($messages as $category=>$message) {
            foreach ($message as $v) {
                if (isset($this->targets[$category])) {
                    $this->targets[$category]->export($v);
                }
            }
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
}