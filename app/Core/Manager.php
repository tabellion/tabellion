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

    /**
     * Chaine encodée en cp1252 vers UTF8
     * @param string $st_valeur chaine cp1252 à convertir
     * @return string chaine encodée en UTF8
     */
    static public function cp1252_vers_utf8(string $st_valeur): string
    {
        return mb_convert_encoding($st_valeur, 'UTF8', 'cp1252');
    }

    /**
     * Chaine encodée en UTF8 vers cp1252
     * @Deprecated Une chaine doit toujours rester en utf8.
     * @param string $st_valeur chaine UTF8 à convertir
     * @return string chaine encodée en UTF8
     */
    static public function utf8_vers_cp1252(string $st_valeur): string
    {
        return mb_convert_encoding($st_valeur, 'cp1252', 'UTF8');
    }
}
