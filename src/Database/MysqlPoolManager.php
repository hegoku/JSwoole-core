<?php
namespace JSwoole\Database;

use Illuminate\Database\MySqlConnection;
use JSwoole\Database\AbstractPool;
use Swoole\Timer;

class MysqlPoolManager extends AbstractPool
{
    private $config;
    private $connection=null;
    private static $wait_timeout;
    protected static $is_m_init=false;
    
    public function __construct($config)
    {
        $this->config=$config;
        if (!static::$is_m_init) {
            static::$is_m_init=true;
            static::$max=$config['pool_max'];
            static::$wait_timeout=$config['wait_timeout'];
            static::checkConnection();
        }
    }

    public function createItem()
    {
        $dsn='mysql:dbname='.$this->config['database'].';host='.$this->config['host'].';charset='.$this->config['charset'];
        $pdo=new \PDO($dsn, $this->config['username'], $this->config['password']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
        return new MysqlConnection($pdo, $this->config['database'], $this->config['prefix']);
    }

    public function getConnection()
    {
        if ($this->connection==null) {
            $this->connection=$this->getItem();
        }
        if (empty($this->connection)) {
            throw new \Exception('Get connection timeout, database: '.$this->config['database']);
        }
        return $this->connection['data'];
    }

    public function __destruct()
    {
        if ($this->connection!=null) {
            $this->pushItem($this->connection);
        }
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
                    'data'=>$this->createItem(),
                    'last_used_time'=>time()
                ]);
            } else {
                self::$pool->push($item);
            }
        });
    }
}