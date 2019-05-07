<?php
namespace JSwoole\Database;

use JSwoole\Database\MysqlPoolManager;

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

    public function connection($name)
    {
        if (isset($this->manager[$name])) {
            return $this->manager[$name]->getConnection();
        } else {
            throw new \Exception('MysqlPoolManager '.$name.' not exist');
        }
    }

}