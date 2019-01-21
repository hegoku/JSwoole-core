<?php
namespace JSwoole\Database\Manager;

use Illuminate\Database\MySqlConnection;

class MysqlCommonManager implements ManagerInterface
{
    private $config;
    private $connection;
    
    public function __construct($config)
    {
        $this->config=$config;
        $dsn='mysql:dbname='.$this->config['database'].';host='.$this->config['host'].';charset='.$this->config['charset'];
        $pdo=new \PDO($dsn, $this->config['username'], $this->config['password']);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
        $this->connection=new MysqlConnection($pdo, $this->config['database'], $this->config['prefix']);
    }

    public function getConnection()
    {
        return $this->connection;
    }

}