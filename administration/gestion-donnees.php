<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../libs/phonex.cls.php';
require_once __DIR__ . '/chargement/chargement.php';
require_once __DIR__ . '/chargement/chargement-recens.php';
require_once __DIR__ . '/../Origin/CompteurPersonne.php';
require_once __DIR__ . '/../Origin/Personne.php';
require_once __DIR__ . '/../Origin/CommunePersonne.php';
require_once __DIR__ . '/../Origin/Patronyme.php';
require_once __DIR__ . '/../Origin/Prenom.php';
require_once __DIR__ . '/../Origin/Profession.php';
require_once __DIR__ . '/../Origin/CompteurActe.php';
require_once __DIR__ . '/../Origin/TypeActe.php';
require_once __DIR__ . '/../Origin/Acte.php';
require_once __DIR__ . '/../Origin/Union.php';
require_once __DIR__ . '/../Origin/StatsCommune.php';
require_once __DIR__ . '/../Origin/StatsPatronyme.php';
require_once __DIR__ . '/../Origin/ChargementNimV2.php';
require_once __DIR__ . '/../Origin/ChargementNimV3.php';
require_once __DIR__ . '/../Origin/Releveur.php';

// Redirect to identification
if (!$session->isAuthenticated()) {
    $session->setAttribute('url_retour', '/administration/gestion-communes.php');
    header('HTTP/1.0 401 Unauthorized');
    header('Location: /se-connecter.php');
    exit;
}
if (!in_array('CHGMT_EXPT', $user['privileges'])) {
    header('HTTP/1.0 401 Unauthorized');
    exit;
}

/**
 * Renvoie la liste des mariages pour la source et la commune données
 * sous la forme d'un table ou chaque ligne est (date,nom époux, prénom époux,nom épouse, prénom épouse) 
 * @param object $pconnexionBD Identifiant de la connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune identifiant de la commune
 * @return array liste des mariages existants
 **/
function liste_mariages_existant($pconnexionBD, $pi_idf_source, $pi_idf_commune)
{
    $st_requete = "SELECT a.date,p_epx.patronyme,prenom_epx.libelle,p_epse.patronyme,prenom_epse.libelle FROM `union`, personne p_epx, prenom prenom_epx, personne p_epse, prenom prenom_epse,acte a where `union`.idf_type_acte=" . IDF_MARIAGE . " and `union`.idf_source=$pi_idf_source and `union`.idf_commune=$pi_idf_commune and `union`.idf_epoux=p_epx.idf and p_epx.idf_type_presence=" . IDF_PRESENCE_INTV . " and `union`.idf_epouse=p_epse.idf and p_epx.idf_acte=a.idf and p_epx.idf_prenom=prenom_epx.idf and p_epse.idf_prenom=prenom_epse.idf";

    $a_lignes_unions = $pconnexionBD->sql_select_multiple($st_requete);
    $a_unions = array();
    foreach ($a_lignes_unions as $a_champs) {
        list($st_date, $st_nom_epx, $st_prn_epx, $st_nom_epse, $st_prn_epse) = $a_champs;
        $a_unions[strval($st_date)][strval($st_nom_epx)][strval($st_prn_epx)][strval($st_nom_epse)][strval($st_prn_epse)] = true;
    }
    return $a_unions;
}

/**
 * Renvoie la liste des divers pour la source et la commune données
 * sous la forme d'un table ou chaque ligne est (date,nom époux, prénom époux,nom épouse, prénom épouse) - Premier temps : seuls les CM sont vérifiés
 * ou acte divers ayant un couple 
 * @param object $pconnexionBD Identifiant de la connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune identifiant de la commune
 * @return array liste des mariages existants
 **/
function liste_divers_existant($pconnexionBD, $pi_idf_source, $pi_idf_commune)
{
    $st_requete = "SELECT a.date,p_epx.patronyme,prenom_epx.libelle,p_epse.patronyme,prenom_epse.libelle FROM `union`, personne p_epx, prenom prenom_epx, personne p_epse, prenom prenom_epse, acte a where `union`.idf_type_acte not in (" . IDF_MARIAGE . ',' . IDF_NAISSANCE . ',' . IDF_DECES . ") and `union`.idf_source=$pi_idf_source and `union`.idf_commune=$pi_idf_commune and `union`.idf_epoux=p_epx.idf and p_epx.idf_type_presence=" . IDF_PRESENCE_INTV . " and `union`.idf_epouse=p_epse.idf and p_epx.idf_acte=a.idf and p_epx.idf_prenom=prenom_epx.idf and p_epse.idf_prenom=prenom_epse.idf";

    $a_lignes_unions = $pconnexionBD->sql_select_multiple($st_requete);
    $a_unions = array();
    foreach ($a_lignes_unions as $a_champs) {
        list($st_date, $st_nom_epx, $st_prn_epx, $st_nom_epse, $st_prn_epse) = $a_champs;
        $a_unions[strval($st_date)][strval($st_nom_epx)][strval($st_prn_epx)][strval($st_nom_epse)][strval($st_prn_epse)] = true;
    }
    return $a_unions;
}

/**
 * Renvoie la liste des naissances pour la source et la commune données
 * sous la forme d'un table ou chaque ligne est (date,nom époux, prénom époux,nom épouse, prénom épouse) - Premier temps : seuls les CM sont vérifiés
 * ou acte divers ayant un couple 
 * @param object $pconnexionBD Identifiant de la connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune identifiant de la commune
 * @return array liste des mariages existants
 **/
function liste_naissances_existant($pconnexionBD, $pi_idf_source, $pi_idf_commune)
{
    $st_requete = "SELECT a.date,p.patronyme,prenom.libelle FROM personne p join prenom on (p.idf_prenom=prenom.idf), acte a where p.idf_acte=a.idf and a.idf_source=$pi_idf_source and a.idf_commune=$pi_idf_commune and a.idf_type_acte =" . IDF_NAISSANCE . " and p.idf_type_presence=" . IDF_PRESENCE_INTV;
    $a_lignes = $pconnexionBD->sql_select_multiple($st_requete);
    $a_intervenants = array();
    foreach ($a_lignes as $a_champs) {
        list($st_date, $st_nom, $st_prn) = $a_champs;
        $a_intervenants[strval($st_date)][strval($st_nom)][strval($st_prn)] = true;
    }
    return $a_intervenants;
}

/**
 * Renvoie la liste des deces pour la source et la commune données
 * sous la forme d'un table ou chaque ligne est (date,nom époux, prénom époux,nom épouse, prénom épouse) - Premier temps : seuls les CM sont vérifiés
 * ou acte divers ayant un couple 
 * @param object $pconnexionBD Identifiant de la connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune identifiant de la commune
 * @return array liste des mariages existants
 **/
function liste_deces_existant($pconnexionBD, $pi_idf_source, $pi_idf_commune)
{
    $st_requete = "SELECT a.date,p.patronyme,prenom.libelle FROM personne p join prenom on (p.idf_prenom=prenom.idf), acte a where p.idf_acte=a.idf and a.idf_source=$pi_idf_source and a.idf_commune=$pi_idf_commune and a.idf_type_acte =" . IDF_DECES . " and p.idf_type_presence=" . IDF_PRESENCE_INTV;
    $a_lignes = $pconnexionBD->sql_select_multiple($st_requete);
    $a_intervenants = array();
    foreach ($a_lignes as $a_champs) {
        list($st_date, $st_nom, $st_prn) = $a_champs;
        $a_intervenants[strval($st_date)][strval($st_nom)][strval($st_prn)] = true;
    }
    return $a_intervenants;
}

/**
 * Exporte les naissances au format Nimègue V2
 * @param object $pconnexionBD lien connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune_acte identifiant de la commune à exporter      
 * @param character $pc_idf_type_acte identifiant du type d'acte à exporter (type de naissance)
 * @param array $pa_liste_personnes liste des personnes à exporter (calculées par une requête SQL précédente)
 * @param array $pa_liste_actes liste des actes à exporter (calculées par une requête SQL précédente)  
 * @param object $pf pointeur sur le fichier de sortie
 */
function export_nai_nimv2($pconnexionBD, $pi_idf_source, $pi_idf_commune_acte, $pc_idf_type_acte, $pa_liste_personnes, $pa_liste_actes, $pf)
{
    // à adapter pour prendre le champ code insee
    list($st_code_insee, $st_nom_commune, $i_dpt_commune, $st_departement) = $pconnexionBD->sql_select_liste("select CONCAT(CAST(ca.code_insee AS CHAR(5)),'-',RIGHT(CAST(100+ca.numero_paroisse AS CHAR(3)),2)), ca.nom, dept.idf, dept.nom from commune_acte ca, departement dept WHERE LEFT(ca.code_insee/1000,2)=LEFT(dept.idf,2) AND ca.idf=$pi_idf_commune_acte");
    $a_prenom = $pconnexionBD->liste_valeur_par_clef("select idf, libelle from prenom");
    $a_profession = $pconnexionBD->liste_valeur_par_clef("select idf, nom from profession");
    $no_enregistrement = 10000;
    foreach ($pa_liste_personnes as $i_idf_acte => $a_personnes) {
        $a_champs = array();
        $i_nb_temoins = 0;
        $b_parrain_initialise = false;
        foreach ($a_personnes as $i_idf_personne => $a_personne) {
            list($i_idf_type_presence, $c_sexe, $st_patronyme, $i_idf_prenom, $i_idf_origine, $st_date_naissance, $st_age, $i_idf_profession, $st_commentaires, $i_idf_pere, $i_idf_mere, $i_est_decede) = $a_personne;

            switch ($i_idf_type_presence) {
                case IDF_PRESENCE_INTV:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $c_sexe;
                    $a_champs[] = $st_commentaires;
                    if (!empty($i_idf_pere)) {
                        $a_champs[] = $a_personnes[$i_idf_pere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_pere][3]];
                        $a_champs[] = $a_personnes[$i_idf_pere][8];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][7]) ? '' : $a_profession[$a_personnes[$i_idf_pere][7]];
                    } else
                        array_push($a_champs, "", "", "", "");
                    if (!empty($i_idf_mere)) {
                        $a_champs[] = $a_personnes[$i_idf_mere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_mere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_mere][3]];
                        $a_champs[] = $a_personnes[$i_idf_mere][8];
                    } else
                        array_push($a_champs, "", "", "");
                    break;
                case IDF_PRESENCE_PARRAIN:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $st_commentaires;
                    $b_parrain_initialise = true;
                    $i_nb_temoins++;
                    break;
                case IDF_PRESENCE_MARRAINE:
                    // cas pour traiter les actes dont seule la marraine est connue
                    if (!$b_parrain_initialise) {
                        array_push($a_champs, "", "", "");
                        $i_nb_temoins++;
                    }
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $st_commentaires;
                    $i_nb_temoins++;
                    break;
                default:
            }
        }
        list($idf_commune_acte, $idf_type_acte, $st_date, $st_date_rep, $st_cote, $st_libre, $st_commentaires, $st_url) = $pa_liste_actes[$i_idf_acte];
        array_unshift($a_champs, 'N', $st_date, $st_date_rep, $st_cote, $st_libre);
        array_unshift($a_champs, $i_dpt_commune, $st_departement); // code département, nom département
        array_unshift($a_champs, "NIMEGUE-V2", $st_code_insee, $st_nom_commune);
        // Crée les témoins manquants
        for ($i = $i_nb_temoins; $i < 2; $i++) {
            array_push($a_champs, "", "", "");
        }
        $st_commentaires = preg_replace('/\r\n/', '§', $st_commentaires);
        if (!empty($st_url)) {
            if (strpos($st_commentaires, $st_url) === false)
                $st_commentaires .= "§$st_url";
        }
        $a_champs[] = $st_commentaires;
        $no_enregistrement = $no_enregistrement + 1;
        $a_champs[] = $no_enregistrement; // Numéro d'enregistrement
        $a_champs[] = "";
        fwrite($pf, (implode(';', $a_champs)));
        fwrite($pf, "\r\n");
    }
}

/**
 * Exporte les deces au format Nimègue V3
 * @param object $pconnexionBD lien connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune_acte identifiant de la commune à exporter      
 * @param character $pc_idf_type_acte identifiant du type d'acte à exporter (type : décès)
 * @param array $pa_liste_personnes liste des personnes à exporter (calculées par une requête SQL précédente)
 * @param array $pa_liste_actes liste des actes à exporter (calculées par une requête SQL précédente)  
 * @param object $pf pointeur sur le fichier de sortie 
 */

