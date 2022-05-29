<?php 



class Connection
{
    protected $db;

    public function __construct(array $config)
    {
        $host = $config['host'];
        $dbname = $config['dbname'];
        $user =  $config['user'];
        $password = $config['password'];
        
        $this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=latin1", $user, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
}