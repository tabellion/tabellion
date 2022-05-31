<?php 
namespace App\Service;

use App\Core\Session;
use App\Manager\UtilisateurManager;

class AuthentificationService
{
    protected UtilisateurManager $utilisateurManager;
    protected Session $session;

    public function __construct(array $connection)
    {
        $this->utilisateurManager = new UtilisateurManager($connection);
        $this->session = new Session();
    }

    public function login(array $credencials)
    {
        $user = $this->utilisateurManager->findOneForAuthentification($credencials['identifier']);
        if ($user && password_verify($credencials['password'], $user['mdp'])) {
            $this->session->setAuthenticated(true);
            return $user;
        }
        return false;
    }

    public function logout()
    {
        $this->session->delete();
        header("Location: /");
    }
}