function export_dec_nimv2($pconnexionBD, $pi_idf_source, $pi_idf_commune_acte, $pc_idf_type_acte, $pa_liste_personnes, $pa_liste_actes, $pf)
{
    // à adapter pour prendre le champ code insee
    list($st_code_insee, $st_nom_commune, $i_dpt_commune, $st_departement) = $pconnexionBD->sql_select_liste("select CONCAT(CAST(ca.code_insee AS CHAR(5)),'-',RIGHT(CAST(100+ca.numero_paroisse AS CHAR(3)),2)), ca.nom, dept.idf, dept.nom from commune_acte ca, departement dept WHERE LEFT(ca.code_insee/1000,2)=LEFT(dept.idf,2) AND ca.idf=$pi_idf_commune_acte");
    $no_enregistrement = 10000;
    $a_prenom = $pconnexionBD->liste_valeur_par_clef("select idf, libelle from prenom");
    $a_commune_personne = $pconnexionBD->liste_valeur_par_clef("select idf, nom from commune_personne");
    $a_profession = $pconnexionBD->liste_valeur_par_clef("select idf, nom from profession");
    $a_conjoint_h = $pconnexionBD->liste_valeur_par_clef("select idf_epoux, idf_epouse from `union` where idf_commune=$pi_idf_commune_acte and idf_source=$pi_idf_source and idf_type_acte=$pc_idf_type_acte");
    $a_conjoint_f = array_flip($a_conjoint_h);
    foreach ($pa_liste_personnes as $i_idf_acte => $a_personnes) {
        $a_champs = array();
        $i_nb_temoins = 0;
        foreach ($a_personnes as $i_idf_personne => $a_personne) {
            list($i_idf_type_presence, $c_sexe, $st_patronyme, $i_idf_prenom, $i_idf_origine, $st_date_naissance, $st_age, $i_idf_profession, $st_commentaires, $i_idf_pere, $i_idf_mere, $i_est_decede) = $a_personne;
            switch ($i_idf_type_presence) {
                case IDF_PRESENCE_INTV:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = empty($i_idf_origine) ? '' : $a_commune_personne[$i_idf_origine];
                    $a_champs[] = $st_date_naissance;
                    $a_champs[] = $c_sexe;
                    $a_champs[] = $st_age;
                    $a_champs[] = empty($i_idf_profession) ? '' : $a_profession[$i_idf_profession];
                    $a_champs[] = $st_commentaires;
                    switch ($c_sexe) {
                        case 'M':
                            if (array_key_exists($i_idf_personne, $a_conjoint_h)) {
                                $a_champs[] = $a_personnes[$a_conjoint_h[$i_idf_personne]][2];
                                $a_champs[] = empty($a_personnes[$a_conjoint_h[$i_idf_personne]][3]) ? '' : ucfirst(strtolower($a_prenom[$a_personnes[$a_conjoint_h[$i_idf_personne]][3]]));
                                $a_champs[] = $a_personnes[$a_conjoint_h[$i_idf_personne]][8];
                            } else
                                array_push($a_champs, "", "", "");
                            break;
                        case 'F':
                            if (array_key_exists($i_idf_personne, $a_conjoint_f)) {
                                $a_champs[] = $a_personnes[$a_conjoint_f[$i_idf_personne]][2];
                                $a_champs[] = empty($a_personnes[$a_conjoint_f[$i_idf_personne]][3]) ? '' : ucfirst(strtolower($a_prenom[$a_personnes[$a_conjoint_f[$i_idf_personne]][3]]));
                                $a_champs[] = $a_personnes[$a_conjoint_f[$i_idf_personne]][8];
                            } else
                                array_push($a_champs, "", "", "");
                            break;
                        default:
                            array_push($a_champs, "", "", "");
                    }
                    if (!empty($i_idf_pere)) {
                        $a_champs[] = $a_personnes[$i_idf_pere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_pere][3]];
                        $a_champs[] = $a_personnes[$i_idf_pere][8];
                    } else
                        array_push($a_champs, "", "", "");
                    if (!empty($i_idf_mere)) {
                        $a_champs[] = $a_personnes[$i_idf_mere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_mere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_mere][3]];
                        $a_champs[] = $a_personnes[$i_idf_mere][8];
                    } else
                        array_push($a_champs, "", "", "");
                    break;
                case IDF_PRESENCE_TEMOIN:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $st_commentaires;
                    $i_nb_temoins++;
                    break;
            }
        }
        list($idf_commune_acte, $idf_type_acte, $st_date, $st_date_rep, $st_cote, $st_libre, $st_commentaires, $st_url) = $pa_liste_actes[$i_idf_acte];
        array_unshift($a_champs, 'D', $st_date, $st_date_rep, $st_cote, $st_libre);
        array_unshift($a_champs, $i_dpt_commune, $st_departement); // code département, nom département
        array_unshift($a_champs, "NIMEGUE-V2", $st_code_insee, $st_nom_commune);
        // Crée les témoins manquants
        for ($i = $i_nb_temoins; $i < 2; $i++) {
            array_push($a_champs, "", "", "");
        }
        $st_commentaires = preg_replace('/\r\n/', '§', $st_commentaires);
        if (!empty($st_url)) {
            if (strpos($st_commentaires, $st_url) === false)
                $st_commentaires .= "§$st_url";
        }
        $a_champs[] = $st_commentaires;
        $no_enregistrement = $no_enregistrement + 1;
        $a_champs[] = $no_enregistrement; // Numéro d'enregistrement
        $a_champs[] = "";
        fwrite($pf, (implode(';', $a_champs)));
        fwrite($pf, "\r\n");
    }
}

/**
 * Exporte les mariages au format Nimègue V2
 * @param object $pconnexionBD lien connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune_acte identifiant de la commune à exporter      
 * @param character $pc_idf_type_acte identifiant du type d'acte à exporter (type : mariage)
 * @param array $pa_liste_personnes liste des personnes à exporter (calculées par une requête SQL précédente)
 * @param array $pa_liste_actes liste des actes à exporter (calculées par une requête SQL précédente)  
 * @param object $pf pointeur sur le fichier de sortie  
 */

function export_mar_nimv2($pconnexionBD, $pi_idf_source, $pi_idf_commune_acte, $pc_idf_type_acte, $pa_liste_personnes, $pa_liste_actes, $pf)
{
    // à adapter pour prendre le champ code insee
    list($st_code_insee, $st_nom_commune, $i_dpt_commune, $st_departement) = $pconnexionBD->sql_select_liste("select CONCAT(CAST(ca.code_insee AS CHAR(5)),'-',RIGHT(CAST(100+ca.numero_paroisse AS CHAR(3)),2)), ca.nom, dept.idf, dept.nom from commune_acte ca, departement dept WHERE LEFT(ca.code_insee/1000,2)=LEFT(dept.idf,2) AND ca.idf=$pi_idf_commune_acte");
    $no_enregistrement = 10000;
    $a_prenom = $pconnexionBD->liste_valeur_par_clef("select idf, libelle from prenom");
    $a_commune_personne = $pconnexionBD->liste_valeur_par_clef("select idf, nom from commune_personne");
    $a_profession = $pconnexionBD->liste_valeur_par_clef("select idf, nom from profession");
    $a_conjoint_h = $pconnexionBD->liste_valeur_par_clef("select idf_epoux, idf_epouse from `union` join `personne` on (idf_epouse=idf) where idf_commune=$pi_idf_commune_acte and idf_source=$pi_idf_source and idf_type_acte=$pc_idf_type_acte and idf_type_presence=" . IDF_PRESENCE_EXCJT);
    $a_conjoint_f = $pconnexionBD->liste_valeur_par_clef("select idf_epouse, idf_epoux from `union` join `personne` on (idf_epoux=idf) where idf_commune=$pi_idf_commune_acte and idf_source=$pi_idf_source and idf_type_acte=$pc_idf_type_acte and idf_type_presence=" . IDF_PRESENCE_EXCJT);
    foreach ($pa_liste_personnes as $i_idf_acte => $a_personnes) {
        $a_champs = array();
        $i_nb_temoins = 0;
        foreach ($a_personnes as $i_idf_personne => $a_personne) {

            list($i_idf_type_presence, $c_sexe, $st_patronyme, $i_idf_prenom, $i_idf_origine, $st_date_naissance, $st_age, $i_idf_profession, $st_commentaires, $i_idf_pere, $i_idf_mere, $i_est_decede) = $a_personne;

            switch ($i_idf_type_presence) {
                case IDF_PRESENCE_INTV:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = empty($i_idf_origine) ? '' : $a_commune_personne[$i_idf_origine];
                    $a_champs[] = $st_date_naissance;
                    $a_champs[] = $st_age;
                    $a_champs[] = empty($i_idf_profession) ? '' : $a_profession[$i_idf_profession];
                    switch ($c_sexe) {
                        case 'M':
                            $st_exconjoint = '';
                            if (array_key_exists($i_idf_personne, $a_conjoint_h)) {
                                $st_exconjoint .= empty($a_personnes[$a_conjoint_h[$i_idf_personne]][3]) ? '' : ucfirst(strtolower($a_prenom[$a_personnes[$a_conjoint_h[$i_idf_personne]][3]]));
                                $st_exconjoint .= ' ';
                                $st_exconjoint .= strtoupper($a_personnes[$a_conjoint_h[$i_idf_personne]][2]);
                                if ($a_personnes[$a_conjoint_h[$i_idf_personne]][8] != '') {
                                    $st_exconjoint .= ', ';
                                    $st_exconjoint .= $a_personnes[$a_conjoint_h[$i_idf_personne]][8];
                                }
                            }
                            array_push($a_champs, $st_exconjoint);
                            break;
                        case 'F':
                            $st_exconjoint = '';
                            if (array_key_exists($i_idf_personne, $a_conjoint_f)) {
                                $st_exconjoint .= empty($a_personnes[$a_conjoint_f[$i_idf_personne]][3]) ? '' : ucfirst(strtolower($a_prenom[$a_personnes[$a_conjoint_f[$i_idf_personne]][3]]));
                                $st_exconjoint .= ' ';
                                $st_exconjoint .= strtoupper($a_personnes[$a_conjoint_f[$i_idf_personne]][2]);
                                if ($a_personnes[$a_conjoint_f[$i_idf_personne]][8] != '') {
                                    $st_exconjoint .= ',';
                                    $st_exconjoint .= $a_personnes[$a_conjoint_f[$i_idf_personne]][8];
                                }
                            }
                            array_push($a_champs, $st_exconjoint);
                            break;
                        default:
                            $st_exconjoint = '';
                            if (array_key_exists($i_idf_personne, $a_conjoint_h)) {
                                $st_exconjoint .= empty($a_personnes[$a_conjoint_h[$i_idf_personne]][3]) ? '' : ucfirst(strtolower($a_prenom[$a_personnes[$a_conjoint_h[$i_idf_personne]][3]]));
                                $st_exconjoint .= ' ';
                                $st_exconjoint .= strtoupper($a_personnes[$a_conjoint_h[$i_idf_personne]][2]);
                                if ($a_personnes[$a_conjoint_h[$i_idf_personne]][8] != '') {
                                    $st_exconjoint .= ',';
                                    $st_exconjoint .= $a_personnes[$a_conjoint_h[$i_idf_personne]][8];
                                }
                            }
                            array_push($a_champs, $st_exconjoint);
                    }
                    $a_champs[] = $st_commentaires;

                    if (!empty($i_idf_pere)) {
                        $a_champs[] = $a_personnes[$i_idf_pere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_pere][3]];
                        $a_champs[] = $a_personnes[$i_idf_pere][8];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][7]) ? ''  : $a_profession[$a_personnes[$i_idf_pere][7]];
                    } else
                        array_push($a_champs, "", "", "", "");
                    if (!empty($i_idf_mere)) {
                        $a_champs[] = $a_personnes[$i_idf_mere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_mere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_mere][3]];
                        $a_champs[] = $a_personnes[$i_idf_mere][8];
                    } else
                        array_push($a_champs, "", "", "");
                    break;
                case IDF_PRESENCE_TEMOIN:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $st_commentaires;
                    $i_nb_temoins++;
                    break;
            }
        }
        list($idf_commune_acte, $idf_type_acte, $st_date, $st_date_rep, $st_cote, $st_libre, $st_commentaires, $st_url) = $pa_liste_actes[$i_idf_acte];
        array_unshift($a_champs, 'M', $st_date, $st_date_rep, $st_cote, $st_libre);
        array_unshift($a_champs, $i_dpt_commune, $st_departement); // code département, nom département
        array_unshift($a_champs, "NIMEGUE-V2", $st_code_insee, $st_nom_commune);
        // Crée les témoins manquants
        for ($i = $i_nb_temoins; $i < 4; $i++) {
            array_push($a_champs, "", "", "");
        }
        $st_commentaires = preg_replace('/\r\n/', '§', $st_commentaires);
        if (!empty($st_url)) {
            if (strpos($st_commentaires, $st_url) === false)
                $st_commentaires .= "§$st_url";
        }
        $a_champs[] = $st_commentaires;
        $no_enregistrement = $no_enregistrement + 1;
        $a_champs[] = $no_enregistrement; // Numéro d'enregistrement
        $a_champs[] = "";
        fwrite($pf, (implode(';', $a_champs)));
        fwrite($pf, "\r\n");
    }
}

