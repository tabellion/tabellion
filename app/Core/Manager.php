<?php 


class Manager
{
    protected $db;

    public function __construct($host, $user, $password, $dbname)
    {
        $this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=latin1", $user, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
}