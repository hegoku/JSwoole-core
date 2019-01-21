<?php
namespace JSwoole\Database\Manager;

use Illuminate\Database\MySqlConnection;
use JSwoole\Database\AbstractPool;

class MysqlPoolManager extends AbstractPool implements ManagerInterface
{
    private $config;
    private $connection=null;
    private $wait_timeout;
    protected static $is_m_init=false;
    
    public function __construct($config)
    {
        $this->config=$config;
        if (!static::$is_m_init) {
            static::$is_m_init=true;
            parent::__construct($config['pool_max']);
            $this->wait_timeout=$config['wait_timeout'];
            $this->checkConnection();
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
        return $this->connection['data'];
    }

    public function __destruct()
    {
        $this->pushItem($this->connection);
    }

    public function checkConnection()
    {
        swoole_timer_tick(12000, function ($timer_id) {
            if (self::$pool->length()<=0) return;
            $item=$this->getItem(1);
            if (($item['last_used_time']+$this->wait_timeout)<time()) {
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