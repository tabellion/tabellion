<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

class Prenom
{
    private static $prenom;
    private $a_prenom_a_creer;
    protected $connexionBD;
    private $a_idf_par_prenom;

    private function __construct($pconnexionBD)
    {
        $this->connexionBD = $pconnexionBD;
        $this->a_prenoms_a_creer = array();
        $this->a_idf_par_prenom = [];
        $this->charge_liste_idf_par_nom();
    }

    public static function singleton($pconnexionBD)
    {
        if (!isset(self::$prenom)) {
            $c = __CLASS__;
            self::$prenom = new $c($pconnexionBD);
        }
        return self::$prenom;
    }

    public function ajoute($pst_prenom)
    {
        $pst_prenom = trim($pst_prenom);
        if ($pst_prenom != '' && !array_key_exists(strval($pst_prenom), $this->a_idf_par_prenom) && !in_array(strval($pst_prenom), $this->a_prenoms_a_creer)) {
            $this->a_prenoms_a_creer[] = strval($pst_prenom);
        }
    }

    public function sauve()
    {
        $a_params_precs = $this->connexionBD->params();
        $a_prenoms_a_creer = array();
        if (count($this->a_prenoms_a_creer) > 0) {
            $st_requete = "insert INTO `prenom` (libelle) values ";
            $a_colonnes = array();
            $i = 0;
            foreach ($this->a_prenoms_a_creer as $st_prenom) {
                $a_colonnes[] = "(:prenom$i)";
                $a_prenoms_a_creer[":prenom$i"] = $st_prenom;
                $i++;
            }
            $st_colonnes = join(',', $a_colonnes);
            $st_requete .= $st_colonnes;
            try {
                $this->connexionBD->initialise_params($a_prenoms_a_creer);
                $this->connexionBD->execute_requete($st_requete);
                $this->connexionBD->initialise_params($a_params_precs);
                $this->a_prenom_a_creer = null;
            } catch (Exception $e) {
                die('Sauvegarde Prenom impossible: ' . $e->getMessage() . ": $st_requete");
            }
            $this->charge_liste_idf_par_nom();
        }
        $st_requete = "select idf,libelle from prenom left join groupe_prenoms on (idf=idf_prenom) where idf_prenom is null and libelle regexp('[ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿA-Za-z]+')";
        $a_prenoms = $this->connexionBD->liste_valeur_par_clef($st_requete);
        $a_prenoms = array_unique($a_prenoms);
        if (count($a_prenoms) > 0) {
            $st_requete = "select idf,libelle from prenom_simple";
            $a_prenoms_simples = $this->connexionBD->liste_clef_par_valeur($st_requete);

            $a_groupe_prenoms_avec_idf = array();
            $a_groupe_prenoms_sans_idf = array();

            foreach ($a_prenoms as $i_idf => $st_prenom) {
                $a_champs = preg_split("/[,\s\/\=\&-]+/", $st_prenom);
                $a_champs = array_unique($a_champs);
                foreach ($a_champs as $st_champ) {
                    $st_champ = preg_replace("/[\"\'\(\)]+/", '', $st_champ);
                    if (!empty($st_champ))
                        if (array_key_exists($st_champ, $a_prenoms_simples))
                            if (array_key_exists($i_idf, $a_groupe_prenoms_avec_idf))
                                $a_groupe_prenoms_avec_idf[$i_idf][] = $a_prenoms_simples[$st_champ];
                            else
                                $a_groupe_prenoms_avec_idf[$i_idf] = array($a_prenoms_simples[$st_champ]);
                        else
                    if (array_key_exists($i_idf, $a_groupe_prenoms_sans_idf))
                            $a_groupe_prenoms_sans_idf[$i_idf][] = $st_champ;
                        else
                            $a_groupe_prenoms_sans_idf[$i_idf] = array($st_champ);
                }
            }
            if (count($a_groupe_prenoms_sans_idf) > 0) {
                $oPhonex = new phonex;
                // Masque les erreurs Notice d'offset de la classe Phonex
                error_reporting(E_ERROR | E_WARNING | E_PARSE);
                $a_prenoms_simples_a_creer = array();
                $i = 0;
                $st_requete = "insert ignore INTO `prenom_simple` (libelle,phonex) values ";
                $a_colonnes = array();
                foreach ($a_groupe_prenoms_sans_idf as $i_idf => $a_prenoms) {
                    $oPhonex = new phonex;
                    foreach ($a_prenoms as $st_prenom) {
                        if (!empty($st_prenom)) {
                            $oPhonex->build($st_prenom);
                            $sPhonex = $oPhonex->sString;
                            $a_prenoms_simples_a_creer[":prenom$i"] = $st_prenom;
                            $a_prenoms_simples_a_creer[":phonex$i"] = $sPhonex;
                            $a_colonnes[] = "(:prenom$i,:phonex$i)";
                            $i++;
                        }
                    }
                }
                $st_colonnes = join(',', $a_colonnes);
                $st_requete .= $st_colonnes;
                $this->connexionBD->initialise_params($a_prenoms_simples_a_creer);
                $this->connexionBD->execute_requete($st_requete);
                $this->connexionBD->initialise_params($a_params_precs);

                $st_requete = "select idf,libelle from prenom_simple";
                $a_prenom_simples = $this->connexionBD->liste_clef_par_valeur($st_requete);

                foreach ($a_groupe_prenoms_sans_idf as $i_idf => $a_prenoms) {
                    foreach ($a_prenoms as $st_prenom) {
                        if (array_key_exists($st_prenom, $a_prenom_simples)) {
                            if (array_key_exists($i_idf, $a_groupe_prenoms_avec_idf))
                                $a_groupe_prenoms_avec_idf[$i_idf][] = $a_prenom_simples[$st_prenom];
                            else
                                $a_groupe_prenoms_avec_idf[$i_idf] = array($a_prenom_simples[$st_prenom]);
                        }
                    }
                }
            }
            $a_groupes_a_creer = array();
            $i = 0;
            $st_requete = "insert ignore INTO `groupe_prenoms` (idf_prenom,idf_prenom_simple) values ";
            $a_colonnes = array();
            foreach ($a_groupe_prenoms_avec_idf as $i_idf_groupe => $a_idf_prenoms) {
                foreach ($a_idf_prenoms as $i_idf_prenom) {
                    $a_groupes_a_creer[":idf_groupe$i"] = $i_idf_groupe;
                    $a_groupes_a_creer[":idf_prenom$i"] = $i_idf_prenom;
                    $a_colonnes[] = "(:idf_groupe$i,:idf_prenom$i)";
                    $i++;
                }
            }
            $st_colonnes = join(',', $a_colonnes);
            $st_requete .= $st_colonnes;
            $this->connexionBD->initialise_params($a_groupes_a_creer);
            $this->connexionBD->execute_requete($st_requete);
            $this->connexionBD->initialise_params($a_params_precs);
            $this->a_prenoms_a_creer = array();
            $this->a_idf_par_prenom  = null;
        }
    }

    public function charge_liste_idf_par_nom()
    {
        $this->a_idf_par_prenom = $this->connexionBD->liste_clef_par_valeur("select idf,libelle from `prenom`");
    }

    public function vers_idf($pst_nom)
    {
        if (empty($pst_nom)) return 0;
        if (!$this->a_idf_par_prenom) $this->charge_liste_idf_par_nom();
        if (array_key_exists(strval($pst_nom), $this->a_idf_par_prenom))
            return $this->a_idf_par_prenom[strval($pst_nom)];
        else {
            return 16777215; // Max de Mediumint => Marqueur pour détecter les erreurs éventuelles  
        }
    }
}
