<?php 
namespace App\Core;

use App\Core\Connection;

class Manager extends Connection
{
    protected $db;

    public function __construct($dbconfig)
    {
        parent::__construct($dbconfig);
    }
}