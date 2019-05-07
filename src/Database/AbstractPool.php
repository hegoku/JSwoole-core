<?php
namespace JSwoole\Database;

use Swoole\Coroutine\Channel;

abstract class AbstractPool
{
    protected static $pool;
    protected static $count;
    protected static $max=0;
    // protected static $is_init=false;

    public abstract function createItem();

    // public function __construct($count)
    // {
    //     if (!static::$is_init) {
    //         static::$is_init=true;
    //         static::$pool=new Channel($count);
    //         for ($i=0;$i<$count;$i++) {
    //             static::$pool->push([
    //                 'create_time'=>time(),
    //                 'data'=>$this->createItem(),
    //                 'last_used_time'=>time()
    //             ]);
    //         }
    //     }
        
    // }

    public function getItem($time_out=10)
    {
        if (static::$count==0) {
            static::$pool=new Channel(static::$max); 
        }
        if (static::$count<static::$max) {
            static::$pool->push([
                'create_time'=>time(),
                'data'=>$this->createItem(),
                'last_used_time'=>time()
            ]); 
            static::$count++;
        }
        return static::$pool->pop($time_out);
    }

    public function pushItem($item)
    {
        $item['last_used_time']=time();
        static::$pool->push($item);
    }

}