/**
 * Exporte les actes divers au format Nimègue V2
 * @param object $pconnexionBD lien connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune_acte identifiant de la commune à exporter      
 * @param array $pa_liste_personnes liste des personnes à exporter (calculées par une requête SQL précédente)
 * @param array $pa_liste_actes liste des actes à exporter (calculées par une requête SQL précédente)  
 * @param object $pf pointeur sur le fichier de sortie   
 */

function export_div_nimv2($pconnexionBD, $pi_idf_source, $pi_idf_commune_acte, $pa_liste_personnes, $pa_liste_actes, $pf)
{
    list($st_code_insee, $st_nom_commune, $i_dpt_commune, $st_departement) = $pconnexionBD->sql_select_liste("select CONCAT(CAST(ca.code_insee AS CHAR(5)),'-',RIGHT(CAST(100+ca.numero_paroisse AS CHAR(3)),2)), ca.nom, dept.idf, dept.nom from commune_acte ca, departement dept WHERE LEFT(ca.code_insee/1000,2)=LEFT(dept.idf,2) AND ca.idf=$pi_idf_commune_acte");
    $no_enregistrement = 10000;
    $a_prenom = $pconnexionBD->liste_valeur_par_clef("select idf, libelle from prenom");
    $a_commune_personne = $pconnexionBD->liste_valeur_par_clef("select idf, nom from commune_personne");
    $a_profession = $pconnexionBD->liste_valeur_par_clef("select idf, nom from profession");
    $a_type_acte = $pconnexionBD->sql_select_multiple_par_idf("select idf, nom,sigle_nimegue from type_acte");
    $a_conjoint_h = $pconnexionBD->liste_valeur_par_clef("select idf_epoux, idf_epouse from `union` join `personne` on (idf_epouse=idf) where idf_commune=$pi_idf_commune_acte and idf_source=$pi_idf_source and idf_type_acte not in (" . IDF_NAISSANCE . "," . IDF_MARIAGE . "," . IDF_DECES . ") and idf_type_presence=" . IDF_PRESENCE_EXCJT);
    $a_conjoint_f = $pconnexionBD->liste_valeur_par_clef("select idf_epouse, idf_epoux from `union` join `personne` on (idf_epoux=idf) where idf_commune=$pi_idf_commune_acte and idf_source=$pi_idf_source and idf_type_acte not in (" . IDF_NAISSANCE . "," . IDF_MARIAGE . "," . IDF_DECES . ") and idf_type_presence=" . IDF_PRESENCE_EXCJT);
    foreach ($pa_liste_personnes as $i_idf_acte => $a_personnes) {
        $a_champs = array();
        $i_nb_personnes = 0;
        $i_nb_temoins = 0;
        foreach ($a_personnes as $i_idf_personne => $a_personne) {

            list($i_idf_type_presence, $c_sexe, $st_patronyme, $i_idf_prenom, $i_idf_origine, $st_date_naissance, $st_age, $i_idf_profession, $st_commentaires, $i_idf_pere, $i_idf_mere, $i_est_decede) = $a_personne;

            switch ($i_idf_type_presence) {
                case IDF_PRESENCE_INTV:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $c_sexe;
                    $a_champs[] = empty($i_idf_origine) ? '' : $a_commune_personne[$i_idf_origine];
                    $a_champs[] = $st_date_naissance;
                    $a_champs[] = $st_age;
                    $a_champs[] = empty($i_idf_profession) ? '' : $a_profession[$i_idf_profession];
                    switch ($c_sexe) {
                        case 'M':
                            $st_exconjoint = '';
                            if (array_key_exists($i_idf_personne, $a_conjoint_h)) {
                                $st_exconjoint .= empty($a_personnes[$a_conjoint_h[$i_idf_personne]][3]) ? '' : ucfirst(strtolower($a_prenom[$a_personnes[$a_conjoint_h[$i_idf_personne]][3]]));
                                $st_exconjoint .= ' ';
                                $st_exconjoint .= strtoupper($a_personnes[$a_conjoint_h[$i_idf_personne]][2]);
                                if ($a_personnes[$a_conjoint_h[$i_idf_personne]][8] != '') {
                                    $st_exconjoint .= ', ';
                                    $st_exconjoint .= $a_personnes[$a_conjoint_h[$i_idf_personne]][8];
                                }
                            }
                            array_push($a_champs, $st_exconjoint);
                            break;
                        case 'F':
                            $st_exconjoint = '';
                            if (array_key_exists($i_idf_personne, $a_conjoint_f)) {
                                $st_exconjoint .= empty($a_personnes[$a_conjoint_f[$i_idf_personne]][3]) ? '' : ucfirst(strtolower($a_prenom[$a_personnes[$a_conjoint_f[$i_idf_personne]][3]]));
                                $st_exconjoint .= ' ';
                                $st_exconjoint .= strtoupper($a_personnes[$a_conjoint_f[$i_idf_personne]][2]);
                                if ($a_personnes[$a_conjoint_f[$i_idf_personne]][8] != '') {
                                    $st_exconjoint .= ',';
                                    $st_exconjoint .= $a_personnes[$a_conjoint_f[$i_idf_personne]][8];
                                }
                            }
                            array_push($a_champs, $st_exconjoint);
                            break;
                        default:
                            $st_exconjoint = '';
                            if (array_key_exists($i_idf_personne, $a_conjoint_h)) {
                                $st_exconjoint .= empty($a_personnes[$a_conjoint_h[$i_idf_personne]][3]) ? '' : ucfirst(strtolower($a_prenom[$a_personnes[$a_conjoint_h[$i_idf_personne]][3]]));
                                $st_exconjoint .= ' ';
                                $st_exconjoint .= strtoupper($a_personnes[$a_conjoint_h[$i_idf_personne]][2]);
                                if ($a_personnes[$a_conjoint_h[$i_idf_personne]][8] != '') {
                                    $st_exconjoint .= ',';
                                    $st_exconjoint .= $a_personnes[$a_conjoint_h[$i_idf_personne]][8];
                                }
                            }
                            array_push($a_champs, $st_exconjoint);
                    }
                    $a_champs[] = $st_commentaires;
                    if (!empty($i_idf_pere)) {
                        $a_champs[] = $a_personnes[$i_idf_pere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_pere][3]];
                        $a_champs[] = $a_personnes[$i_idf_pere][8];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][7]) ? ''  : $a_profession[$a_personnes[$i_idf_pere][7]];
                    } else
                        array_push($a_champs, "", "", "", "");
                    if (!empty($i_idf_mere)) {
                        $a_champs[] = $a_personnes[$i_idf_mere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_mere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_mere][3]];
                        $a_champs[] = $a_personnes[$i_idf_mere][8];
                    } else
                        array_push($a_champs, "", "", "");
                    break;
                case IDF_PRESENCE_TEMOIN:
                    if ($i_nb_personnes == 1) {
                        // Si le premier témoin en seconde position, le second intervenant n'a pas été saisi
                        // ses champs doivent donc être complétés
                        array_push($a_champs, "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "");
                    }
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $st_commentaires;
                    $i_nb_temoins++;
                    break;
            }
            $i_nb_personnes++;
        }
        list($idf_commune_acte, $idf_type_acte, $st_date, $st_date_rep, $st_cote, $st_libre, $st_commentaires, $st_url) = $pa_liste_actes[$i_idf_acte];
        list($st_type_acte, $st_sigle_acte) = $a_type_acte[$idf_type_acte];
        array_unshift($a_champs, $st_sigle_acte, $st_type_acte);
        array_unshift($a_champs, 'V', $st_date, $st_date_rep, $st_cote, $st_libre);
        array_unshift($a_champs, $i_dpt_commune, $st_departement); // code département, nom département
        array_unshift($a_champs, "NIMEGUE-V2", $st_code_insee, $st_nom_commune);
        // Crée les témoins manquants
        for ($i = $i_nb_temoins; $i < 4; $i++) {
            array_push($a_champs, "", "", "");
        }
        $st_commentaires = preg_replace('/\r\n/', '§', $st_commentaires);
        if (!empty($st_url)) {
            if (strpos($st_commentaires, $st_url) === false)
                $st_commentaires .= "§$st_url";
        }
        $a_champs[] = $st_commentaires;
        $no_enregistrement = $no_enregistrement + 1;
        $a_champs[] = $no_enregistrement; // Numéro d'enregistrement
        $a_champs[] = "";
        fwrite($pf, (implode(';', $a_champs)));
        fwrite($pf, "\r\n");
    }
}

/**
 * Exporte les naissances au format Nimègue V3
 * @param object $pconnexionBD lien connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune_acte identifiant de la commune à exporter      
 * @param character $pc_idf_type_acte identifiant du type d'acte à exporter (type de naissance)
 * @param array $pa_liste_personnes liste des personnes à exporter (calculées par une requête SQL précédente)
 * @param array $pa_liste_actes liste des actes à exporter (calculées par une requête SQL précédente)  
 * @param object $pf pointeur sur le fichier de sortie
 */

function export_nai_nimv3($pconnexionBD, $pi_idf_source, $pi_idf_commune_acte, $pc_idf_type_acte, $pa_liste_personnes, $pa_liste_actes, $pf)
{
    // à adapter pour prendre le champ code insee
    list($st_code_insee, $st_nom_commune, $i_dpt_commune, $st_departement) = $pconnexionBD->sql_select_liste("select CONCAT(CAST(ca.code_insee AS CHAR(5)),'-',RIGHT(CAST(100+ca.numero_paroisse AS CHAR(3)),2)), ca.nom, dept.idf, dept.nom from commune_acte ca, departement dept WHERE LEFT(ca.code_insee/1000,2)=LEFT(dept.idf,2) AND ca.idf=$pi_idf_commune_acte");
    $a_prenom = $pconnexionBD->liste_valeur_par_clef("select idf, libelle from prenom");
    $a_profession = $pconnexionBD->liste_valeur_par_clef("select idf, nom from profession");
    $no_enregistrement = 10000;
    foreach ($pa_liste_personnes as $i_idf_acte => $a_personnes) {
        $a_champs = array();
        $i_nb_temoins = 0;
        $b_parrain_initialise = false;
        foreach ($a_personnes as $i_idf_personne => $a_personne) {
            list($i_idf_type_presence, $c_sexe, $st_patronyme, $i_idf_prenom, $i_idf_origine, $st_date_naissance, $st_age, $i_idf_profession, $st_commentaires, $i_idf_pere, $i_idf_mere, $i_est_decede) = $a_personne;
            switch ($i_idf_type_presence) {
                case IDF_PRESENCE_INTV:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $c_sexe;
                    $a_champs[] = $st_commentaires;
                    if (!empty($i_idf_pere)) {
                        $a_champs[] = $a_personnes[$i_idf_pere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_pere][3]];
                        $a_champs[] = $a_personnes[$i_idf_pere][8];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][7]) ? '' : $a_profession[$a_personnes[$i_idf_pere][7]];
                    } else
                        array_push($a_champs, "", "", "", "");
                    if (!empty($i_idf_mere)) {
                        $a_champs[] = $a_personnes[$i_idf_mere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_mere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_mere][3]];
                        $a_champs[] = $a_personnes[$i_idf_mere][8];
                        $a_champs[] = empty($a_personnes[$i_idf_mere][7]) ? '' : $a_profession[$a_personnes[$i_idf_mere][7]];
                    } else
                        array_push($a_champs, "", "", "", "");
                    break;
                case IDF_PRESENCE_PARRAIN:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $st_commentaires;
                    $b_parrain_initialise = true;
                    $i_nb_temoins++;
                    break;
                case IDF_PRESENCE_MARRAINE:
                    // cas pour traiter les actes dont seule la marraine est connue
                    if (!$b_parrain_initialise) {
                        array_push($a_champs, "", "", "");
                        $i_nb_temoins++;
                    }
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $st_commentaires;
                    $i_nb_temoins++;
                    break;
            }
        }
        list($idf_commune_acte, $idf_type_acte, $st_date, $st_date_rep, $st_cote, $st_libre, $st_commentaires, $st_permalien) = $pa_liste_actes[$i_idf_acte];
        array_unshift($a_champs, 'N', $st_date, $st_date_rep, $st_cote, $st_libre);
        array_unshift($a_champs, $i_dpt_commune, $st_departement); // code département, nom département
        array_unshift($a_champs, "NIMEGUEV3", $st_code_insee, $st_nom_commune);
        // Crée les témoins manquants
        for ($i = $i_nb_temoins; $i < 2; $i++) {
            array_push($a_champs, "", "", "");
        }
        $st_commentaires = preg_replace('/\r\n/', '§', $st_commentaires);
        $no_enregistrement = $no_enregistrement + 1;
        $a_champs[] = trim($st_commentaires);
        $a_champs[] = $no_enregistrement; // Numéro d'enregistrement
        $a_champs[] = $st_permalien;
        fwrite($pf, (implode(';', $a_champs)));
        fwrite($pf, "\r\n");
    }
}

