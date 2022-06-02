<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------?

class Profession
{
    private static $profession;
    protected $connexionBD;
    private $a_idf_par_profession;
    private $a_professions_a_creer;

    private function __construct($pconnexionBD)
    {
        $this->connexionBD = $pconnexionBD;
        $this->a_professions_a_creer = array();
        $this->a_idf_par_profession = [];
        $this->charge_liste_idf_par_nom();
    }

    public static function singleton($pconnexionBD)
    {
        if (!isset(self::$profession)) {
            $c = __CLASS__;
            self::$profession = new $c($pconnexionBD);
        }
        return self::$profession;
    }

    public function ajoute($pst_profession)
    {
        $st_profession = ucfirst(strtolower(trim($pst_profession)));
        if ($st_profession != '' && !array_key_exists(strval($st_profession), $this->a_idf_par_profession) && !in_array(strval($st_profession), $this->a_professions_a_creer))
            $this->a_professions_a_creer[] = strval($st_profession);
    }

    public function sauve($pst_rep_tmp = '')
    {
        $a_params_precs = $this->connexionBD->params();
        $a_professions_a_creer = array();
        if (count($this->a_professions_a_creer) > 0) {
            $st_requete = "insert INTO `profession` (nom) values ";
            $a_colonnes = array();
            $i = 0;
            foreach ($this->a_professions_a_creer as $st_elem) {
                $a_colonnes[] = "(:prof$i)";
                $a_professions_a_creer[":prof$i"] = $st_elem;
                $i++;
            }
            $st_colonnes = join(',', $a_colonnes);
            $st_requete .= $st_colonnes;
            try {
                $this->connexionBD->initialise_params($a_professions_a_creer);
                $this->connexionBD->execute_requete($st_requete);
                $this->connexionBD->initialise_params($a_params_precs);
                $this->a_professions_a_creer = array();
                $this->a_idf_par_profession = null;
            } catch (Exception $e) {
                die('Sauvegarde profession impossible: ' . $e->getMessage() . ": $st_requete");
            }
            $this->charge_liste_idf_par_nom();
        }
    }

    public function charge_liste_idf_par_nom()
    {
        $this->a_idf_par_profession = $this->connexionBD->liste_clef_par_valeur("select idf, nom from `profession`");
    }

    public function vers_idf($pst_nom)
    {
        $pst_nom = ucfirst(strtolower(trim($pst_nom)));
        if (empty($pst_nom)) return 0;
        if (!$this->a_idf_par_profession) $this->charge_liste_idf_par_nom();
        if (array_key_exists(strval($pst_nom), $this->a_idf_par_profession))
            return $this->a_idf_par_profession[strval($pst_nom)];
        else {
            return 65535; // Max de Smallint => Marqueur pour détecter les erreurs éventuelles
        }
    }
}
