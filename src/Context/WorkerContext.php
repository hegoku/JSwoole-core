<?php
namespace JSwoole\Context;

class WorkerContext
{
    protected $app_config;
    protected $worker_id=0;

    public function __construct($worker_id)
    {
        $this->worker_id=$worker_id;   
    }

    public function getWorkerId()
    {
        return $this->worker_id;
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

    public function getRote()
    {
        return $this->app_config['route'];
    }
}