/**
 * Exporte les deces au format Nimègue V3
 * @param object $pconnexionBD lien connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune_acte identifiant de la commune à exporter      
 * @param character $pc_idf_type_acte identifiant du type d'acte à exporter (type : décès)
 * @param array $pa_liste_personnes liste des personnes à exporter (calculées par une requête SQL précédente)
 * @param array $pa_liste_actes liste des actes à exporter (calculées par une requête SQL précédente)  
 * @param object $pf pointeur sur le fichier de sortie 
 */

function export_dec_nimv3($pconnexionBD, $pi_idf_source, $pi_idf_commune_acte, $pc_idf_type_acte, $pa_liste_personnes, $pa_liste_actes, $pf)
{
    // à adapter pour prendre le champ code insee
    list($st_code_insee, $st_nom_commune, $i_dpt_commune, $st_departement) = $pconnexionBD->sql_select_liste("select CONCAT(CAST(ca.code_insee AS CHAR(5)),'-',RIGHT(CAST(100+ca.numero_paroisse AS CHAR(3)),2)), ca.nom, dept.idf, dept.nom from commune_acte ca, departement dept WHERE LEFT(ca.code_insee/1000,2)=LEFT(dept.idf,2) AND ca.idf=$pi_idf_commune_acte");
    $a_prenom = $pconnexionBD->liste_valeur_par_clef("select idf, libelle from prenom");
    $a_commune_personne = $pconnexionBD->liste_valeur_par_clef("select idf, nom from commune_personne");
    $a_profession = $pconnexionBD->liste_valeur_par_clef("select idf, nom from profession");
    $a_conjoint_h = $pconnexionBD->liste_valeur_par_clef("select idf_epoux, idf_epouse from `union` where idf_commune=$pi_idf_commune_acte and idf_source=$pi_idf_source and idf_type_acte=$pc_idf_type_acte");
    $a_conjoint_f = array_flip($a_conjoint_h);
    $no_enregistrement = 10000;
    foreach ($pa_liste_personnes as $i_idf_acte => $a_personnes) {
        $a_champs = array();
        $i_nb_temoins = 0;
        foreach ($a_personnes as $i_idf_personne => $a_personne) {
            list($i_idf_type_presence, $c_sexe, $st_patronyme, $i_idf_prenom, $i_idf_origine, $st_date_naissance, $st_age, $i_idf_profession, $st_commentaires, $i_idf_pere, $i_idf_mere, $i_est_decede) = $a_personne;
            switch ($i_idf_type_presence) {
                case IDF_PRESENCE_INTV:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = empty($i_idf_origine) ? '' : $a_commune_personne[$i_idf_origine];
                    $a_champs[] = $st_date_naissance;
                    $a_champs[] = $c_sexe;
                    $a_champs[] = $st_age;
                    $a_champs[] = $st_commentaires;
                    $a_champs[] = empty($i_idf_profession) ? '' : $a_profession[$i_idf_profession];
                    switch ($c_sexe) {
                        case 'M':
                            if (array_key_exists($i_idf_personne, $a_conjoint_h)) {
                                $a_champs[] = $a_personnes[$a_conjoint_h[$i_idf_personne]][2];
                                $a_champs[] = empty($a_personnes[$a_conjoint_h[$i_idf_personne]][3]) ? '' : $a_prenom[$a_personnes[$a_conjoint_h[$i_idf_personne]][3]];
                                $a_champs[] = $a_personnes[$a_conjoint_h[$i_idf_personne]][8];
                                $a_champs[] = empty($a_personnes[$a_conjoint_h[$i_idf_personne]][7]) ? '' : $a_profession[$a_personnes[$a_conjoint_h[$i_idf_personne]][7]];
                            } else
                                array_push($a_champs, "", "", "", "");
                            break;
                        case 'F':
                            if (array_key_exists($i_idf_personne, $a_conjoint_f)) {
                                $a_champs[] = $a_personnes[$a_conjoint_f[$i_idf_personne]][2];
                                $a_champs[] = empty($a_personnes[$a_conjoint_f[$i_idf_personne]][3]) ? '' : $a_prenom[$a_personnes[$a_conjoint_f[$i_idf_personne]][3]];
                                $a_champs[] = $a_personnes[$a_conjoint_f[$i_idf_personne]][8];
                                $a_champs[] = empty($a_personnes[$a_conjoint_f[$i_idf_personne]][7]) ? '' : $a_profession[$a_personnes[$a_conjoint_f[$i_idf_personne]][7]];
                            } else
                                array_push($a_champs, "", "", "", "");
                            break;
                        default:
                            array_push($a_champs, "", "", "", "");
                    }
                    if (!empty($i_idf_pere)) {
                        $a_champs[] = $a_personnes[$i_idf_pere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_pere][3]];
                        $a_champs[] = $a_personnes[$i_idf_pere][8];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][7]) ? '' : $a_profession[$a_personnes[$i_idf_pere][7]];
                    } else
                        array_push($a_champs, "", "", "", "");
                    if (!empty($i_idf_mere)) {
                        $a_champs[] = $a_personnes[$i_idf_mere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_mere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_mere][3]];
                        $a_champs[] = $a_personnes[$i_idf_mere][8];
                        $a_champs[] = empty($a_personnes[$i_idf_mere][7]) ? '' : $a_profession[$a_personnes[$i_idf_mere][7]];
                    } else
                        array_push($a_champs, "", "", "", "");
                    break;
                case IDF_PRESENCE_TEMOIN:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $st_commentaires;
                    $i_nb_temoins++;
                    break;
            }
        }
        list($idf_commune_acte, $idf_type_acte, $st_date, $st_date_rep, $st_cote, $st_libre, $st_commentaires, $st_permalien) = $pa_liste_actes[$i_idf_acte];
        array_unshift($a_champs, 'D', $st_date, $st_date_rep, $st_cote, $st_libre);
        array_unshift($a_champs, $i_dpt_commune, $st_departement); // code département, nom département
        array_unshift($a_champs, "NIMEGUEV3", $st_code_insee, $st_nom_commune);
        // Crée les témoins manquants
        for ($i = $i_nb_temoins; $i < 2; $i++) {
            array_push($a_champs, "", "", "");
        }
        $st_commentaires = preg_replace('/\r\n/', '§', $st_commentaires);
        $a_champs[] = trim($st_commentaires);
        $no_enregistrement = $no_enregistrement + 1;
        $a_champs[] = $no_enregistrement; // Numéro d'enregistrement
        $a_champs[] = $st_permalien;
        fwrite($pf, (implode(';', $a_champs)));
        fwrite($pf, "\r\n");
    }
}

/**
 * Exporte les mariages au format Nimègue V3
 * @param object $pconnexionBD lien connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune_acte identifiant de la commune à exporter
 * @param character $pc_idf_type_acte identifiant du type d'acte à exporter (type : mariage)
 * @param array $pa_liste_personnes liste des personnes à exporter (calculées par une requête SQL précédente)
 * @param array $pa_liste_actes liste des actes à exporter (calculées par une requête SQL précédente)  
 * @param object $pf pointeur sur le fichier de sortie
 */

function export_mar_nimv3($pconnexionBD, $pi_idf_source, $pi_idf_commune_acte, $pc_idf_type_acte, $pa_liste_personnes, $pa_liste_actes, $pf)
{
    // à adapter pour prendre le champ code insee
    list($st_code_insee, $st_nom_commune, $i_dpt_commune, $st_departement) = $pconnexionBD->sql_select_liste("select CONCAT(CAST(ca.code_insee AS CHAR(5)),'-',RIGHT(CAST(100+ca.numero_paroisse AS CHAR(3)),2)), ca.nom, dept.idf, dept.nom from commune_acte ca, departement dept WHERE LEFT(ca.code_insee/1000,2)=LEFT(dept.idf,2) AND ca.idf=$pi_idf_commune_acte");
    $a_prenom = $pconnexionBD->liste_valeur_par_clef("select idf, libelle from prenom");
    $a_commune_personne = $pconnexionBD->liste_valeur_par_clef("select idf, nom from commune_personne");
    $a_profession = $pconnexionBD->liste_valeur_par_clef("select idf, nom from profession");
    $a_conjoint_h = $pconnexionBD->liste_valeur_par_clef("select idf_epoux, idf_epouse from `union` join `personne` on (idf_epouse=idf) where idf_commune=$pi_idf_commune_acte and idf_source=$pi_idf_source and idf_type_acte=$pc_idf_type_acte and `personne`.idf_type_presence=" . IDF_PRESENCE_EXCJT);
    $a_conjoint_f = $pconnexionBD->liste_valeur_par_clef("select idf_epouse, idf_epoux from `union` join `personne` on (idf_epoux=idf) where idf_commune=$pi_idf_commune_acte and idf_source=$pi_idf_source and idf_type_acte=$pc_idf_type_acte and `personne`.idf_type_presence=" . IDF_PRESENCE_EXCJT);
    $no_enregistrement = 10000;
    foreach ($pa_liste_personnes as $i_idf_acte => $a_personnes) {
        $a_champs = array();
        $i_nb_temoins = 0;
        foreach ($a_personnes as $i_idf_personne => $a_personne) {
            list($i_idf_type_presence, $c_sexe, $st_patronyme, $i_idf_prenom, $i_idf_origine, $st_date_naissance, $st_age, $i_idf_profession, $st_commentaires, $i_idf_pere, $i_idf_mere, $i_est_decede) = $a_personne;
            if ($i_idf_origine == 16777215) $i_idf_origine = 0;
            switch ($i_idf_type_presence) {
                case IDF_PRESENCE_INTV:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = empty($i_idf_origine) ? '' : $a_commune_personne[$i_idf_origine];
                    $a_champs[] = $st_date_naissance;
                    $a_champs[] = $st_age;
                    $a_champs[] = $st_commentaires;
                    $a_champs[] = empty($i_idf_profession) ? '' : $a_profession[$i_idf_profession];
                    switch ($c_sexe) {
                        case 'M':
                            if (array_key_exists($i_idf_personne, $a_conjoint_h)) {
                                $a_champs[] = $a_personnes[$a_conjoint_h[$i_idf_personne]][2];
                                $a_champs[] = empty($a_personnes[$a_conjoint_h[$i_idf_personne]][3]) ? '' : $a_prenom[$a_personnes[$a_conjoint_h[$i_idf_personne]][3]];
                                $a_champs[] = $a_personnes[$a_conjoint_h[$i_idf_personne]][8];
                            } else
                                array_push($a_champs, "", "", "");
                            break;
                        case 'F':
                            if (array_key_exists($i_idf_personne, $a_conjoint_f)) {
                                $a_champs[] = $a_personnes[$a_conjoint_f[$i_idf_personne]][2];
                                $a_champs[] = empty($a_personnes[$a_conjoint_f[$i_idf_personne]][3]) ? '' : $a_prenom[$a_personnes[$a_conjoint_f[$i_idf_personne]][3]];
                                $a_champs[] = $a_personnes[$a_conjoint_f[$i_idf_personne]][8];
                            } else
                                array_push($a_champs, "", "", "");
                            break;
                        default:
                            if (array_key_exists($i_idf_personne, $a_conjoint_h)) {
                                $a_champs[] = $a_personnes[$a_conjoint_h[$i_idf_personne]][2];
                                $a_champs[] = empty($a_personnes[$a_conjoint_h[$i_idf_personne]][3]) ? '' : $a_prenom[$a_personnes[$a_conjoint_h[$i_idf_personne]][3]];
                                $a_champs[] = $a_personnes[$a_conjoint_h[$i_idf_personne]][8];
                            } else
                                array_push($a_champs, "", "", "");
                    }
                    if (!empty($i_idf_pere)) {
                        $a_champs[] = $a_personnes[$i_idf_pere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_pere][3]];
                        $a_champs[] = $a_personnes[$i_idf_pere][8];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][7]) ? ''  : $a_profession[$a_personnes[$i_idf_pere][7]];
                    } else
                        array_push($a_champs, "", "", "", "");
                    if (!empty($i_idf_mere)) {
                        $a_champs[] = $a_personnes[$i_idf_mere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_mere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_mere][3]];
                        $a_champs[] = $a_personnes[$i_idf_mere][8];
                        $a_champs[] = empty($a_personnes[$i_idf_mere][7]) ? '' : $a_profession[$a_personnes[$i_idf_mere][7]];
                    } else
                        array_push($a_champs, "", "", "", "");
                    break;
                case IDF_PRESENCE_TEMOIN:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $st_commentaires;
                    $i_nb_temoins++;
                    break;
            }
        }
        list($idf_commune_acte, $idf_type_acte, $st_date, $st_date_rep, $st_cote, $st_libre, $st_commentaires, $st_permalien) = $pa_liste_actes[$i_idf_acte];
        array_unshift($a_champs, 'M', $st_date, $st_date_rep, $st_cote, $st_libre);
        array_unshift($a_champs, $i_dpt_commune, $st_departement); // code département, nom département
        array_unshift($a_champs, "NIMEGUEV3", $st_code_insee, $st_nom_commune);
        // Crée les témoins manquants
        for ($i = $i_nb_temoins; $i < 4; $i++) {
            array_push($a_champs, "", "", "");
        }
        $st_commentaires = preg_replace('/\r\n/', '§', $st_commentaires);
        $a_champs[] = trim($st_commentaires);
        $no_enregistrement = $no_enregistrement + 1;
        $a_champs[] = $no_enregistrement; // Numéro d'enregistrement
        $a_champs[] = $st_permalien;
        fwrite($pf, (implode(';', $a_champs)));
        fwrite($pf, "\r\n");
    }
}


