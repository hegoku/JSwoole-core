<?php
namespace JSwoole\Database;

use Illuminate\Database\Eloquent\Model as BaseModel;
use JSwoole\JSwoole;

class Model extends BaseModel
{
    public static function resolveConnection($connection = null)
    {
        return JSwoole::app()->db->connection($connection);
    }
}