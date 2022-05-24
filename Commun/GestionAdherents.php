<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

/*
* Effectue une réadhésion pour l'utilisateur identifié par la transaction $pst_jeton
* @param object $po_cnx_bd connexion à la bd
* @param string jeton de transaction
*/
function traite_readhesion($po_cnx_bd,$pst_jeton)
{
   global $gst_time_zone;
   date_default_timezone_set($gst_time_zone);
   list($i_sec,$i_min,$i_heure,$i_jour,$i_mois,$i_annee,$i_jsem,$i_jan,$b_hiv)= localtime();
   $i_mois++;
   $i_annee+=1900;
   $i_annee_cotisation = ($i_mois>9) ?  $i_annee+1 : $i_annee;
   $st_requete = "update `adherent` adh join `inscription_prov` i_p on (adh.idf=i_p.ins_idf_agc) set adh.statut=i_p.ins_statut, adh.aide=i_p.ins_aide,adh.type_origine=i_p.ins_type_origine,adh.description_origine=i_p.ins_description_origine, adh.prix=i_p.ins_prix, adh.jeton_paiement='$pst_jeton', adh.date_paiement=now(), adh.annee_cotisation=$i_annee_cotisation , infos_agc=CONCAT_WS(\"\n\",infos_agc,'Re-adhesion en ligne $i_jour/$i_mois/$i_annee') where i_p.ins_token='$pst_jeton'";
   $po_cnx_bd->execute_requete($st_requete);
   $st_requete = "delete from `inscription_prov` where ins_token='$pst_jeton'";
   $po_cnx_bd->execute_requete($st_requete);
}

/*
* Effectue la première inscription d'un utilisateur identifiée par la transaction $pst_jeton
* @param object $po_cnx_bd connexion a la bd
* @param string $pst_jeton jeton de transaction
* @param string $pst_mdp mot de passe
* @param integer $pi_idf_agc identifiant AGC si ancien adherent
* @param integer $pi_adh_existant indique si l'adhérent est connu du système ou pas 
* @return integer numero de l'adherent cree
*/
function traite_inscription($po_cnx_bd,$pst_jeton,$pst_mdp,$pi_idf_agc,$pi_adh_existant)
{
   date_default_timezone_set('Europe/Paris'); 
   list($i_sec,$i_min,$i_heure,$i_jour,$i_mois,$i_annee,$i_jsem,$i_jan,$b_hiv)= localtime();
   $i_mois++;
   $i_annee+=1900;
   $i_annee_cotisation = ($i_mois>9) ?  $i_annee+1 : $i_annee;
   if (empty($pi_idf_agc))
   {
     $i_idf_dernier_adherent = $po_cnx_bd->sql_select1("select max(idf) from adherent");
     $i_idf_dernier_adherent++;
     $st_requete = "insert into `adherent`(idf,nom,prenom,ident,mdp,email_forum,email_perso,tel,adr1,adr2,cp, ville,pays,confidentiel,statut,site,date_premiere_adhesion,date_paiement,prix,annee_cotisation,aide,type_origine,description_origine,infos_agc,jeton_paiement) select $i_idf_dernier_adherent,ins_nom,ins_prenom,'$i_idf_dernier_adherent','$pst_mdp',ins_email_perso,ins_email_perso,ins_telephone,ins_adr1,ins_adr2,ins_cp, ins_commune,ins_pays,ins_cache,ins_statut,ins_site_web,now(),now(),ins_prix,$i_annee_cotisation,ins_aide,ins_type_origine,ins_description_origine,'Inscription en ligne $i_jour/$i_mois/$i_annee',ins_token from `inscription_prov` where ins_token='$pst_jeton'";
     $po_cnx_bd->execute_requete($st_requete);
     $i_idf_adh = $i_idf_dernier_adherent;
   }
   else
   {
      if ($pi_adh_existant==0)
        // l'adhérent est trop ancien => on doit le créer
        $st_requete = "insert into `adherent`(idf,nom,prenom,ident,mdp,email_forum,email_perso,tel,adr1,adr2,cp, ville,pays,confidentiel,statut,site,date_premiere_adhesion,date_paiement,prix,annee_cotisation,aide,type_origine,description_origine,infos_agc,jeton_paiement) select $pi_idf_agc,ins_nom,ins_prenom,'$pi_idf_agc','$pst_mdp',ins_email_perso,ins_email_perso,ins_telephone,ins_adr1,ins_adr2,ins_cp, ins_commune,ins_pays,ins_cache,ins_statut,ins_site_web,now(),now(),ins_prix,$i_annee_cotisation,ins_aide,ins_type_origine,ins_description_origine,'Inscription en ligne $i_jour/$i_mois/$i_annee',ins_token from `inscription_prov` where ins_token='$pst_jeton'";
      else
        $st_requete = "update `adherent` adh join `inscription_prov` i_p on (adh.idf=i_p.ins_idf_agc) set adh.statut=i_p.ins_statut, adh.aide=i_p.ins_aide,adh.type_origine=i_p.ins_type_origine,adh.description_origine=i_p.ins_description_origine, adh.prix=i_p.ins_prix, adh.jeton_paiement='$pst_jeton', adh.date_paiement=now(), adh.annee_cotisation=$i_annee_cotisation,adh.nom=i_p.ins_nom,adh.prenom=i_p.ins_prenom,adh.ident=i_p.ins_idf_agc, adh.mdp='$pst_mdp',adh.email_perso=i_p.ins_email_perso,adh.tel=i_p.ins_telephone,adh.adr1=i_p.ins_adr1,adh.adr2=i_p.ins_adr2,adh.cp=i_p.ins_cp,adh.ville=i_p.ins_commune,adh.pays=i_p.ins_pays,adh.confidentiel=i_p.ins_cache,adh.site=i_p.ins_site_web,infos_agc=CONCAT_WS(\"\n\",infos_agc,'Re-adhesion en ligne $i_jour/$i_mois/$i_annee') where i_p.ins_token='$pst_jeton'";
      $po_cnx_bd->execute_requete($st_requete);
      $i_idf_adh= $pi_idf_agc;
   }
   $st_requete = "delete from `inscription_prov` where ins_token='$pst_jeton'";
   $po_cnx_bd->execute_requete($st_requete);
   return $i_idf_adh;
}

?>