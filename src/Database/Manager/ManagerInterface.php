<?php
namespace JSwoole\Database\Manager;

interface ManagerInterface
{
    public function __construct($config);
    
    public function getConnection();
}