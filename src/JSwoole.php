<?php
namespace JSwoole;

use JSwoole\Content\WorkerContent;
use JSwoole\Content\RequestContent;

class JSwoole
{
    protected static $requestContent=[];
    protected static $worker_content;

    public static function initWorderContent($worker_id, $app_config)
    {
        static::$worker_content=new WorkerContent($worker_id);
        static::$worker_content->setConfig($app_config);
    }

    public static function getWorkerContent()
    {
        return static::$worker_content;
    }

    public static function addRequestContent()
    {
        $cid=\co::getuid();
        if (isset(static::$requestContent[$cid])) {
            unset(static::$requestContent[$cid]);
        }
        static::$requestContent[$cid]=new RequestContent(static::getWorkerContent());
    }

    public static function removeRequestContent()
    {
        $cid=\co::getuid();
        if (isset(static::$requestContent[$cid])) {
            unset(static::$requestContent[$cid]);
        }
    }

    public static function app()
    {
        $cid=\co::getuid();
        if (isset(static::$requestContent[$cid])) {
            return static::$requestContent[$cid];
        } else {
            return null;
        }
    }
}