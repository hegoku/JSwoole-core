<?php
namespace JSwoole;

class JSwoole
{
    public static $app;
    public $worker_id=0;
    public $app_config=[];
    public $components=[];

    public static function init()
    {
        static::$app=new static();
    }

    public function setConfig($config)
    {
        $this->app_config=$config;
    }

    public function getConfig($name)
    {
        if (isset($this->app_config[$name])) {
            return $this->app_config[$name];
        } else {
            return null;
        }
    }

    public function loadComponents()
    {
        foreach ($this->app_config as $class=>$param) {
            $class_name=$param['class'];
            $this->components[$class]=new $class_name();
        }
    }

    public function getRote()
    {
        return $this->app_config['route'];
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