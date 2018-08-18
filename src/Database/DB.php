<?php
namespace JSwoole\Database;

use JSwoole\Database\SwoolePDO;
use Illuminate\Database\MySqlConnection;

class DB
{
    public $connection=[];
    public $connection_config=[];

    public function __construct($connection_config)
    {
        $this->connection_config=$connection_config;
        foreach ($this->connection_config as $name=>$v) {
            $pdo = new SwoolePDO();
            $pdo->connect([
                'host'        => $v['host'],
                'port'        => $v['port'],
                'user'        => $v['username'],
                'password'    => $v['password'],
                'database'    => $v['database'],
                'charset'     => $v['utf8'],
            ]);
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