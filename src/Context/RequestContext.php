<?php
namespace JSwoole\Context;

class RequestContext
{
    use ComponentTrait {
        loadComponents as traitLoadComponents;
    }

    protected $worker_context;
    public $co_uid;

    public function __construct($co_uid, $worker_context)
    {
        $this->worker_context=$worker_context;
        $this->co_uid=$co_uid;
    }

    public function loadComponents()
    {
        $this->traitLoadComponents($this->worker_context->getConfig('components'));
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