/**
 * Exporte les actes divers au format Nimègue V3
 * @param object $pconnexionBD lien connexion BD
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune_acte identifiant de la commune à exporter      
 * @param array $pa_liste_personnes liste des personnes à exporter (calculéees par une requête SQL précédente)
 * @param array $pa_liste_actes liste des actes à exporter (calculées par une requête SQL précédente)  
 * @param object $pf pointeur sur le fichier de sortie   
 */

function export_div_nimv3($pconnexionBD, $pi_idf_source, $pi_idf_commune_acte, $pa_liste_personnes, $pa_liste_actes, $pf)
{
    list($st_code_insee, $st_nom_commune, $i_dpt_commune, $st_departement) = $pconnexionBD->sql_select_liste("select CONCAT(CAST(ca.code_insee AS CHAR(5)),'-',RIGHT(CAST(100+ca.numero_paroisse AS CHAR(3)),2)), ca.nom, dept.idf, dept.nom from commune_acte ca, departement dept WHERE LEFT(ca.code_insee/1000,2)=LEFT(dept.idf,2) AND ca.idf=$pi_idf_commune_acte");
    $a_prenom = $pconnexionBD->liste_valeur_par_clef("select idf, libelle from prenom");
    $a_commune_personne = $pconnexionBD->liste_valeur_par_clef("select idf, nom from commune_personne");
    $a_profession = $pconnexionBD->liste_valeur_par_clef("select idf, nom from profession");
    $a_type_acte = $pconnexionBD->sql_select_multiple_par_idf("select idf, nom,sigle_nimegue from type_acte");
    $a_conjoint_h = $pconnexionBD->liste_valeur_par_clef("select idf_epoux, idf_epouse from `union` join `personne` on (idf_epouse=idf) where idf_commune=$pi_idf_commune_acte and idf_source=$pi_idf_source and idf_type_acte not in (" . IDF_NAISSANCE . "," . IDF_MARIAGE . "," . IDF_DECES . ") and idf_type_presence=" . IDF_PRESENCE_EXCJT);
    $a_conjoint_f = $pconnexionBD->liste_valeur_par_clef("select idf_epouse, idf_epoux from `union` join `personne` on (idf_epoux=idf) where idf_commune=$pi_idf_commune_acte and idf_source=$pi_idf_source and idf_type_acte not in (" . IDF_NAISSANCE . "," . IDF_MARIAGE . "," . IDF_DECES . ") and idf_type_presence=" . IDF_PRESENCE_EXCJT);
    $no_enregistrement = 10000;
    foreach ($pa_liste_personnes as $i_idf_acte => $a_personnes) {
        $a_champs = array();
        $i_nb_temoins = 0;
        $i_nb_personnes = 0;
        foreach ($a_personnes as $i_idf_personne => $a_personne) {
            list($i_idf_type_presence, $c_sexe, $st_patronyme, $i_idf_prenom, $i_idf_origine, $st_date_naissance, $st_age, $i_idf_profession, $st_commentaires, $i_idf_pere, $i_idf_mere, $i_est_decede) = $a_personne;
            switch ($i_idf_type_presence) {
                case IDF_PRESENCE_INTV:
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $c_sexe;
                    $a_champs[] = empty($i_idf_origine) ? '' : $a_commune_personne[$i_idf_origine];
                    $a_champs[] = $st_date_naissance;
                    $a_champs[] = $st_age;
                    $a_champs[] = $st_commentaires;
                    $a_champs[] = empty($i_idf_profession) ? '' : $a_profession[$i_idf_profession];
                    switch ($c_sexe) {
                        case 'M':
                            if (array_key_exists($i_idf_personne, $a_conjoint_h)) {
                                $a_champs[] = $a_personnes[$a_conjoint_h[$i_idf_personne]][2];
                                $a_champs[] = empty($a_personnes[$a_conjoint_h[$i_idf_personne]][3]) ? '' : $a_prenom[$a_personnes[$a_conjoint_h[$i_idf_personne]][3]];
                                $a_champs[] = $a_personnes[$a_conjoint_h[$i_idf_personne]][8];
                            } else
                                array_push($a_champs, "", "", "");
                            break;
                        case 'F':
                            if (array_key_exists($i_idf_personne, $a_conjoint_f)) {
                                $a_champs[] = $a_personnes[$a_conjoint_f[$i_idf_personne]][2];
                                $a_champs[] = empty($a_personnes[$a_conjoint_f[$i_idf_personne]][3]) ? '' : $a_prenom[$a_personnes[$a_conjoint_f[$i_idf_personne]][3]];
                                $a_champs[] = $a_personnes[$a_conjoint_f[$i_idf_personne]][8];
                            } else
                                array_push($a_champs, "", "", "");
                            break;
                        default:
                            if (array_key_exists($i_idf_personne, $a_conjoint_h)) {
                                $a_champs[] = $a_personnes[$a_conjoint_h[$i_idf_personne]][2];
                                $a_champs[] = empty($a_personnes[$a_conjoint_h[$i_idf_personne]][3]) ? '' : $a_prenom[$a_personnes[$a_conjoint_h[$i_idf_personne]][3]];
                                $a_champs[] = $a_personnes[$a_conjoint_h[$i_idf_personne]][8];
                            } else
                                array_push($a_champs, "", "", "");
                    }
                    if (!empty($i_idf_pere)) {
                        $a_champs[] = $a_personnes[$i_idf_pere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_pere][3]];
                        $a_champs[] = $a_personnes[$i_idf_pere][8];
                        $a_champs[] = empty($a_personnes[$i_idf_pere][7]) ? ''  : $a_profession[$a_personnes[$i_idf_pere][7]];
                    } else
                        array_push($a_champs, "", "", "", "");
                    if (!empty($i_idf_mere)) {
                        $a_champs[] = $a_personnes[$i_idf_mere][2];
                        $a_champs[] = empty($a_personnes[$i_idf_mere][3]) ? '' : $a_prenom[$a_personnes[$i_idf_mere][3]];
                        $a_champs[] = $a_personnes[$i_idf_mere][8];
                        $a_champs[] = empty($a_personnes[$i_idf_mere][7]) ? '' : $a_profession[$a_personnes[$i_idf_mere][7]];
                    } else
                        array_push($a_champs, "", "", "", "");
                    break;
                case IDF_PRESENCE_TEMOIN:
                    if ($i_nb_personnes == 1) {
                        // Si le premier témoin en seconde position, le second intervenant n'a pas été saisi
                        // ses champs doivent donc être complétés
                        array_push($a_champs, "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "");
                    }
                    $a_champs[] = $st_patronyme;
                    $a_champs[] = empty($i_idf_prenom) ? '' : $a_prenom[$i_idf_prenom];
                    $a_champs[] = $st_commentaires;
                    $i_nb_temoins++;
                    break;
            }
            $i_nb_personnes++;
        }
        list($idf_commune_acte, $idf_type_acte, $st_date, $st_date_rep, $st_cote, $st_libre, $st_commentaires, $st_permalien) = $pa_liste_actes[$i_idf_acte];
        list($st_type_acte, $st_sigle_acte) = $a_type_acte[$idf_type_acte];
        array_unshift($a_champs, $st_sigle_acte, $st_type_acte);
        array_unshift($a_champs, 'V', $st_date, $st_date_rep, $st_cote, $st_libre);
        array_unshift($a_champs, $i_dpt_commune, $st_departement); // code département, nom département
        array_unshift($a_champs, "NIMEGUEV3", $st_code_insee, $st_nom_commune);
        // Crée les témoins manquants
        for ($i = $i_nb_temoins; $i < 4; $i++) {
            array_push($a_champs, "", "", "");
        }
        $st_commentaires = preg_replace('/\r\n/', '§', $st_commentaires);
        $a_champs[] = trim($st_commentaires);
        $no_enregistrement = $no_enregistrement + 1;
        $a_champs[] = $no_enregistrement; // Numéro d'enregistrement
        $a_champs[] = $st_permalien;
        fwrite($pf, (implode(';', $a_champs)));
        fwrite($pf, "\r\n");
    }
}

/**
 * Exporte les index pour alimenter le moteur des AD
 * Le résultat est stocké dans le fichier $pst_fichier
 * Le format est de la forme:
 * NOM;PRENOM;COMMUNE;ANNEE,TYPE_ACTES.
 * Seuls les intervenants sont exportés (pas les parents, parents, marraines)
 * Les sources utilisées sont la base et les TD AGC     
 * @param object $pconnexionBD lien connexion BD
 * @param object $pf pointeur sur le fichier de sortie  
 */

