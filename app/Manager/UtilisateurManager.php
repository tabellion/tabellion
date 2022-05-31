<?php
namespace App\Manager;

use App\Core\Manager;

class UtilisateurManager extends Manager
{
    private PrivilegeManager $privilegeManager;

    public function __construct($dbconfig)
    {
        parent::__construct($dbconfig);
        $this->privilegeManager = new PrivilegeManager($dbconfig);

    }

    public function findAll()
    {
        $sql = "SELECT * FROM adherent";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findOneById(int $id)
    {
        $sql = "SELECT * FROM adherent WHERE idf=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':id' => $id
        ]);
        if (!$result) {
            throw new \Exception("Cet utilisateur n'existe pas.", 1);
        }
        return $stmt->fetch();
    }

    public function findOneByCriteria(array $criteria)
    {
        $fields = '';
        $i = 0;
        foreach ($criteria as $key => $value) {
            if ($i == 0) {
                $fields .= " $key=:$key";
            } else {
                $fields .= " AND $key=:$key";
            }
        }

        $sql = "SELECT * FROM adherent WHERE $fields";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($criteria);
        if (!$result) {
            throw new \Exception("Cet utilisateur n'existe pas.", 1);
        }
        return $stmt->fetch();
    }

    public function findOneForAuthentification(string $identifier)
    {
        $sql = "SELECT * FROM adherent WHERE ident=:identifier OR email_perso=:identifier";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':identifier' => $identifier
        ]);
        if (!$result) {
            return null;
        }
        
        $user = $stmt->fetch();
        
        $sql2 = "UPDATE adherent SET derniere_connexion=now() WHERE idf=:idf";
        $stmt = $this->db->prepare($sql2);
        $result = $stmt->execute([
            ':idf' => $user['idf']
        ]);

        $privileges = $this->privilegeManager->findAllWhithAdherentId($user['idf']);
        $user['privileges'] = [];

        foreach ($privileges as $row) {
            $user['privileges'][] = $row['droit'];
        }

        return $user;
    }
}