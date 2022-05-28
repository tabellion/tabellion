<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

class Union
{
    private static $union;
    private $a_union;
    protected $connexionBD;
    protected $type_acte;

    private function __construct($pconnexionBD)
    {
        $this->a_union = array();
        $this->connexionBD = $pconnexionBD;
        $this->type_acte = TypeActe::singleton($pconnexionBD);
    }

    public static function singleton($pconnexionBD)
    {
        if (!isset(self::$union)) {
            $c = __CLASS__;
            self::$union = new $c($pconnexionBD);
        }
        return self::$union;
    }

    public function ajoute($pi_idf_source, $pi_idf_commune, $pi_idf_acte, $pst_type_acte, $pi_idf_epoux, $pst_nom_epoux, $pi_idf_epouse, $pst_nom_epouse)
    {
        $this->a_union[] = array($pi_idf_source, $pi_idf_commune, $pi_idf_acte, $pst_type_acte, $pi_idf_epoux, $pst_nom_epoux, $pi_idf_epouse, $pst_nom_epouse);
    }

    /**
     * @param string $pst_valeur valeur à convertir
     * @return string champ CSV  
     */
    static function champ_csv($pst_valeur)
    {
        return is_null($pst_valeur) ? '\N' : "\"$pst_valeur\"";
    }

    /**
     * sauve la liste des unions en base   
     */
    public function sauve()
    {
        $st_requete = "insert ignore INTO `union` (idf_source,idf_commune,idf_acte,idf_type_acte,idf_epoux,patronyme_epoux,idf_epouse,patronyme_epouse) values ";
        $a_params_precs = $this->connexionBD->params();
        $a_unions_a_creer = array();
        $a_colonnes = array();
        $i = 0;
        foreach ($this->a_union as $a_ligne) {
            list($i_idf_source, $i_idf_commune, $i_idf_acte, $st_type_acte, $i_idf_epoux, $st_nom_epoux, $i_idf_epouse, $st_nom_epouse) = $a_ligne;
            $a_colonnes[] = "(:idf_source$i,:idf_commune$i,:idf_acte$i,:type_acte$i,:idf_epoux$i,:nom_epoux$i,:idf_epouse$i,:nom_epouse$i)";
            $a_unions_a_creer[":idf_source$i"] = $i_idf_source;
            $a_unions_a_creer[":idf_commune$i"] = $i_idf_commune;
            $a_unions_a_creer[":idf_acte$i"] = $i_idf_acte;
            $a_unions_a_creer[":type_acte$i"] = $this->type_acte->vers_idf($st_type_acte);
            $a_unions_a_creer[":idf_epoux$i"] = $i_idf_epoux;
            $a_unions_a_creer[":nom_epoux$i"] = $st_nom_epoux;
            $a_unions_a_creer[":idf_epouse$i"] = $i_idf_epouse;
            $a_unions_a_creer[":nom_epouse$i"] = $st_nom_epouse;
            $i++;
        }
        if (count($this->a_union) > 0) {
            $st_colonnes = join(',', $a_colonnes);
            $st_requete .= $st_colonnes;
            try {
                $this->connexionBD->initialise_params($a_unions_a_creer);
                $this->connexionBD->execute_requete($st_requete);
                $this->connexionBD->initialise_params($a_params_precs);
                $this->a_union = array();
            } catch (Exception $e) {
                die('Sauvegarde union impossible: ' . $e->getMessage());
            }
        }
    }

    /*
   * Crée une union dans la table Union
   * @param integer $pi_idf_source identifiant de la source
   * @param integer $pi_idf_commune identifiant de la commune
   * @param integer $pi_idf_acte identifiant de l'acte
   * @param integer $pi_idf_type_acte identifiant du type de l'acte
   * @param integer $pi_idf_epoux identifiant de l'époux
   * @param string  $pst_nom_epoux nom de l'époux
   * @param integer $pi_idf_epouse identifiant de l'épouse
   * @param string  $pst_nom_epouse nom de l'épouse
   */
    public function cree($pi_idf_source, $pi_idf_commune, $pi_idf_acte, $pi_idf_type_acte, $pi_idf_epoux, $pst_nom_epoux, $pi_idf_epouse, $pst_nom_epouse)
    {
        $this->connexionBD->initialise_params(array(':idf_source' => $pi_idf_source, ':idf_commune' => $pi_idf_commune, ':idf_acte' => $pi_idf_acte, ':idf_type_acte' => $pi_idf_type_acte, ':idf_epoux' => $pi_idf_epoux, ':nom_epoux' => $pst_nom_epoux, ':idf_epouse' => $pi_idf_epouse, ':nom_epouse' => $pst_nom_epouse));
        $st_requete = "insert into `union`(idf_source,idf_commune,idf_acte ,idf_type_acte,idf_epoux,patronyme_epoux,idf_epouse,patronyme_epouse) values(:idf_source,:idf_commune,:idf_acte,:idf_type_acte,:idf_epoux,:nom_epoux,:idf_epouse,:nom_epouse)";
        $this->connexionBD->execute_requete($st_requete);
    }
}