function export_index_AD($pconnexionBD, $pst_fichier)
{
    $a_ligne = array();
    $st_requete = "select p.patronyme,prn.libelle,ca.nom,a.annee,ta.nom from personne p join prenom prn on (p.idf_prenom=prn.idf) join acte a on (p.idf_acte=a.idf) join commune_acte ca on (a.idf_commune=ca.idf) join type_acte ta on (a.idf_type_acte=ta.idf) where p.idf_type_presence=" . IDF_PRESENCE_INTV . " and a.idf_source=1 and a.idf_type_acte=" . IDF_MARIAGE . " or a.idf_type_acte=" . IDF_NAISSANCE . " or a.idf_type_acte=" . IDF_DECES;
    $pconnexionBD->execute_requete($st_requete);
    $pf = fopen($pst_fichier, "w") or die("<div class=\"alert alert-danger\">Impossible d'&eacute;crire $pst_fichier</div>");
    while (list($st_patro, $st_prenom, $st_commune, $st_annee, $st_type_acte) = $pconnexionBD->ligne_suivante_resultat()) {
        $st_ligne = join(';', array($st_patro, $st_prenom, $st_commune, $st_annee, $st_type_acte));
        fwrite($pf, "$st_ligne\r\n");
    }
    fclose($pf);
}

/**
 * Affiche le menu de la page
 * @param integer $pi_idf_source identifiant de la source
 * @param integer $pi_idf_commune_acte identifiant du releveur
 * @param integer $pc_idf_type_acte identifiant du type d'acte sélectionné
 * @param integer $pi_idf_version_nimegue identifiant de la version de nimègue sélectionnée
 * @global integer $gi_max_taille_upload taille maximale du téléchargement
 * @global array $ga_sources liste des sources
 * @global array $ga_communes_acte liste des communes
 * @global array $ga_adherents liste des adhérents
 * @global array $ga_types_nimegue liste des types d'acte Nimègue
 * @global array $ga_versions_nimegue liste des versions de Nimègue
 */
function affiche_menu($pi_idf_source, $pi_idf_commune_acte, $pi_idf_releveur, $pc_idf_type_acte, $pi_idf_version_nimegue)
{
    global $gi_max_taille_upload, $ga_sources, $ga_communes_acte, $gi_annee_recens, $ga_adherents, $ga_types_nimegue, $ga_versions_nimegue;
    print('<div class="panel-group">');
    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Chargement/Export des donn&eacute;es d\'une commune/paroisse</div>');
    print('<div class="panel-body">');
    print('<div class="panel panel-info">');
    print('<div class="panel-heading">Chargement/Export BMS/EC/Divers</div>');
    print('<div class="panel-body">');
    print("<form id=chargement enctype=\"multipart/form-data\"  method=\"post\">");
    print("<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$gi_max_taille_upload\" >");
    print('<input type="hidden" name="mode" id="mode" value="CHARGEMENT">');
    print('<div class="form-group row">');
    print('<label for="idf_source" class="col-form-label col-md-2 col-md-offset-3">Source:</label>');
    print('<div class="col-md-4">');
    print('<select name=idf_source id=idf_source class="js-select-avec-recherche form-control">');
    print(chaine_select_options($pi_idf_source, $ga_sources));
    print('</select>');
    print('</div></div>');

    print('<div class="form-group row">');
    print('<label for="idf_commune_acte" class="col-form-label col-md-2 col-md-offset-3">Commune:</label>');
    print('<div class="col-md-4">');
    print('<select name=idf_commune_acte id=idf_commune_acte class="js-select-avec-recherche form-control" >');
    print(chaine_select_options($pi_idf_commune_acte, $ga_communes_acte));
    print('</select>');
    print('</div></div>');

    $a_releveurs_select = array();
    foreach ($ga_adherents as $i_idf => $a_champs) {
        list($st_prenom, $st_nom) = $a_champs;
        $a_releveurs_select[$i_idf] = "$st_nom $st_prenom";
    }
    $a_releveurs_select[0] = "Aucun releveur";
    print('<div class="form-group row">');
    print('<label for="idf_releveur" class="col-form-label col-md-2 col-md-offset-3">Releveur:</label>');
    print('<div class="col-md-4">');
    print('<select name=idf_releveur id=idf_releveur class="js-select-avec-recherche form-control">');
    print(chaine_select_options($pi_idf_releveur, $a_releveurs_select));
    print('</select>');
    print('</div></div>');

    print('<div class="form-group row">');
    print('<label for="idf_type_acte" class="col-form-label col-md-2 col-md-offset-3">Type d\'acte Nimegue:</label>');
    print('<div class="col-md-4">');
    print('<select name=idf_type_acte id="idf_type_acte" class="js-select-avec-recherche form-control">');
    print(chaine_select_options($pc_idf_type_acte, $ga_types_nimegue, false));
    print('</select>');
    print('</div></div>');

    print('<div class="form-group row">');
    print('<label for="idf_version_nimegue" class="col-form-label col-md-2 col-md-offset-3">Version Nimegue:</label>');
    print('<div class="col-md-4">');
    print('<select name=idf_version_nimegue id=idf_version_nimegue class="form-control">');
    print(chaine_select_options($pi_idf_version_nimegue, $ga_versions_nimegue));
    print('</select>');
    print('</div></div>');

    print('<div class="form-group row"><div class="custom-file">');
    print('<label for="FichNim" class="col-form-label col-md-2 col-md-offset-3">Fichier:</label>');
    print('<div class="col-md-4">');
    print('<input name="FichNim" id="FichNim" type="file" class="custom-file-input">');
    print('</div>');
    print('</div>');
    print('</div>');

    print('<div class="form-row">');
    print('<div class="col-md-offset-4 col-md-4">');
    print('<div class="btn-group-vertical">');
    print('<button type=submit class="btn btn-primary" ><span class="glyphicon glyphicon-upload"> Charger le fichier</button>');
    print('<button type=button class="btn btn-primary" id=export_bmsv><span class="glyphicon glyphicon-download"> Exporter la commune au format s&eacute;lectionn&eacute;</button>');
    print('</div>');
    print('</div>');
    print('</div>');

    print("</form></div></div>");

    print('<div class="panel panel-info">');
    print('<div class="panel-heading">Export AD</div>');
    print('<div class="panel-body">');
    print("<input type=\"hidden\" id=\"export_idf_source\" name=\"idf_source\" value=\"\">");
    print("<input type=\"hidden\" id=\"export_idf_commune\" name=\"idf_commune_acte\" value=\"\">");
    print("<input type=\"hidden\" id=\"export_idf_type_acte\" name=\"idf_type_acte\" value=\"\">");

    print("<form id=export_ad   method=\"post\" >");
    print('<input type="hidden" name="mode" id=\"mode_export\" value="EXPORT_INDEX_AD">');
    print('<button type=submit class="btn btn-primary col-md-offset-4 col-md-4"><span class="glyphicon glyphicon-download"> Exporter les index pour les AD</button>');
    print("</form>");
    print("</div></div>");

    print('<div class="panel panel-info">');
    print('<div class="panel-heading">Chargement recensement</div>');
    print('<div class="panel-body">');

    print("<form id=chargement_recens enctype=\"multipart/form-data\"  method=\"post\">");
    print("<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$gi_max_taille_upload\" >");
    print('<input type="hidden" name="mode" value="CHARGEMENT_RECENS">');
    print('<div class="form-group row">');
    print('<label for="idf_source_recens" class="col-form-label col-md-2 col-md-offset-3">Source:</label>');
    print('<div class="col-md-4">');
    print('<select name=idf_source id=idf_source_recens class="js-select-avec-recherche form-control">');
    print(chaine_select_options($pi_idf_source, $ga_sources));
    print('</select>');
    print('</div></div>');

    print('<div class="form-group row">');
    print('<label for="idf_commune_recens" class="col-form-label col-md-2 col-md-offset-3">Commune:</label>');
    print('<div class="col-md-4">');
    print('<select name=idf_commune_acte id=idf_commune_recens class="js-select-avec-recherche form-control" >');
    print(chaine_select_options($pi_idf_commune_acte, $ga_communes_acte));
    print('</select>');
    print('</div></div>');

    print('<div class="form-group row">');
    print('<label for="annee_recens" class="col-form-label col-md-2 col-md-offset-3">Ann&eacute;e:</label>');
    print('<div class="col-md-4">');
    print("<input type=\"text\" name=\"annee_recens\" id=\"annee_recens\" size=\"4\" maxlength=\"4\" value=\"$gi_annee_recens\" class=\"form-control\">");
    print('</div></div>');

    print('<div class="form-group row"><div class="custom-file">');
    print('<label for="FichRecens" class="col-form-label col-md-2 col-md-offset-3">Fichier:</label>');
    print('<div class="col-md-4">');
    print('<input name="FichRecens" id="FichRecens" type="file" class="custom-file-input">');
    print('</div></div></div>');

    print('<button type=submit name=Rechercher class="btn btn-primary col-md-offset-4 col-md-4"><span class="glyphicon glyphicon-upload"> Charger le recensement</button>');

    print("</form></div></div></div>");
}

/*------------------------------------------------------------------------------
                            Corps du programme
 -----------------------------------------------------------------------------*/
$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

$gst_mode = empty($_POST['mode']) ? 'FORMULAIRE' : $_POST['mode'];
$i_session_idf_source = isset($_SESSION['idf_source']) ? $_SESSION['idf_source'] : 1;
$gi_idf_source = empty($_POST['idf_source']) ? $i_session_idf_source : (int) $_POST['idf_source'];
$i_session_idf_commune_acte = isset($_SESSION['idf_commune_acte']) ? $_SESSION['idf_commune_acte'] : 0;
$gi_idf_commune_acte = empty($_POST['idf_commune_acte']) ? $i_session_idf_commune_acte : (int) $_POST['idf_commune_acte'];
$i_session_idf_releveur = isset($_SESSION['idf_releveur']) ? $_SESSION['idf_releveur'] : '0';
$gi_idf_releveur = empty($_POST['idf_releveur']) ? $i_session_idf_releveur : (int) $_POST['idf_releveur'];
$gc_idf_type_acte = empty($_POST['idf_type_acte']) ? '0' : $_POST['idf_type_acte'];
$gi_idf_version_nimegue = empty($_POST['idf_version_nimegue']) ? 3 : (int) $_POST['idf_version_nimegue'];
$i_session_annee_recens = isset($_SESSION['annee_recens']) ? $_SESSION['annee_recens'] : '';
$gi_annee_recens = empty($_POST['annee_recens']) ? $i_session_annee_recens : (int) $_POST['annee_recens'];
$ga_sources = $connexionBD->liste_valeur_par_clef("select idf,nom from source order by nom");
$ga_communes_acte = $connexionBD->liste_valeur_par_clef("select idf,nom from commune_acte order by nom");
$ga_adherents = $connexionBD->sql_select_multiple_par_idf("select idf,prenom,nom from adherent order by nom,prenom");
// Attention, la valeur des types doit correspondre aux constantes qui servent au chargement

$ga_versions_nimegue = array('2' => 'Version 2', '3' => 'Version 3');

