<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------‌

class TypeActe
{
    private static $typeActe;
    private array $a_type_acte = [];
    protected $connexionBD;
    private array $a_idf_par_type_acte = [];

    private function __construct($pconnexionBD)
    {
        $this->connexionBD = $pconnexionBD;
    }

    public static function singleton($pconnexionBD)
    {
        if (!isset(self::$typeActe)) {
            $c = __CLASS__;
            self::$typeActe = new $c($pconnexionBD);
        }
        return self::$typeActe;
    }

    public function ajoute($pst_type_acte, $pst_sigle_acte)
    {
        if ($pst_type_acte != '' && !array_key_exists(strval($pst_type_acte), $this->a_type_acte))
            $this->a_type_acte[strval($pst_type_acte)] = $pst_sigle_acte;
    }

    public function sauve()
    {
        $a_params_precs = $this->connexionBD->params();
        $a_types_a_creer = array();
        if (count($this->a_type_acte) > 0) {
            $st_requete = "insert ignore INTO `type_acte` (nom,sigle_nimegue) values ";
            $a_colonnes = array();
            $i = 0;
            foreach ($this->a_type_acte as $st_type_acte => $st_sigle) {
                $a_colonnes[] = "(:type$i,:sigle$i)";
                $a_types_a_creer[":type$i"] = $st_type_acte;
                $a_types_a_creer[":sigle$i"] = $st_sigle;
                $i++;
            }
            $st_colonnes = join(',', $a_colonnes);
            $st_requete .= $st_colonnes;
            try {
                $this->connexionBD->initialise_params($a_types_a_creer);
                $this->connexionBD->execute_requete($st_requete);
                $this->connexionBD->initialise_params($a_params_precs);
                $this->a_idf_par_type_acte = null;
            } catch (Exception $e) {
                die('Sauvegarde TypeActe impossible: ' . $e->getMessage() . ": $st_requete");
            }
        }
    }

    public function charge_liste_idf_par_nom()
    {
        $this->a_idf_par_type_acte = $this->connexionBD->liste_clef_par_valeur("select idf,nom from `type_acte`");
    }

    public function vers_idf($pst_nom)
    {
        if (is_null($pst_nom)) return null;
        if (!$this->a_idf_par_type_acte) $this->charge_liste_idf_par_nom();
        if (array_key_exists(strval($pst_nom), $this->a_idf_par_type_acte))
            return $this->a_idf_par_type_acte[strval($pst_nom)];
        else
            return -1;
    }
}
