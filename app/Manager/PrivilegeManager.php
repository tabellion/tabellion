<?php 

require_once __DIR__ . '/../Core/Connection.php';

class PrivilegeManager extends Connection
{
    public function findAllWhithAdherentId(int $id)
    {
        $sql = "SELECT * FROM privilege WHERE idf_adherent=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => $id
        ]);
        return $stmt->fetchAll();
    }
}