switch ($gst_mode) {
    case 'EXPORTV2':
        switch ($gc_idf_type_acte) {
            case IDF_NAISSANCE:
            case IDF_MARIAGE:
            case IDF_DECES:
                $a_liste_actes = $connexionBD->sql_select_multiple_par_idf("select idf,idf_commune,idf_type_acte,date, date_rep, cote,libre, commentaires,url from acte where idf_commune=$gi_idf_commune_acte and idf_source=$gi_idf_source and idf_type_acte=$gc_idf_type_acte");
                $a_liste_personnes = $connexionBD->liste_valeur_par_doubles_clefs("select p.idf_acte,p.idf,p.idf_type_presence,p.sexe, p.patronyme,p.idf_prenom,p.idf_origine,p.date_naissance,p.age,p.idf_profession, p.commentaires,p.idf_pere,p.idf_mere,p.est_decede from personne p join acte a on (p.idf_acte=a.idf)where a.idf_commune=$gi_idf_commune_acte and a.idf_source=$gi_idf_source and a.idf_type_acte=$gc_idf_type_acte order by p.idf_acte,p.idf");
                break;
            case IDF_DIVERS:
                $a_liste_actes = $connexionBD->sql_select_multiple_par_idf("select idf,idf_commune,idf_type_acte,date, date_rep, cote,libre, commentaires,url from acte where idf_commune=$gi_idf_commune_acte and idf_source=$gi_idf_source and idf_type_acte not in (" . IDF_NAISSANCE . "," . IDF_MARIAGE . "," . IDF_DECES . ")");
                $a_liste_personnes = $connexionBD->liste_valeur_par_doubles_clefs("select p.idf_acte,p.idf,p.idf_type_presence,p.sexe, p.patronyme,p.idf_prenom,p.idf_origine,p.date_naissance,p.age,p.idf_profession, p.commentaires,p.idf_pere,p.idf_mere,p.est_decede from personne p join acte a on (p.idf_acte=a.idf) where a.idf_commune=$gi_idf_commune_acte and a.idf_source=$gi_idf_source and a.idf_type_acte not in (" . IDF_NAISSANCE . "," . IDF_MARIAGE . "," . IDF_DECES . ") order by p.idf_acte,p.idf");
        }
        header("Content-type: text/csv");
        header("Expires: 0");
        header("Pragma: public");
        header("Content-disposition: attachment; filename=\"ExportNimV2.csv\"");
        $pf = @fopen('php://output', 'w');
        switch ($gc_idf_type_acte) {
            case IDF_NAISSANCE:
                export_nai_nimv2($connexionBD, $gi_idf_source, $gi_idf_commune_acte, $gc_idf_type_acte, $a_liste_personnes, $a_liste_actes, $pf);
                break;
            case IDF_DECES:
                export_dec_nimv2($connexionBD, $gi_idf_source, $gi_idf_commune_acte, $gc_idf_type_acte, $a_liste_personnes, $a_liste_actes, $pf);
                break;
            case IDF_MARIAGE:
                export_mar_nimv2($connexionBD, $gi_idf_source, $gi_idf_commune_acte, $gc_idf_type_acte, $a_liste_personnes, $a_liste_actes, $pf);
                break;
            case IDF_DIVERS:
                export_div_nimv2($connexionBD, $gi_idf_source, $gi_idf_commune_acte, $a_liste_personnes, $a_liste_actes, $pf);
                break;
        }
        fclose($pf);
        exit();
        break;
    case 'EXPORTV3':
        switch ($gc_idf_type_acte) {
            case IDF_NAISSANCE:
            case IDF_MARIAGE:
            case IDF_DECES:
                $a_liste_actes = $connexionBD->sql_select_multiple_par_idf("select idf,idf_commune,idf_type_acte,date, date_rep, cote,libre, commentaires,url from acte where idf_commune=$gi_idf_commune_acte and idf_source=$gi_idf_source and idf_type_acte=$gc_idf_type_acte");
                $a_liste_personnes = $connexionBD->liste_valeur_par_doubles_clefs("select p.idf_acte,p.idf,p.idf_type_presence,p.sexe, p.patronyme,p.idf_prenom,p.idf_origine,p.date_naissance,p.age,p.idf_profession, p.commentaires,p.idf_pere,p.idf_mere,p.est_decede from personne p join acte a on (p.idf_acte=a.idf)where a.idf_commune=$gi_idf_commune_acte and a.idf_source=$gi_idf_source and a.idf_type_acte=$gc_idf_type_acte order by p.idf_acte,p.idf");
                break;
            case IDF_DIVERS:
                $a_liste_actes = $connexionBD->sql_select_multiple_par_idf("select idf,idf_commune,idf_type_acte,date, date_rep, cote,libre, commentaires,url from acte where idf_commune=$gi_idf_commune_acte and idf_source=$gi_idf_source and idf_type_acte not in (" . IDF_NAISSANCE . "," . IDF_MARIAGE . "," . IDF_DECES . ")");
                $a_liste_personnes = $connexionBD->liste_valeur_par_doubles_clefs("select p.idf_acte,p.idf,p.idf_type_presence,p.sexe, p.patronyme,p.idf_prenom,p.idf_origine,p.date_naissance,p.age,p.idf_profession, p.commentaires,p.idf_pere,p.idf_mere,p.est_decede from personne p join acte a on (p.idf_acte=a.idf) where a.idf_commune=$gi_idf_commune_acte and a.idf_source=$gi_idf_source and a.idf_type_acte not in (" . IDF_NAISSANCE . "," . IDF_MARIAGE . "," . IDF_DECES . ") order by p.idf_acte,p.idf");
        }
        header("Content-type: text/csv");
        header("Expires: 0");
        header("Pragma: public");
        header("Content-disposition: attachment; filename=\"ExportNimV3.csv\"");
        $pf = @fopen('php://output', 'w');
        switch ($gc_idf_type_acte) {
            case IDF_NAISSANCE:
                export_nai_nimv3($connexionBD, $gi_idf_source, $gi_idf_commune_acte, $gc_idf_type_acte, $a_liste_personnes, $a_liste_actes, $pf);
                break;
            case IDF_DECES:
                export_dec_nimv3($connexionBD, $gi_idf_source, $gi_idf_commune_acte, $gc_idf_type_acte, $a_liste_personnes, $a_liste_actes, $pf);
                break;
            case IDF_MARIAGE:
                export_mar_nimv3($connexionBD, $gi_idf_source, $gi_idf_commune_acte, $gc_idf_type_acte, $a_liste_personnes, $a_liste_actes, $pf);
                break;
            case IDF_DIVERS:
                export_div_nimv3($connexionBD, $gi_idf_source, $gi_idf_commune_acte, $a_liste_personnes, $a_liste_actes, $pf);
                break;
        }
        fclose($pf);
        exit();
        break;
}

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'> ");
print("<link href='../assets/css/select2.min.css' type='text/css' rel='stylesheet'> ");
print("<link href='../assets/css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'> ");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/select2.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
    $(document).ready(function() {

        $.fn.select2.defaults.set("theme", "bootstrap");

        $(".js-select-avec-recherche").select2();

        //validation rules
        $("#chargement").validate({
            rules: {
                FichNim: {
                    required: true,
                    extension: "csv|txt"
                }
            },
            messages: {
                FichNim: {
                    required: "Un fichier doit être choisi",
                    extension: "Le fichier doit être du type csv ou txt"
                }
            },
            submitHandler: function(form) {
                var source = $('#idf_source option:selected').text();
                var type_acte = $('#idf_type_acte option:selected').text();
                var commune = $('#idf_commune_acte option:selected').text();
                if ($('#mode').val() == "CHARGEMENT") {
                    if (confirm('Etes-vous sûr de recharger le fichier de la commune ' + commune + ' (' + type_acte + ')' + ' de la source ' + source + ' ?')) {
                        form.submit();
                    }
                } else
                    form.submit();
            },
            errorElement: "em",
            errorPlacement: function(error, element) {
                // Add the `help-block` class to the error element
                error.addClass("help-block");

                // Add `has-feedback` class to the parent div.form-group
                // in order to add icons to inputs
                element.parents(".col-md-4").addClass("has-feedback");

                if (element.prop("type") === "checkbox") {
                    error.insertAfter(element.parent("label"));
                } else {
                    error.insertAfter(element);
                }

                // Add the span element, if doesn't exists, and apply the icon classes to it.
                if (!element.next("span")[0]) {
                    $("<span class='glyphicon glyphicon-remove form-control-feedback'></span>").insertAfter(element);
                }
            },
            success: function(label, element) {
                // Add the span element, if doesn't exists, and apply the icon classes to it.
                if (!$(element).next("span")[0]) {
                    $("<span class='glyphicon glyphicon-ok form-control-feedback'></span>").insertAfter($(element));
                }
            },
            highlight: function(element, errorClass, validClass) {
                $(element).parents(".col-md-4").addClass("has-error").removeClass("has-success");
                $(element).next("span").addClass("glyphicon-remove").removeClass("glyphicon-ok");
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents(".col-md-4").addClass("has-success").removeClass("has-error");
                $(element).next("span").addClass("glyphicon-ok").removeClass("glyphicon-remove");
            }

        });

        $("#chargement_recens").validate({
            rules: {
                annee_recens: {
                    required: true,
                    integer: true,
                    minlength: 4
                },
                FichRecens: {
                    required: true,
                    extension: "csv|txt"
                }
            },
            messages: {
                FichRecens: {
                    required: "Un fichier doit être choisi",
                    extension: "Le fichier doit être du type csv ou txt"
                },
                annee_recens: {
                    required: "L'année doit être spécifiée",
                    integer: "L'année doit être un entier",
                    minlength: "L'année doit comporter 4 chiffes"
                },
            },
            errorElement: "em",
            errorPlacement: function(error, element) {
                // Add the `help-block` class to the error element
                error.addClass("help-block");

                // Add `has-feedback` class to the parent div.form-group
                // in order to add icons to inputs
                element.parents(".col-md-4").addClass("has-feedback");

                if (element.prop("type") === "checkbox") {
                    error.insertAfter(element.parent("label"));
                } else {
                    error.insertAfter(element);
                }

                // Add the span element, if doesn't exists, and apply the icon classes to it.
                if (!element.next("span")[0]) {
                    $("<span class='glyphicon glyphicon-remove form-control-feedback'></span>").insertAfter(element);
                }
            },
            success: function(label, element) {
                // Add the span element, if doesn't exists, and apply the icon classes to it.
                if (!$(element).next("span")[0]) {
                    $("<span class='glyphicon glyphicon-ok form-control-feedback'></span>").insertAfter($(element));
                }
            },
            highlight: function(element, errorClass, validClass) {
                $(element).parents(".col-md-4").addClass("has-error").removeClass("has-success");
                $(element).next("span").addClass("glyphicon-remove").removeClass("glyphicon-ok");
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).parents(".col-md-4").addClass("has-success").removeClass("has-error");
                $(element).next("span").addClass("glyphicon-ok").removeClass("glyphicon-remove");
            },
            submitHandler: function(form) {
                var annee_recens = $('#annee_recens').val();
                var commune = $('#idf_commune_recens option:selected').text();
                if (confirm('Etes-vous sûr de charger les recensements de la commune ' + commune + ' (' + annee_recens + ') ?')) {
                    form.submit();
                }
            }
        });

        //validation rules
        $("#export").validate({
            submitHandler: function(form) {
                $("#export_idf_source").val($("#idf_source").val());
                $("#export_idf_commune").val($("#idf_commune_acte").val());
                $("#export_idf_type_acte").val($("#idf_type_acte").val());
                if ($("#idf_version_nimegue").val() == 2)
                    $("#mode_export").val('EXPORTV2');
                else if ($("#idf_version_nimegue").val() == 3)
                    $("#mode_export").val('EXPORTV3');
                form.submit();
            }
        });

        $("#export_bmsv").click(function() {
            if ($('#idf_version_nimegue').val() == 2)
                $('#mode').val("EXPORTV2");
            if ($('#idf_version_nimegue').val() == 3)
                $('#mode').val("EXPORTV3");
            $('#FichNim').rules('add', {
                required: false // overwrite an existing rule
            });
            $("#chargement").submit();
        });

    });
</script>
<?php
print("<title>Chargement/Export des donnees</title>");
print('</head>');
print('<body>');
print('<div class="container">');

require_once __DIR__ . '/../commun/menu.php';

