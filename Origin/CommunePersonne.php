<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

class CommunePersonne
{
    private static $communePersonne;
    private $a_communes_a_creer;
    private $a_idf_par_commune;
    protected $connexionBD;

    private function __construct($pconnexionBD)
    {
        $this->connexionBD = $pconnexionBD;
        $this->a_communes_a_creer = array();
        $this->a_idf_par_commune = [];
        $this->charge_liste_idf_par_nom();
    }

    public static function singleton($pconnexionBD)
    {
        if (!isset(self::$communePersonne)) {
            $c = __CLASS__;
            self::$communePersonne = new $c($pconnexionBD);
        }
        return self::$communePersonne;
    }

    public function ajoute($pst_commune)
    {
        if ($pst_commune != '' && !array_key_exists(strval(trim($pst_commune)), $this->a_idf_par_commune) && !in_array(strval(trim($pst_commune)), $this->a_communes_a_creer))
            $this->a_communes_a_creer[] = trim($pst_commune);
    }

    public function sauve()
    {
        $a_params_precs = $this->connexionBD->params();
        $a_communes_a_creer = array();
        if (count($this->a_communes_a_creer) > 0) {
            $st_requete = "insert INTO `commune_personne` (nom) values ";
            $a_colonnes = array();
            $i = 0;
            foreach ($this->a_communes_a_creer as $st_elem) {
                $a_colonnes[] = "(:commune$i)";
                $a_communes_a_creer[":commune$i"] = $st_elem;
                $i++;
            }
            $st_colonnes = join(',', $a_colonnes);
            $st_requete .= $st_colonnes;
            try {
                $this->connexionBD->initialise_params($a_communes_a_creer);
                $this->connexionBD->execute_requete($st_requete);
                $this->connexionBD->initialise_params($a_params_precs);
                $this->a_communes_a_creer = array();
                $this->a_idf_par_commune = null;
            } catch (Exception $e) {
                die('Sauvegarde CommunePersonne impossible: ' . $e->getMessage() . ": $st_requete");
            }
        }
        $this->charge_liste_idf_par_nom();
    }

    public function charge_liste_idf_par_nom()
    {
        $this->a_idf_par_commune = $this->connexionBD->liste_clef_par_valeur("select idf,nom from `commune_personne`");
    }

    public function vers_idf($pst_nom)
    {
        if (empty($pst_nom)) return 0;
        if (is_null($this->a_idf_par_commune)) $this->charge_liste_idf_par_nom();
        if (array_key_exists(strval($pst_nom), $this->a_idf_par_commune))
            return $this->a_idf_par_commune[strval($pst_nom)];
        else
            return 16777215; // Max de Mediumint => Marqueur pour détecter les erreurs éventuelles  
    }
}
