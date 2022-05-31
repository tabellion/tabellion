<?php 
namespace App\Core;

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

    public function setAuthenticated(bool $autenticated = false)
    {
        if ($autenticated === true) {
            $this->setAttribute('auth', true);
        }
    }

    public function isAuthenticated()
    {
        return (null !== $this->getAttribute('auth') && $this->getAttribute('auth') === true);
    }
    
    public function delete()
    {
        session_destroy();
    }
}