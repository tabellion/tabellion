<?php 
namespace App\Manager;

use App\Core\Manager;

class PrivilegeManager extends Manager
{
    public function findAllWhithAdherentId(int $id)
    {
        $sql = "SELECT droit FROM privilege WHERE idf_adherent=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id
        ]);
        return $stmt->fetchAll();
    }
}