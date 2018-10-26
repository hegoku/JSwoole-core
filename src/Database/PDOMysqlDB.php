<?php
namespace JSwoole\Database;

use JSwoole\Database\SwoolePDO;
use Illuminate\Database\MySqlConnection;

class PDOMysqlDB
{
    public $connection=[];
    public $connection_config=[];

    public function __construct($connection_config)
    {
        $this->connection_config=$connection_config;
        foreach ($this->connection_config as $name=>$v) {
            $dsn='mysql:dbname='.$v['database'].';host='.$v['host'];
            $pdo=new \PDO($dsn, $v['username'], $v['password']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->connection[$name]=new MysqlConnection($pdo, $v['database'], $v['prefix']);
        }
    }

    public function connection($name = null)
    {
        if (isset($this->connection[$name])) {
            return $this->connection[$name];
        } else {
            return null;
        }
    }
}