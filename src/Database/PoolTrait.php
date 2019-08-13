<?php
namespace JSwoole\Database;

use Swoole\Coroutine\Channel;

trait PoolTrait
{
    protected static $pool;
    protected static $count;
    protected static $max=0;

    public static abstract function createItem();

    public static function getItem($time_out=10)
    {
        if (static::$count==0) {
            static::$pool=new Channel(static::$max); 
        }
        if (static::$count<static::$max) {
            static::$count++; //要先加+1，锁住，再push，否则并发会有问题
            static::$pool->push([
                'create_time'=>time(),
                'data'=>static::createItem(),
                'last_used_time'=>time()
            ]); 
        }
        return static::$pool->pop($time_out);
    }

    public static function pushItem($item)
    {
        $item['last_used_time']=time();
        static::$pool->push($item);
    }

}