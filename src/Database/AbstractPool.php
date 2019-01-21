<?php
namespace JSwoole\Database;

use Swoole\Coroutine\Channel;

abstract class AbstractPool
{
    protected static $pool;
    protected static $is_init=false;

    public abstract function createItem();

    public function __construct($count)
    {
       if (!static::$is_init) {
            static::$is_init=true;
            static::$pool=new Channel($count);
            for ($i=0;$i<$count;$i++) {
                static::$pool->push([
                    'create_time'=>time(),
                    'data'=>$this->createItem(),
                    'last_used_time'=>time()
                ]);
            }
        }
        
    }

    public function getItem($time_out=10)
    {
        return static::$pool->pop($time_out);
    }

    public function pushItem($item)
    {
        $item['last_used_time']=time();
        static::$pool->push($item);
    }

}