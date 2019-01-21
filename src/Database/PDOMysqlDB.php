<?php
namespace JSwoole\Database;

use JSwoole\Database\SwoolePDO;
use JSwoole\Database\Manager\MysqlPoolManager;
use Illuminate\Database\MySqlConnection;

class PDOMysqlDB
{
    public $manager=[];
    public $connection_config=[];

    public function __construct($connection_config)
    {
        $this->connection_config=$connection_config;
        foreach ($this->connection_config as $name=>$v) {
            $this->manager[$name]=new MysqlPoolManager($v);
        }
    }

    public function connection($name = null)
    {
        if (isset($this->manager[$name])) {
            return $this->manager[$name]->getConnection();
        } else {
            return null;
        }
    }

}