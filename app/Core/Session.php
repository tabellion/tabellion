<?php 

session_start();

class Session 
{
    public function getAttribute(string $attibute)
    {
        return $_SESSION[$attibute] ?? null;
    }

    public function setAttribute(string $attibute, $value)
    {
        $_SESSION[$attibute] = $value;
    }

    public function delete()
    {
        session_destroy();
    }
}