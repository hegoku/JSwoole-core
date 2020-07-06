<?php
namespace JSwoole\Database;

use Illuminate\Database\MySqlConnection;
use JSwoole\Database\PoolTrait;
use Swoole\Timer;

class MysqlPoolManager
{
    use PoolTrait;

    private $connection=null;
    private static $config;
    private static $wait_timeout;
    protected static $is_m_init=false;
    
    public function __construct($config)
    {
        if (!static::$is_m_init) {
            static::$config=$config;
            static::$is_m_init=true;
            static::$max=$config['pool_max'];
            static::$wait_timeout=$config['wait_timeout'];
            static::checkConnection();
        }
    }

    public static function createItem()
    {
        $dsn='mysql:dbname='.static::$config['database'].';host='.static::$config['host'].';port='.static::$config['port'].';charset='.static::$config['charset'];
        $pdo=new \PDO($dsn, static::$config['username'], static::$config['password']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
        if (isset(static::$config['collation'])) {
            $pdo->prepare('set names "'.static::$config['charset'].'" COLLATE "'.static::$config['collation'].'"')->execute();
        } else {
            $pdo->prepare('set names "'.static::$config['charset'].'"')->execute();
        }
        return new MysqlConnection($pdo, static::$config['database'], static::$config['prefix'], static::$config);
    }

    public function getConnection()
    {
        if ($this->connection==null) {
            $this->connection=static::getItem();
        }
        if (empty($this->connection)) {
            throw new \Exception('Get connection timeout, database: '.static::$config['database']);
        }
        return $this->connection['data'];
    }

    public function release()
    {
        if ($this->connection!=null) {
            if ($this->connection['data']->logging()) {
                $this->connection['data']->flushQueryLog();
            }
            $this->connection['data']->rollBack();
            static::pushItem($this->connection);
            $this->connection=null;
        }
    }

    public function __destruct()
    {
        $this->release();
    }

    public static function checkConnection()
    {
        Timer::tick(12000, function ($timer_id) {
            if (self::$count<=0 || self::$pool->length()<=0) return;
            $item=self::$pool->pop(1);
            if (empty($item)) {
                return;
            }
            if (($item['last_used_time']+static::$wait_timeout)<time()) {
                $item=null;
                self::$pool->push([
                    'create_time'=>time(),
                    'data'=>static::createItem(),
                    'last_used_time'=>time()
                ]);
            } else {
                self::$pool->push($item);
            }
        });
    }
}