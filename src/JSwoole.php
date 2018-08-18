<?php
namespace JSwoole;

use JSwoole\Context\WorkerContext;
use JSwoole\Context\RequestContext;

class JSwoole
{
    protected static $requestContext=[];
    protected static $worker_context;

    public static function initWorderContext($worker_id, $app_config)
    {
        static::$worker_context=new WorkerContext($worker_id);
        static::$worker_context->setConfig($app_config);
    }

    public static function getWorkerContext()
    {
        return static::$worker_context;
    }

    public static function addRequestContext()
    {
        $cid=\co::getuid();
        if (isset(static::$requestContext[$cid])) {
            unset(static::$requestContext[$cid]);
        }
        static::$requestContext[$cid]=new RequestContext(static::getWorkerContext());
    }

    public static function removeRequestContext()
    {
        $cid=\co::getuid();
        if (isset(static::$requestContext[$cid])) {
            unset(static::$requestContext[$cid]);
        }
    }

    public static function app()
    {
        $cid=\co::getuid();
        if (isset(static::$requestContext[$cid])) {
            return static::$requestContext[$cid];
        } else {
            return null;
        }
    }
}