switch ($gst_mode) {
    case 'FORMULAIRE':
        affiche_menu($gi_idf_source, $gi_idf_commune_acte, $gi_idf_releveur, $gc_idf_type_acte, $gi_idf_version_nimegue);
        break;

    case 'CHARGEMENT':
        ini_set("memory_limit", "256M");
        $_SESSION['idf_source'] = $gi_idf_source;
        $_SESSION['idf_commune_acte'] = $gi_idf_commune_acte;
        $_SESSION['idf_releveur'] = $gi_idf_releveur;
        switch ($gc_idf_type_acte) {
            case IDF_NAISSANCE:
                $st_type_nimegue = 'N';
                break;
            case IDF_MARIAGE:
                $st_type_nimegue = 'M';
                break;
            case IDF_DECES:
                $st_type_nimegue = 'D';
                break;
            case IDF_DIVERS:
                $st_type_nimegue = 'V';
                break;
            default:
                $st_type_nimegue = 'I';
        }
        list($st_nom_commune, $st_code_insee, $i_numero_paroisse) = $connexionBD->sql_select_liste("select nom,code_insee,numero_paroisse from commune_acte where idf=$gi_idf_commune_acte");
        // Suppression des quotes éventuelles
        $st_nom_commune = str_replace("'", '', $st_nom_commune);
        if (preg_match("/^([\w\-]+)\s*/", $st_nom_commune, $a_correspondances)) {
            // Récupère le premier champ de la commune qui doit être un alphanumerique
            $st_nom = $a_correspondances[1];
            $st_nom_fich_dest = sprintf("%s_%s%.2d-%s.txt", $st_nom, $st_code_insee, $i_numero_paroisse, $st_type_nimegue);
        } else
            $st_nom_fich_dest = sprintf("INCONNU_%s%.2d-$st_type_nimegue.txt", $st_code_insee, $i_numero_paroisse);
        $st_fich_dest = "$gst_repertoire_telechargement/$st_nom_fich_dest";
        if (!move_uploaded_file($_FILES['FichNim']['tmp_name'], $st_fich_dest)) {
            print("Erreur de telechargement : impossible de copier en $st_fich_dest:<br>");
            switch ($_FILES['FichNim']['error']) {
                case 2:
                    print("Fichier trop gros par rapport a MAX_FILE_SIZE");
                    break;
                default:
                    print("Erreur inconnue");
                    print_r($_FILES);
            }

            exit;
        }

        $i_epoch_deb = time();
        switch ($gi_idf_version_nimegue) {
            case '2':
                $chargementNimV2 = new ChargementNimV2($connexionBD);
                switch ($gc_idf_type_acte) {
                    case IDF_NAISSANCE:
                        $b_ret = $chargementNimV2->charge_naissances($st_fich_dest, $gi_idf_commune_acte, $gi_idf_source, $gi_idf_releveur, liste_naissances_existant($connexionBD, $gi_idf_source, $gi_idf_commune_acte));
                        $i_nb_actes_charges = $chargementNimV2->nb_actes_charges();
                        $connexionBD->execute_requete("insert into chargement(date_chgt,idf_commune,type_acte_nim,nb_actes) values(now(),$gi_idf_commune_acte," . IDF_NAISSANCE . ",$i_nb_actes_charges)");
                        break;
                    case IDF_MARIAGE:

                        $b_ret = $chargementNimV2->charge_mariages($st_fich_dest, $gi_idf_commune_acte, $gi_idf_source, $gi_idf_releveur, liste_mariages_existant($connexionBD, $gi_idf_source, $gi_idf_commune_acte));
                        $i_nb_actes_charges = $chargementNimV2->nb_actes_charges();
                        $connexionBD->execute_requete("insert into chargement(date_chgt,idf_commune,type_acte_nim,nb_actes) values(now(),$gi_idf_commune_acte," . IDF_MARIAGE . ",$i_nb_actes_charges)");
                        break;
                    case IDF_DECES:
                        $b_ret = $chargementNimV2->charge_deces($st_fich_dest, $gi_idf_commune_acte, $gi_idf_source, $gi_idf_releveur, liste_deces_existant($connexionBD, $gi_idf_source, $gi_idf_commune_acte));
                        $i_nb_actes_charges = $chargementNimV2->nb_actes_charges();
                        $connexionBD->execute_requete("insert into chargement(date_chgt,idf_commune,type_acte_nim,nb_actes) values(now(),$gi_idf_commune_acte," . IDF_DECES . ",$i_nb_actes_charges)");
                        break;
                    case IDF_DIVERS:
                        $b_ret = $chargementNimV2->charge_divers($st_fich_dest, $gi_idf_commune_acte, $gi_idf_source, $gi_idf_releveur, liste_divers_existant($connexionBD, $gi_idf_source, $gi_idf_commune_acte));

                        $i_nb_actes_charges = $chargementNimV2->nb_actes_charges();
                        $connexionBD->execute_requete("insert into chargement(date_chgt,idf_commune,type_acte_nim,nb_actes) values(now(),$gi_idf_commune_acte," . IDF_DIVERS . ",$i_nb_actes_charges)");
                        break;
                }
                $a_liste_deja_existants = $chargementNimV2->liste_deja_existants();

                break;
            case '3':
                $chargementNimV3 = new ChargementNimV3($connexionBD);
                switch ($gc_idf_type_acte) {
                    case IDF_NAISSANCE:
                        $b_ret = $chargementNimV3->charge_naissances($st_fich_dest, $gi_idf_commune_acte, $gi_idf_source, $gi_idf_releveur, liste_naissances_existant($connexionBD, $gi_idf_source, $gi_idf_commune_acte));
                        $i_nb_actes_charges = $chargementNimV3->nb_actes_charges();
                        $connexionBD->execute_requete("insert into chargement(date_chgt,idf_commune,type_acte_nim,nb_actes) values(now(),$gi_idf_commune_acte," . IDF_NAISSANCE . ",$i_nb_actes_charges)");
                        break;
                    case IDF_MARIAGE:
                        $b_ret = $chargementNimV3->charge_mariages($st_fich_dest, $gi_idf_commune_acte, $gi_idf_source, $gi_idf_releveur, liste_mariages_existant($connexionBD, $gi_idf_source, $gi_idf_commune_acte));
                        $i_nb_actes_charges = $chargementNimV3->nb_actes_charges();
                        $connexionBD->execute_requete("insert into chargement(date_chgt,idf_commune,type_acte_nim,nb_actes) values(now(),$gi_idf_commune_acte," . IDF_MARIAGE . ",$i_nb_actes_charges)");
                        break;
                    case IDF_DECES:
                        $b_ret = $chargementNimV3->charge_deces($st_fich_dest, $gi_idf_commune_acte, $gi_idf_source, $gi_idf_releveur, liste_deces_existant($connexionBD, $gi_idf_source, $gi_idf_commune_acte));
                        $i_nb_actes_charges = $chargementNimV3->nb_actes_charges();
                        $connexionBD->execute_requete("insert into chargement(date_chgt,idf_commune,type_acte_nim,nb_actes) values(now(),$gi_idf_commune_acte," . IDF_DECES . ",$i_nb_actes_charges)");
                        break;
                    case IDF_DIVERS:
                        $b_ret = $chargementNimV3->charge_divers($st_fich_dest, $gi_idf_commune_acte, $gi_idf_source, $gi_idf_releveur, liste_divers_existant($connexionBD, $gi_idf_source, $gi_idf_commune_acte));
                        $i_nb_actes_charges = $chargementNimV3->nb_actes_charges();
                        $connexionBD->execute_requete("insert into chargement(date_chgt,idf_commune,type_acte_nim,nb_actes) values(now(),$gi_idf_commune_acte," . IDF_DIVERS . ",$i_nb_actes_charges)");
                        break;
                }
                $a_liste_deja_existants = $chargementNimV3->liste_deja_existants();

                break;
            default:
                print("Inconnu");
                $a_liste_deja_existants = array();
                $i_nb_actes_charges = 0;
        }
        unlink($st_fich_dest);
        print('<div class="text-center"> Temps de traitement : ' . (time() - $i_epoch_deb) . ' s</div>');
        if ($b_ret) {

            print('<div for="actes_existants" class="alert alert-warning text-center">Actes d&eacute;j&agrave; existants:</div>');
            print('<div class="row text-center">');
            print('<textarea rows=20 cols=80 id="actes_existants">');
            foreach ($a_liste_deja_existants as $st_acte)
                print("$st_acte\n");
            print("</textarea></div>");
            print("<div class=\"alert alert-success text-center\" role=\"alert\">$i_nb_actes_charges actes charg&eacute;s</div>");;
        }
        $st_requete = "select distinct count(*) from `union` u  where u.idf_commune = $gi_idf_commune_acte and idf_type_acte=1 and (u.idf_epoux not in (select idf from personne p where p.idf_acte=u.idf_acte) or u.idf_epouse not in (select idf from personne p2 where p2.idf_acte=u.idf_acte))";
        $i_nb_unions_sans_pers = $connexionBD->sql_select1($st_requete);
        if ($i_nb_unions_sans_pers > 0) {
            print("<div class=\"alert alert-danger text-center\">ERREUR: $i_nb_unions_sans_pers unions avec des personnes inexistantes. Recharger le fichier !</div>");
        }
        print("<form  method=\"post\">");
        print('<input type="hidden" name="mode" value="FORMULAIRE" >');
        print('<div class="form-group row"><button type="submit" class="btn btn-primary col-md-4 col-md-offset-4">Menu chargement</button></div>');
        print("</form>");


        print("<form action=\"NotificationCommune.php\" method=\"post\">");
        print('<input type="hidden" name="mode" value="EDITION_NOTIFICATION" >');
        print("<input type=\"hidden\" name=\"idf_source\" value=$gi_idf_source >");
        print("<input type=\"hidden\" name=\"idf_commune\" value=$gi_idf_commune_acte >");
        print("<input type=\"hidden\" name=\"idf_type_acte_nimegue\" value=$gc_idf_type_acte>");
        print('<div class="form-group row"><button type="submit" class="btn btn-primary col-md-4 col-md-offset-4">Notification sur le forum</button></div>');
        print("</form>");
        break;

    case 'EXPORT_INDEX_AD':
        print('<div class="text-center">');
        export_index_AD($connexionBD, "$gst_repertoire_indexes_AD/index.csv");
        $zip = new ZipArchive();
        $st_chemin_zip = "$gst_repertoire_indexes_AD/index.zip";
        if (file_exists($st_chemin_zip)) unlink($st_chemin_zip);
        if ($zip->open($st_chemin_zip, ZIPARCHIVE::CREATE) !== TRUE) {
            exit("Impossible d'ecrire <$st_chemin_zip>\n");
        }
        $zip->addFile("$gst_repertoire_indexes_AD/index.csv", "index_agc.csv");
        $zip->close();
        unlink("$gst_repertoire_indexes_AD/index.csv");
        print("<p class=\"text-center\"><a href=\"$gst_url_indexes_AD/index.zip\" class=\"btn btn-primary\">Export</a></p>");
        print("<form  method=\"post\">");
        print('<input type="hidden" name="mode" value="FORMULAIRE"/><br>');
        print('<div class="form-group col-md-4"><button type="submit" class="btn btn-primary">Menu Chargement/button></div>');
        print("</form>");
        break;

    case 'CHARGEMENT_RECENS':
        ini_set("memory_limit", "256M");
        $_SESSION['idf_source'] = $gi_idf_source;
        $_SESSION['idf_commune_acte'] = $gi_idf_commune_acte;
        $_SESSION['annee_recens'] = $gi_annee_recens;
        list($st_nom_commune, $st_code_insee, $i_numero_paroisse) = $connexionBD->sql_select_liste("select nom,code_insee,numero_paroisse from commune_acte where idf=$gi_idf_commune_acte");
        // Suppression des quotes éventuelles
        $st_nom_commune = str_replace("'", '', $st_nom_commune);
        if (preg_match("/^([\w\-]+)\s*/", $st_nom_commune, $a_correspondances)) {
            // Récupère le premier champ de la commune qui doit être un alphanumerique
            $st_nom = $a_correspondances[1];
            $st_nom_fich_dest = sprintf("%s_%s%.2d-recens.txt", $st_nom, $st_code_insee, $i_numero_paroisse);
        } else
            $st_nom_fich_dest = sprintf("INCONNU_%s%.2d-$st_type_nimegue.txt", $st_code_insee, $i_numero_paroisse);
        $st_fich_dest = "$gst_repertoire_telechargement/$st_nom_fich_dest";
        if (!move_uploaded_file($_FILES['FichRecens']['tmp_name'], $st_fich_dest)) {
            print("Erreur de telechargement : impossible de copier en $st_fich_dest:<br>");
            switch ($_FILES['FichRecens']['error']) {
                case 2:
                    print("Fichier trop gros par rapport a MAX_FILE_SIZE");
                    break;
                default:
                    print("Erreur inconnue");
                    print_r($_FILES);
            }

            exit;
        }
        $i_nb_actes_charges = charge_recensement($st_fich_dest, $gi_idf_commune_acte, $gi_annee_recens, $gi_idf_source, null);
        $connexionBD->execute_requete("insert into chargement(date_chgt,idf_commune,type_acte_nim,nb_actes) values(now(),$gi_idf_commune_acte," . IDF_RECENS . ",$i_nb_actes_charges)");
        unlink($st_fich_dest);
        print("<div class=\"alert alert-success\">$i_nb_actes_charges actes charg&eacute;s</div>");
        print("<form  method=\"post\">");
        print('<input type="hidden" name="mode" value="FORMULAIRE" >');
        print('<div class="form-group col-md-4"><button type="submit" class="btn btn-primary">Menu Chargement</button></div>');
        print("</form>");
        break;

    default:
        print("<div class=\"alert alert-danger\">mode $gst_mode inconnu</div>");
}
print('</div></body></html>');
