<?php 

require_once __DIR__ . '/../Core/Connection.php';

class UtilisateurManager extends Connection
{
    public function findAll()
    {
        $sql = "SELECT * FROM adherent";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findOneById(int $id)
    {
        $sql = "SELECT * FROM adherent WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':id' => $id
        ]);
        if (!$result) {
            throw new Exception("Cet utilisateur n'existe pas.", 1);
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
            throw new Exception("Cet utilisateur n'existe pas.", 1);
        }
        return $stmt->fetch();
    }
}