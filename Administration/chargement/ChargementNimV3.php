<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

class ChargementNimV3 {
   
   protected $i_nb_actes;
   protected $a_deja_existants;
   
   public function __construct ($pconnexionBD) {
      $this->i_nb_actes = 0;
      $this->a_deja_existants = array();
   }
   
   /**
   * Charge les mariages d'un fichier au format Nimegue v3 dans la base
   * @param string $pst_fichier localisation du fichier
   * @param integer $pi_idf_commune : identifiant de la commune a charger
   * @param integer $pi_idf_source : identifiant de la source 
   * @param integer $pi_idf_releveur : identifiant de l'adherent releveur 
   * @param array $pa_liste_mariages_existants : Liste des mariages existants indexés par date, nom époux, prénom époux, nom épouse, prénom épouse (valeur=true))        
   * @return boolean : a reussi ou pas  
   */ 
	function charge_mariages($pst_fichier,$pi_idf_commune,$pi_idf_source,$pi_idf_releveur,$pa_liste_mariages_existants)
	{
		global $gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd;
		$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
		$union = Union::singleton($connexionBD);
		$stats_patronyme = new StatsPatronyme($connexionBD,$pi_idf_commune,$pi_idf_source);
		$stats_commune = new StatsCommune($connexionBD,$pi_idf_commune,$pi_idf_source);
		$releveur =  new Releveur($connexionBD);
		$a_liste_personnes = array();
		$a_liste_actes = array();  
		$pf=fopen($pst_fichier,"r") or die("Impossible de lire le fichier $pst_fichier");
		//Empeche le chargement de la table le temps de la mise a jour
		$connexionBD->execute_requete("LOCK TABLES `personne` write , `patronyme` write,`prenom` write,`acte` write, `profession` write, `commune_personne` write, `stats_patronyme` write, `stats_commune` write, `union` write,`acte` as a read,`personne` as p1 read,`personne` as p2 read,`union` as u read, `type_acte` as ta read,`type_acte` write, `releveur` write,`adherent` read,`prenom_simple` write, `groupe_prenoms` write");  
		// les redondances de donnees seront traitees par sql load data et la contrainte sur l'index nom
		$i_nb_actes =0 ;
		while (!feof($pf))
		{         
			$st_ligne       = fgets($pf);
			$st_ligne = rtrim($st_ligne);
			// saute les lignes vides
			if (preg_match("/^[ ]*$/",$st_ligne)) continue;
			if (preg_match("/^$/",$st_ligne)) continue;
			$a_champs       = explode(SEP_CSV,$st_ligne);
			// Saute les lignes dont le nombre de champs n'est pas valide
			if (count($a_champs)<61) continue;
			$i_detail_supp  = 0;
			if (est_informatif($a_champs,12,30,16) || a_temoins($a_champs,46,3,4) || $a_champs[58]!='')
				$i_detail_supp  = 1;
			else if (preg_match('/vue/i',$a_champs[8]))
				$i_detail_supp  = 2;
			list($st_version_nim,$st_insee,$st_commune,$i_dept,$st_dept,$st_type_acte,$st_date,$st_date_rep,$st_cote,$st_libre) = array_splice($a_champs,0,10);
			list($st_nom_epx,$st_prn_epx,$st_orig_epx,$st_dnais_epx,$i_age_epx,$st_cmt_epx,$st_prof_epx,$st_nom_excjt_epx,$st_prn_excjt_epx,$st_cmt_excjt_epx,$st_nom_pere_epx,$st_prn_pere_epx,$st_cmt_pere_epx,$st_prof_pere_epx,$st_nom_mere_epx,$st_prn_mere_epx,$st_cmt_mere_epx,$st_prof_mere_epx) = array_splice($a_champs,0,18);
			list($st_nom_epse,$st_prn_epse,$st_orig_epse,$st_dnais_epse,$i_age_epse,$st_cmt_epse,$st_prof_epse,$st_nom_excjt_epse,$st_prn_excjt_epse,$st_cmt_excjt_epse,$st_nom_pere_epse,$st_prn_pere_epse,$st_cmt_pere_epse,$st_prof_pere_epse,$st_nom_mere_epse,$st_prn_mere_epse,$st_cmt_mere_epse,$st_prof_mere_epse) = array_splice($a_champs,0,18);
			list($st_nom_tem1,$st_prn_tem1,$st_cmt_tem1) = array_splice($a_champs,0,3);
			list($st_nom_tem2,$st_prn_tem2,$st_cmt_tem2) = array_splice($a_champs,0,3);
			list($st_nom_tem3,$st_prn_tem3,$st_cmt_tem3) = array_splice($a_champs,0,3);
			list($st_nom_tem4,$st_prn_tem4,$st_cmt_tem4) = array_splice($a_champs,0,3);
			list($st_cmt_acte,$i_num_enreg,$st_permalien) = array_splice($a_champs,0,3);
			// nettoyage des noms
			nettoie_nom($st_nom_epx);
			nettoie_nom($st_nom_epse);
			nettoie_nom($st_nom_excjt_epx);
			nettoie_nom($st_nom_pere_epx);
			nettoie_nom($st_nom_mere_epx);
			nettoie_nom($st_nom_excjt_epse);
			nettoie_nom($st_nom_pere_epse);
			nettoie_nom($st_nom_mere_epse);
			// Nettoyage des prénoms
			nettoie_prenom($st_prn_epx);
			nettoie_prenom($st_prn_epse);
			nettoie_prenom($st_prn_excjt_epx);
			nettoie_prenom($st_prn_pere_epx);
			nettoie_prenom($st_prn_mere_epx);
			nettoie_prenom($st_prn_excjt_epse);
			nettoie_prenom($st_prn_pere_epse);
			nettoie_prenom($st_prn_mere_epse); 			
			if ((!isset($pa_liste_mariages_existants[strval($st_date)][strval($st_nom_epx)][strval($st_prn_epx)][strval($st_nom_epse)][strval($st_prn_epse)])))
			{
				$i_nb_actes++;
				$acte = new Acte($connexionBD,$pi_idf_commune,utf8_vers_cp1252(LIB_MARIAGE),'M',$pi_idf_source,$st_date,$releveur->idf_releveur($pi_idf_releveur));
				$i_annee = $acte->getAnnee();
				$stats_commune->compte_acte(utf8_vers_cp1252(LIB_MARIAGE),$i_annee); 
				$acte->importeNimV3($st_date_rep,$st_cote,$st_libre,$st_cmt_acte);
				$acte->setUrl($st_permalien);
				$acte->setDetailSupp($i_detail_supp);
				$i_acte=$acte->getIdf();
				$a_liste_actes[] = $acte;         
				// Création de l'epoux, de ses éventuels conjoint et parents
				$epoux = new Personne($connexionBD,$i_acte,IDF_PRESENCE_INTV,'M',$st_nom_epx,$st_prn_epx);
				$epoux->importeMarNimV3($st_orig_epx,$st_dnais_epx,$i_age_epx,$st_prof_epx);
				$epoux->setCommentaires($st_cmt_epx);
				$stats_patronyme->maj_patro($st_nom_epx,utf8_vers_cp1252(LIB_MARIAGE),$i_annee);
				if ($st_nom_excjt_epx=='' && $st_prn_excjt_epx!='')
					$st_nom_excjt_epx = LIB_MANQUANT;
				if ($st_nom_excjt_epx!= '')
				{
					// Tentative de récupération d'un fichier Nimgue V3 issu d'un fichier Nimègue V2 
					if ($st_prn_excjt_epx=='' && $st_cmt_excjt_epx== '')
					{
						list($st_nom_excjt_epx,$st_prn_excjt_epx,$st_cmt_excjt_epx) = infos_conjoint($st_nom_excjt_epx);
					}          
					$cjt_epoux = new Personne($connexionBD,$i_acte,IDF_PRESENCE_EXCJT,'F',$st_nom_excjt_epx,$st_prn_excjt_epx);
					$cjt_epoux->setCommentaires($st_cmt_excjt_epx);
					$a_liste_personnes[]=$cjt_epoux;
					$stats_patronyme->maj_patro($st_nom_excjt_epx,utf8_vers_cp1252(LIB_MARIAGE),$i_annee);               
					$union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,utf8_vers_cp1252(LIB_MARIAGE),$epoux->getIdf(),$st_nom_epx,$cjt_epoux->getIdf(),$st_nom_excjt_epx);      
				}   
				if ($st_nom_pere_epx=='' && $st_prn_pere_epx!='')
					$st_nom_pere_epx = LIB_MANQUANT;             
				if ($st_nom_pere_epx!='')
				{
					$pere_epoux = new Personne($connexionBD,$i_acte,IDF_PRESENCE_PERE,'M',$st_nom_pere_epx,$st_prn_pere_epx);
					$stats_patronyme->maj_patro($st_nom_pere_epx,utf8_vers_cp1252(LIB_MARIAGE),$i_annee);
					$pere_epoux->setProfession($st_prof_pere_epx);
					$pere_epoux->setCommentaires($st_cmt_pere_epx);
					$a_liste_personnes[]=$pere_epoux;
					$epoux->setIdfPere($pere_epoux->getIdf());
				}
				if ($st_nom_mere_epx=='' && $st_prn_mere_epx!='')
					$st_nom_mere_epx = LIB_MANQUANT;
				if ($st_nom_mere_epx!='')
				{
					$mere_epoux = new Personne($connexionBD,$i_acte,IDF_PRESENCE_MERE,'F',$st_nom_mere_epx,$st_prn_mere_epx);
					$stats_patronyme->maj_patro($st_nom_mere_epx,utf8_vers_cp1252(LIB_MARIAGE),$i_annee);
					$mere_epoux->setProfession($st_prof_mere_epx); 
					$mere_epoux->setCommentaires($st_cmt_mere_epx);
					$a_liste_personnes[]=$mere_epoux;
					$epoux->setIdfMere($mere_epoux->getIdf());
				}
				$a_liste_personnes[]=$epoux;
				if ($st_nom_pere_epx!='' && $st_nom_mere_epx!='')
					$union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,utf8_vers_cp1252(LIB_MARIAGE),$pere_epoux->getIdf(),$st_nom_pere_epx,$mere_epoux->getIdf(),$st_nom_mere_epx); 
				// Création de l'epouse, de ses éventuels conjoint et parents
				$epouse = new Personne($connexionBD,$i_acte,IDF_PRESENCE_INTV,'F',$st_nom_epse,$st_prn_epse);
				$epouse->importeMarNimV3($st_orig_epse,$st_dnais_epse,$i_age_epse,$st_prof_epse);
				$epouse->setCommentaires($st_cmt_epse);
				$stats_patronyme->maj_patro($st_nom_epse,utf8_vers_cp1252(LIB_MARIAGE),$i_annee);
				if ($st_nom_excjt_epse=='' && $st_prn_excjt_epse!='')
					$st_nom_excjt_epse = LIB_MANQUANT;
				if ($st_nom_excjt_epse!= '')
				{
					// Tentative de récupération d'un fichier Nimègue V3 issu d'un fichier Nimègue V2 
					if ($st_prn_excjt_epse=='' && $st_cmt_excjt_epse== '')
					{
						list($st_nom_excjt_epse,$st_prn_excjt_epse,$st_cmt_excjt_epse) = infos_conjoint($st_nom_excjt_epse);
					}	 
					$cjt_epouse = new Personne($connexionBD,$i_acte,IDF_PRESENCE_EXCJT,'M',$st_nom_excjt_epse,$st_prn_excjt_epse);
					$cjt_epouse->setCommentaires($st_cmt_excjt_epse);
					$stats_patronyme->maj_patro($st_nom_excjt_epse,utf8_vers_cp1252(LIB_MARIAGE),$i_annee);               
					$a_liste_personnes[]=$cjt_epouse;
					$union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,utf8_vers_cp1252(LIB_MARIAGE),$cjt_epouse->getIdf(),$st_nom_excjt_epse,$epouse->getIdf(),$st_nom_epse);   
				}           
				if ($st_nom_pere_epse=='' && $st_prn_pere_epse!='')
					$st_nom_pere_epse = LIB_MANQUANT;         
				if ($st_nom_pere_epse!='')
				{
					$pere_epouse = new Personne($connexionBD,$i_acte,IDF_PRESENCE_PERE,'M',$st_nom_pere_epse,$st_prn_pere_epse);
					$stats_patronyme->maj_patro($st_nom_pere_epse,utf8_vers_cp1252(LIB_MARIAGE),$i_annee);
					$pere_epouse->setProfession($st_prof_pere_epse);
					$pere_epouse->setCommentaires($st_cmt_pere_epse);
					$a_liste_personnes[]=$pere_epouse;
					$epouse->setIdfPere($pere_epouse->getIdf());
				}
				if ($st_nom_mere_epse=='' && $st_prn_mere_epse!='')
					$st_nom_mere_epse = LIB_MANQUANT;
				if ($st_nom_mere_epse!='')
				{
					$mere_epouse = new Personne($connexionBD,$i_acte,IDF_PRESENCE_MERE,'F',$st_nom_mere_epse,$st_prn_mere_epse);
					$mere_epouse->setProfession($st_prof_mere_epse);
					$mere_epouse->setCommentaires($st_cmt_mere_epse);
					$stats_patronyme->maj_patro($st_nom_mere_epse,utf8_vers_cp1252(LIB_MARIAGE),$i_annee);
					$a_liste_personnes[]=$mere_epouse;
					$epouse->setIdfMere($mere_epouse->getIdf());
				}
				$a_liste_personnes[]=$epouse;
				if ($st_nom_pere_epse!='' && $st_nom_mere_epse!='')
					$union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,utf8_vers_cp1252(LIB_MARIAGE),$pere_epouse->getIdf(),$st_nom_pere_epse,$mere_epouse->getIdf(),$st_nom_mere_epse); 
				// Création du lien conjoint entre époux
				$union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,utf8_vers_cp1252(LIB_MARIAGE),$epoux->getIdf(),$st_nom_epx,$epouse->getIdf(),$st_nom_epse);
				if ($st_nom_tem1=='' && ($st_prn_tem1!='' || $st_cmt_tem1!=''))
					$st_nom_tem1 = LIB_MANQUANT;
				if ($st_nom_tem1!='')
				{
					$temoin1 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_TEMOIN,'?',$st_nom_tem1,$st_prn_tem1);
					$temoin1->setCommentaires($st_cmt_tem1);
					$stats_patronyme->maj_patro($st_nom_tem1,utf8_vers_cp1252(LIB_MARIAGE),$i_annee);            
					$a_liste_personnes[]=$temoin1;
				}
				if ($st_nom_tem2=='' && ($st_prn_tem2!='' || $st_cmt_tem2!=''))
					$st_nom_tem2 = LIB_MANQUANT;
				if ($st_nom_tem2!='')
				{
					$temoin2 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_TEMOIN,'?',$st_nom_tem2,$st_prn_tem2);
					$temoin2->setCommentaires($st_cmt_tem2);
					$stats_patronyme->maj_patro($st_nom_tem2,utf8_vers_cp1252(LIB_MARIAGE),$i_annee);           
					$a_liste_personnes[]=$temoin2;
				}
				if ($st_nom_tem3=='' && ($st_prn_tem3!='' || $st_cmt_tem3!=''))
					$st_nom_tem3 = LIB_MANQUANT;   
				if ($st_nom_tem3!='')
				{
					$temoin3 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_TEMOIN,'?',$st_nom_tem3,$st_prn_tem3);
					$temoin3->setCommentaires($st_cmt_tem3);
					$stats_patronyme->maj_patro($st_nom_tem3,utf8_vers_cp1252(LIB_MARIAGE),$i_annee);            
					$a_liste_personnes[]=$temoin3;
				}
				if ($st_nom_tem4=='' && ($st_prn_tem4!='' || $st_cmt_tem4!=''))
					$st_nom_tem4 = LIB_MANQUANT;
				if ($st_nom_tem4!='')
				{
					$temoin4 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_TEMOIN,'?',$st_nom_tem4,$st_prn_tem4);
					$temoin4->setCommentaires($st_cmt_tem4);
					$stats_patronyme->maj_patro($st_nom_tem4,utf8_vers_cp1252(LIB_MARIAGE),$i_annee);            
					$a_liste_personnes[]=$temoin4;
				}
				// Bufferise la création des personnes par bloc
				if ($i_nb_actes%NB_ACTES_BLOC_CHGMT==0)
				{
					if (count($a_liste_personnes)>0 && count($a_liste_actes)>0)
					{
						$a_liste_personnes[0]->sauveCommunePersonne();
						$a_liste_personnes[0]->sauveProfession();
						$a_liste_personnes[0]->sauvePrenom();
						$a_params=array();
						$a_valeurs=array();
						foreach ($a_liste_personnes as $personne)
						{
							list($st_valeurs_courantes,$a_params_courants)=$personne->ligne_sql_a_inserer();
							$a_params=$a_params+$a_params_courants;
							$a_valeurs[]=$st_valeurs_courantes;
						}
						$connexionBD->initialise_params($a_params);
						$st_requete = Personne::requete_base().join(',',$a_valeurs);
						$connexionBD->execute_requete($st_requete);
						$a_liste_personnes= array();
						$a_params=array();
						$a_valeurs=array();
						foreach ($a_liste_actes as $acte)
						{
							list($st_valeurs_courantes,$a_params_courants)=$acte->ligne_sql_a_inserer();
							$a_params=$a_params+$a_params_courants;
							$a_valeurs[]=$st_valeurs_courantes;
						}
						$connexionBD->initialise_params($a_params);
						$st_requete = Acte::requete_base().join(',',$a_valeurs);
						$connexionBD->execute_requete($st_requete);
						$a_liste_actes= array();
						$union->sauve();
					}
				}
			}
			else
			{
				$this->a_deja_existants[] = "Le mariage ".cp1252_vers_utf8($st_prn_epx)." ".cp1252_vers_utf8($st_nom_epx)." X ".cp1252_vers_utf8($st_prn_epse)." ".cp1252_vers_utf8($st_nom_epse)." du $st_date existe déjà";
			} 
		}   
		fclose($pf);   
	
		if (count($a_liste_personnes)>0 && count($a_liste_actes)>0)
		{
			$a_liste_personnes[0]->sauveCommunePersonne();
			$a_liste_personnes[0]->sauveProfession();
			$a_liste_personnes[0]->sauvePrenom();
			$a_params=array();
			$a_valeurs=array();
			foreach ($a_liste_personnes as $personne)
			{
				list($st_valeurs_courantes,$a_params_courants)=$personne->ligne_sql_a_inserer();
				$a_params=$a_params+$a_params_courants;
				$a_valeurs[]=$st_valeurs_courantes;
			}
			$connexionBD->initialise_params($a_params);
			$st_requete = Personne::requete_base().join(',',$a_valeurs);
			$connexionBD->execute_requete($st_requete);
			$a_params=array();
			$a_valeurs=array();
			foreach ($a_liste_actes as $acte)
			{
				list($st_valeurs_courantes,$a_params_courants)=$acte->ligne_sql_a_inserer();
				$a_params=$a_params+$a_params_courants;
				$a_valeurs[]=$st_valeurs_courantes;
			}
			$connexionBD->initialise_params($a_params);
			$st_requete = Acte::requete_base().join(',',$a_valeurs);
			$connexionBD->execute_requete($st_requete);
			$union->sauve();
		}
		$stats_patronyme->sauvePatronyme();	
		$stats_patronyme->sauveTypeActe();	
		$stats_patronyme->sauve();
		$stats_commune->sauve();
		$connexionBD->execute_requete("UNLOCK TABLES");  
		$this->i_nb_actes = $i_nb_actes;
		return true;
}
   
/**
 * Charge les actes divers d'un fichier au format Nimegue v3 dans la base
 * @param string $pst_fichier localisation du fichier
 * @param integer $pi_idf_commune : identifiant de la commune a charger
 * @param integer $pi_idf_source : identifiant de la source 
 * @param integer $pi_idf_releveur : identifiant de l'adherent releveur 
 * @param array $pa_liste_divers_existants : Liste des divers existants indexés par date, nom époux, prénom époux, nom épouse, prénom épouse (valeur=true))  
 * @return boolean : a reussi ou pas  
 */ 
function charge_divers($pst_fichier,$pi_idf_commune,$pi_idf_source,$pi_idf_releveur,$pa_liste_divers_existants)
{
   global $gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd;
   $connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
   $union = Union::singleton($connexionBD);
   $stats_patronyme = new StatsPatronyme($connexionBD,$pi_idf_commune,$pi_idf_source);
   $stats_commune = new StatsCommune($connexionBD,$pi_idf_commune,$pi_idf_source);
   $releveur =  new Releveur($connexionBD);   
   $a_types_acte_par_idf = $connexionBD->liste_clef_par_valeur("select idf,nom from type_acte");
   $a_liste_personnes = array();
   $a_liste_actes = array();  
   $pf=fopen($pst_fichier,"r") or die("Impossible de lire le fichier $pst_fichier");
   // Empeche le chargement de la table le temps de la mise a jour
   $connexionBD->execute_requete("LOCK TABLES `personne` write, `patronyme` write, `prenom` write ,`acte` write, `profession` write, `commune_personne` write, `stats_patronyme` write, `stats_commune` write, `union` write,`personne` as p1 read,`personne` as p2 read,`union` as u read,`type_acte` as ta read,`type_acte` write, `acte` as a read,`releveur` write,`adherent` read,`prenom_simple` write, `groupe_prenoms` write"); 
   // les redondances de donnees seront traitees par sql load data et la contrainte sur l'index nom   
   $i_nb_actes =0 ;
	while (!feof($pf))
	{         
		$st_ligne       = rtrim(fgets($pf));
		// saute les lignes vides
		if (preg_match("/^[ ]*$/",$st_ligne)) continue;
		if (preg_match("/^$/",$st_ligne)) continue;
		$a_champs       = explode(SEP_CSV,$st_ligne);
		// Saute les lignes dont le nombre de champs n'est pas valide
		if (count($a_champs)<65) continue;
		$i_detail_supp  = 0;
		if (est_informatif($a_champs,12,30,16) || a_temoins($a_champs,46,3,4) || $a_champs[58]!='')
			$i_detail_supp  = 1;
		else if (preg_match('/vue/i',$a_champs[8]))
			$i_detail_supp  = 2; 
		list($st_version_nimegue,$st_insee,$st_commune,$i_dept,$st_dept,$st_type_acte_nim,$st_date,$st_date_rep,$st_cote,$st_libre,$st_sigle_type_acte,$st_type_acte) = array_splice($a_champs,0,12);
        
		list($st_nom_intv1,$st_prn_intv1,$c_sexe_intv1,$st_orig_intv1,$st_dnais_intv1,$i_age_intv1,$st_cmt_intv1,$st_prof_intv1,$st_nom_excjt_intv1,$st_prn_excjt_intv1,$st_cmt_excjt_intv1,$st_nom_pere_intv1,$st_prn_pere_intv1,$st_cmt_pere_intv1,$st_prof_pere_intv1,$st_nom_mere_intv1,$st_prn_mere_intv1,$st_cmt_mere_intv1,$st_prof_mere_intv1) = array_splice($a_champs,0,19);
                     
		list($st_nom_intv2,$st_prn_intv2,$c_sexe_intv2,$st_orig_intv2,$st_dnais_intv2,$i_age_intv2,$st_cmt_intv2,$st_prof_intv2,$st_nom_excjt_intv2,$st_prn_excjt_intv2,$st_cmt_excjt_intv2,$st_nom_pere_intv2,$st_prn_pere_intv2,$st_cmt_pere_intv2,$st_prof_pere_intv2,$st_nom_mere_intv2,$st_prn_mere_intv2,$st_cmt_mere_intv2,$st_prof_mere_intv2) = array_splice($a_champs,0,19);      
  
		list($st_nom_tem1,$st_prn_tem1,$st_cmt_tem1) = array_splice($a_champs,0,3);
		list($st_nom_tem2,$st_prn_tem2,$st_cmt_tem2) = array_splice($a_champs,0,3);
		list($st_nom_tem3,$st_prn_tem3,$st_cmt_tem3) = array_splice($a_champs,0,3);
		list($st_nom_tem4,$st_prn_tem4,$st_cmt_tem4) = array_splice($a_champs,0,3);
		list($st_cmt_acte,$i_num_enreg,$st_permalien) = array_splice($a_champs,0,3);
		// nettoyage des noms
		nettoie_nom($st_nom_intv1);
		nettoie_nom($st_nom_intv2);
		nettoie_nom($st_nom_excjt_intv1);
		nettoie_nom($st_nom_pere_intv1);
		nettoie_nom($st_nom_mere_intv1);
		nettoie_nom($st_nom_excjt_intv2);
		nettoie_nom($st_nom_pere_intv2);
		nettoie_nom($st_nom_mere_intv2);
		// Nettoyage des prénoms
		nettoie_prenom($st_prn_intv1);
		nettoie_prenom($st_prn_intv2);
		nettoie_prenom($st_prn_excjt_intv1);
		nettoie_prenom($st_prn_pere_intv1);
		nettoie_prenom($st_prn_mere_intv1);
		nettoie_prenom($st_prn_excjt_intv2);
		nettoie_prenom($st_prn_pere_intv2);
		nettoie_prenom($st_prn_mere_intv2);
		if (!array_key_exists(strval($st_type_acte),$a_types_acte_par_idf))
			$b_acte_nexistepas = true;
		else
			$b_acte_nexistepas = !isset($pa_liste_divers_existants[strval($st_date)][strval($st_nom_intv1)][strval($st_prn_intv1)][strval($st_nom_intv2)][strval($st_prn_intv2)]);
		if ($b_acte_nexistepas)
		{
			$i_nb_actes++;
			$acte = new Acte($connexionBD,$pi_idf_commune,$st_type_acte,$st_sigle_type_acte,$pi_idf_source,$st_date,$releveur->idf_releveur($pi_idf_releveur));
			$i_annee = $acte->getAnnee();
			$stats_commune->compte_acte($st_type_acte,$i_annee); 
			$acte->importeNimV3($st_date_rep,$st_cote,$st_libre,$st_cmt_acte);
			$acte->setUrl($st_permalien);
			$acte->setDetailSupp($i_detail_supp);
			$i_acte=$acte->getIdf();         
			$a_liste_actes[] = $acte;
			// Création de l'intervenant 1, de ses éventuels conjoint et parents
			$intv1 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_INTV,$c_sexe_intv1,$st_nom_intv1,$st_prn_intv1);
			$intv1->importeDivNimV3($st_orig_intv1,$st_dnais_intv1,$i_age_intv1,$st_prof_intv1);
			$intv1->setCommentaires($st_cmt_intv1);
			$stats_patronyme->maj_patro($st_nom_intv1,$st_type_acte,$i_annee);
			if ($st_nom_excjt_intv1=='' && $st_prn_excjt_intv1!='')
               $st_nom_excjt_intv1 = LIB_MANQUANT;
			if ($st_nom_excjt_intv1!= '')
			{         
				$c_sexe_cjt_intv1 = ($c_sexe_intv1=='M') ? 'F' : 'M';
				// Tentative de récupération d'un fichier Nimègue V3 issu d'un fichier Nimègue V2 
				if ($st_prn_excjt_intv1=='' && $st_cmt_excjt_intv1== '')
				{
					list($st_nom_excjt_intv1,$st_prn_excjt_intv1,$st_cmt_excjt_intv1) = infos_conjoint($st_nom_excjt_intv1);
				}     
				$cjt_intv1 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_EXCJT,$c_sexe_cjt_intv1,$st_nom_excjt_intv1,$st_prn_excjt_intv1);
				$cjt_intv1->setCommentaires($st_cmt_excjt_intv1);
				$stats_patronyme->maj_patro($st_nom_excjt_intv1,$st_type_acte,$i_annee);               
				$a_liste_personnes[]=$cjt_intv1;
				switch ($c_sexe_intv1)
				{
					case 'M' : $union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,$st_type_acte,$intv1->getIdf(),$st_nom_intv1,$cjt_intv1->getIdf(),$st_nom_excjt_intv1);break;
					case 'F' : $union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,$st_type_acte,$cjt_intv1->getIdf(),$st_nom_excjt_intv1,$intv1->getIdf(),$st_nom_intv1);break;
					default : $union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,$st_type_acte,$intv1->getIdf(),$st_nom_intv1,$cjt_intv1->getIdf(),$st_nom_excjt_intv1);                           
				}
			}   
			if ($st_nom_pere_intv1=='' && $st_prn_pere_intv1!='')
				$st_nom_pere_intv1 = LIB_MANQUANT;
			if ($st_nom_pere_intv1!='')
			{
				$pere_intv1= new Personne($connexionBD,$i_acte,IDF_PRESENCE_PERE,'M',$st_nom_pere_intv1,$st_prn_pere_intv1);
				$stats_patronyme->maj_patro($st_nom_pere_intv1,$st_type_acte,$i_annee);
				$pere_intv1->setProfession($st_prof_pere_intv1);
				$pere_intv1->setCommentaires($st_cmt_pere_intv1);
				$a_liste_personnes[]=$pere_intv1;
				$intv1->setIdfPere($pere_intv1->getIdf());
			}
			if ($st_nom_mere_intv1=='' && $st_prn_mere_intv1!='')
				$st_nom_mere_intv1 = LIB_MANQUANT;
			if ($st_nom_mere_intv1!='')
			{
				$mere_intv1= new Personne($connexionBD,$i_acte,IDF_PRESENCE_MERE,'F',$st_nom_mere_intv1,$st_prn_mere_intv1);
				$stats_patronyme->maj_patro($st_nom_mere_intv1,$st_type_acte,$i_annee);
				$mere_intv1->setProfession($st_prof_mere_intv1);
				$mere_intv1->setCommentaires($st_cmt_mere_intv1);
				$a_liste_personnes[]=$mere_intv1;
				$intv1->setIdfMere($mere_intv1->getIdf());
			}
			$a_liste_personnes[]=$intv1;
			if ($st_nom_pere_intv1!='' && $st_nom_mere_intv1!='')
				$union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,$st_type_acte,$pere_intv1->getIdf(),$st_nom_pere_intv1,$mere_intv1->getIdf(),$st_nom_mere_intv1); 
			// Création de l'intervenant2, de ses éventuels conjoint et parents
			if($st_nom_intv2!='')
			{
				$intv2 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_INTV,$c_sexe_intv2,$st_nom_intv2,$st_prn_intv2);
				$intv2->importeDivNimV3($st_orig_intv2,$st_dnais_intv2,$i_age_intv2,$st_prof_intv2);
				$intv2->setCommentaires($st_cmt_intv2);
				$stats_patronyme->maj_patro($st_nom_intv2,$st_type_acte,$i_annee);
				if ($st_nom_excjt_intv2=='' && $st_prn_excjt_intv2!='')
					$st_nom_excjt_intv2 = LIB_MANQUANT;
				if ($st_nom_excjt_intv2!= '')
				{
					$c_sexe_cjt_intv2 = ($c_sexe_intv2=='M') ? 'F' : 'M';
					// Tentative de récupèération d'un fichier Nimgue V3 issu d'un fichier Nimègue V2 
					if ($st_prn_excjt_intv2=='' && $st_cmt_excjt_intv2== '')
					{
						list($st_nom_excjt_intv2,$st_prn_excjt_intv2,$st_cmt_excjt_intv2) = infos_conjoint($st_nom_excjt_intv2);
					}      
					$cjt_intv2 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_EXCJT,$c_sexe_cjt_intv2,$st_nom_excjt_intv2,$st_prn_excjt_intv2);
					$stats_patronyme->maj_patro($st_nom_excjt_intv2,$st_type_acte,$i_annee);
					$cjt_intv2->setCommentaires($st_cmt_excjt_intv2);
					$a_liste_personnes[]=$cjt_intv2;
					switch ($c_sexe_intv2)
					{
						case 'M' : $union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,$st_type_acte,$intv2->getIdf(),$st_nom_intv2,$cjt_intv2->getIdf(),$st_nom_excjt_intv2);break;
						case 'F' : $union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,$st_type_acte,$cjt_intv2->getIdf(),$st_nom_excjt_intv2,$intv2->getIdf(),$st_nom_intv2);break;
						default : $union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,$st_type_acte,$intv2->getIdf(),$st_nom_intv2,$cjt_intv2->getIdf(),$st_nom_excjt_intv2);
					}
                                     
				}    
				if ($st_nom_pere_intv2=='' && $st_prn_pere_intv2!='')
					$st_nom_pere_intv2 = LIB_MANQUANT;         
				if ($st_nom_pere_intv2!='')
				{
					$pere_intv2 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_PERE,'M',$st_nom_pere_intv2,$st_prn_pere_intv2);
					$stats_patronyme->maj_patro($st_nom_pere_intv2,$st_type_acte,$i_annee);
					$pere_intv2->setProfession($st_prof_pere_intv2);
					$pere_intv2->setCommentaires($st_cmt_pere_intv2);
					$a_liste_personnes[]=$pere_intv2;
					$intv2->setIdfPere($pere_intv2->getIdf());
				}
				if ($st_nom_mere_intv2=='' && $st_prn_mere_intv2!='')
					$st_nom_mere_intv2 = LIB_MANQUANT;
				if ($st_nom_mere_intv2!='')
				{
					$mere_intv2 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_MERE,'F',$st_nom_mere_intv2,$st_prn_mere_intv2);
					$stats_patronyme->maj_patro($st_nom_mere_intv2,$st_type_acte,$i_annee);
					$mere_intv2->setProfession($st_prof_mere_intv2);
					$mere_intv2->setCommentaires($st_cmt_mere_intv2);
					$a_liste_personnes[]=$mere_intv2;
					$intv2->setIdfMere($mere_intv2->getIdf());
				}
				$a_liste_personnes[]=$intv2;
				if ($st_nom_pere_intv2!='' && $st_nom_mere_intv2!='')
					$union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,$st_type_acte,$pere_intv2->getIdf(),$st_nom_pere_intv2,$mere_intv2->getIdf(),$st_nom_mere_intv2); 
				// Création du lien couple entre intervenant si c'est un couple :
				// personnes de sexes différents
				if (($c_sexe_intv1=='M' && $c_sexe_intv2=='F')
					||
					($c_sexe_intv1=='F' && $c_sexe_intv2=='M')
					||
                 ($c_sexe_intv1=='?' && $c_sexe_intv2=='?')
                )
					$union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,$st_type_acte,$intv1->getIdf(),$st_nom_intv1,$intv2->getIdf(),$st_nom_intv2);   
			}
			if ($st_nom_tem1=='' && ($st_prn_tem1!='' || $st_cmt_tem1!=''))
               $st_nom_tem1 = LIB_MANQUANT;
			if ($st_nom_tem1!='')
			{
				$temoin1 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_TEMOIN,'?',$st_nom_tem1,$st_prn_tem1);
				$temoin1->setCommentaires($st_cmt_tem1);
				$stats_patronyme->maj_patro($st_nom_tem1,$st_type_acte,$i_annee);            
				$a_liste_personnes[]=$temoin1;
			}
			if ($st_nom_tem2=='' && ($st_prn_tem2!='' || $st_cmt_tem2!=''))
				$st_nom_tem2 = LIB_MANQUANT;
			if ($st_nom_tem2!='')
			{
				$temoin2 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_TEMOIN,'?',$st_nom_tem2,$st_prn_tem2);
				$temoin2->setCommentaires($st_cmt_tem2);
				$stats_patronyme->maj_patro($st_nom_tem2,$st_type_acte,$i_annee);           
				$a_liste_personnes[]=$temoin2;
			}
			if ($st_nom_tem3=='' && ($st_prn_tem3!='' || $st_cmt_tem3!=''))
				$st_nom_tem3 = LIB_MANQUANT;   
			if ($st_nom_tem3!='')
			{
				$temoin3 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_TEMOIN,'?',$st_nom_tem3,$st_prn_tem3);
				$temoin3->setCommentaires($st_cmt_tem3);
				$stats_patronyme->maj_patro($st_nom_tem3,$st_type_acte,$i_annee);            
				$a_liste_personnes[]=$temoin3;
			}
			if ($st_nom_tem4=='' && ($st_prn_tem4!='' || $st_cmt_tem4!=''))
               $st_nom_tem4 = LIB_MANQUANT;
			if ($st_nom_tem4!='')
			{
				$temoin4 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_TEMOIN,'?',$st_nom_tem4,$st_prn_tem4);
				$temoin4->setCommentaires($st_cmt_tem4);
				$stats_patronyme->maj_patro($st_nom_tem4,$st_type_acte,$i_annee);            
				$a_liste_personnes[]=$temoin4;
			}
			// Bufferise la création des personnes par bloc
			if ($i_nb_actes%NB_ACTES_BLOC_CHGMT==0)
			{
				if (count($a_liste_personnes)>0 && count($a_liste_actes)>0)
				{
					$a_liste_personnes[0]->sauveCommunePersonne();
					$a_liste_personnes[0]->sauveProfession();
					$a_liste_personnes[0]->sauvePrenom();
					$a_params=array();
					$a_valeurs=array();
					foreach ($a_liste_personnes as $personne)
					{
						list($st_valeurs_courantes,$a_params_courants)=$personne->ligne_sql_a_inserer();
						$a_params=$a_params+$a_params_courants;
						$a_valeurs[]=$st_valeurs_courantes;
					}
					$connexionBD->initialise_params($a_params);
					$st_requete = Personne::requete_base().join(',',$a_valeurs);
					$connexionBD->execute_requete($st_requete);
					$a_liste_personnes= array();
					$a_liste_actes[0]->sauveTypeActe();
					$a_params=array();
					$a_valeurs=array();
					foreach ($a_liste_actes as $acte)
					{
						list($st_valeurs_courantes,$a_params_courants)=$acte->ligne_sql_a_inserer();
						$a_params=$a_params+$a_params_courants;
						$a_valeurs[]=$st_valeurs_courantes;
					}
					$connexionBD->initialise_params($a_params);
					$st_requete = Acte::requete_base().join(',',$a_valeurs);
					$connexionBD->execute_requete($st_requete);
					$a_liste_actes= array();
					$union->sauve();
				}
			}
		}
		else
		{
			$this->a_deja_existants[] ="L'acte divers ".cp1252_vers_utf8($st_prn_intv1)." ".cp1252_vers_utf8($st_nom_intv1)." X ".cp1252_vers_utf8($st_prn_intv2)." ".cp1252_vers_utf8($st_nom_intv2)." du $st_date existe déjà";
		} 
	} 
	fclose($pf);      
	if (count($a_liste_personnes)>0 && count($a_liste_actes)>0)
	{
		$a_liste_personnes[0]->sauveCommunePersonne();
		$a_liste_personnes[0]->sauveProfession();
		$a_liste_personnes[0]->sauvePrenom();
		$a_params=array();
		$a_valeurs=array();
		foreach ($a_liste_personnes as $personne)
		{
			list($st_valeurs_courantes,$a_params_courants)=$personne->ligne_sql_a_inserer();
			$a_params=$a_params+$a_params_courants;
			$a_valeurs[]=$st_valeurs_courantes;
		}
		$connexionBD->initialise_params($a_params);
		$st_requete = Personne::requete_base().join(',',$a_valeurs);
		$connexionBD->execute_requete($st_requete);
		$a_liste_personnes= array();
		$a_liste_actes[0]->sauveTypeActe();
		$a_params=array();
		$a_valeurs=array();
		foreach ($a_liste_actes as $acte)
		{
			list($st_valeurs_courantes,$a_params_courants)=$acte->ligne_sql_a_inserer();
			$a_params=$a_params+$a_params_courants;
			$a_valeurs[]=$st_valeurs_courantes;
		}
		$connexionBD->initialise_params($a_params);
		$st_requete = Acte::requete_base().join(',',$a_valeurs);
		$connexionBD->execute_requete($st_requete);
		$a_liste_actes= array();
		$union->sauve();
	}
	$stats_patronyme->sauvePatronyme();	
	$stats_patronyme->sauveTypeActe();		
	$stats_patronyme->sauve();
	$stats_commune->sauve();
	$connexionBD->execute_requete("UNLOCK TABLES");  
	$this->i_nb_actes = $i_nb_actes;   
	return true;
} 

/**
 * Charge les naissances d'un fichier au format Nimegue v3 dans la base
 * @param string $pst_fichier localisation du fichier
 * @param integer $pi_idf_commune : identifiant de la commune a charger
 * @param integer $pi_idf_source : identifiant de la source 
 * @param integer $pi_idf_releveur : identifiant de l'adherent releveur 
 * @param array $pa_liste_naissances_existantes : Liste des naissances existantes indexées par date, nom , prénom,(valeur=true))    
 * @return boolean : a reussi ou pas  
 */ 
function charge_naissances($pst_fichier,$pi_idf_commune,$pi_idf_source,$pi_idf_releveur,$pa_liste_naissances_existantes)
{
	global $gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd;
	$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
	$union = Union::singleton($connexionBD);
	$stats_patronyme = new StatsPatronyme($connexionBD,$pi_idf_commune,$pi_idf_source);
	$stats_commune = new StatsCommune($connexionBD,$pi_idf_commune,$pi_idf_source);
	$releveur =  new Releveur($connexionBD);
	$a_liste_personnes = array();
	$a_liste_actes = array();      
	$pf=fopen($pst_fichier,"r") or die("Impossible de lire $pst_fichier");
	// Empeche le chargement de la table le temps de la mise a jour
   $connexionBD->execute_requete("LOCK TABLES `personne` write, `patronyme` write , `prenom` write ,`acte` write, `profession` write, `commune_personne` write, `stats_patronyme` write, `stats_commune` write, `union` write, `acte` as a read, `personne` as p read,`personne` as pers_pere read,`personne` as 	pers_mere read,`type_acte` as ta read,`type_acte` write, `releveur` write,`adherent` read,`prenom_simple` write, `groupe_prenoms` write");
	// les redondances de donnees seront traitees par sql load data et la contrainte sur l'index nom
	$i_nb_actes =0 ;
	while (!feof($pf))
	{      
		$st_ligne       = fgets($pf);
		// saute les lignes vides
		if (preg_match("/^[ ]*$/",$st_ligne)) continue;
		if (preg_match("/^$/",$st_ligne)) continue;
		$a_champs       = explode(SEP_CSV,$st_ligne);
		// Saute les lignes dont le nombre de champs n'est pas valide
		if (count($a_champs)<31) continue;
		list($st_version_nim,$st_insee,$st_commune,$i_dept,$st_dept,$st_type_acte,$st_date,$st_date_rep,$st_cote,$st_libre) = array_splice($a_champs,0,10);
		list($st_nom,$st_prn,$st_sexe,$st_cmt,$st_nom_pere,$st_prn_pere,$st_cmt_pere,$st_prof_pere,$st_nom_mere,$st_prn_mere,$st_cmt_mere,$st_prof_mere) = 	array_splice($a_champs,0,12);
		list($st_nom_tem1,$st_prn_tem1,$st_cmt_tem1) = array_splice($a_champs,0,3);
		list($st_nom_tem2,$st_prn_tem2,$st_cmt_tem2) = array_splice($a_champs,0,3);
		list($st_cmt_acte,$i_num_enreg,$st_permalien) = array_splice($a_champs,0,3);
		// nettoyage des noms
		nettoie_nom($st_nom);    
		nettoie_nom($st_nom_pere);
		nettoie_nom($st_nom_mere);
		// Nettoyage des prénoms
		nettoie_prenom($st_prn);
		nettoie_prenom($st_prn_pere);
		nettoie_prenom($st_prn_mere);          

		if (!isset($pa_liste_naissances_existantes[strval($st_date)][strval($st_nom)][strval($st_prn)]))
		{  
			$i_nb_actes++;
			$acte = new Acte($connexionBD,$pi_idf_commune,utf8_vers_cp1252(LIB_NAISSANCE),'N',$pi_idf_source,$st_date,$releveur->idf_releveur($pi_idf_releveur));
			$i_annee = $acte->getAnnee();
			//print("$st_date,$st_nom,$st_prn,$st_sexe,$st_nom_pere,$st_prn_pere,$st_nom_mere,$st_prn_mere<br>");
			$stats_commune->compte_acte(utf8_vers_cp1252(LIB_NAISSANCE),$i_annee); 
			$acte->importeNimV3($st_date_rep,$st_cote,$st_libre,$st_cmt_acte);
			$acte->setUrl($st_permalien);
			$acte->setDetailSupp(1); // à compléter si l'on veut affiner le détail
			$i_acte = $acte->getIdf();
			$a_liste_actes[] = $acte; 
			// Création du nouveau né
			$nouveaune = new Personne($connexionBD,$i_acte,IDF_PRESENCE_INTV,$st_sexe,$st_nom,$st_prn);
			$nouveaune->setCommentaires($st_cmt);        
			$stats_patronyme->maj_patro($st_nom,utf8_vers_cp1252(LIB_NAISSANCE),$i_annee);
			if ($st_nom_pere=='' && $st_prn_pere!='')
				$st_nom_pere = LIB_MANQUANT;   
			if ($st_nom_pere!='')
			{
				$pere = new Personne($connexionBD,$i_acte,IDF_PRESENCE_PERE,'M',$st_nom_pere,$st_prn_pere);
				$stats_patronyme->maj_patro($st_nom_pere,utf8_vers_cp1252(LIB_NAISSANCE),$i_annee);
				$pere->setProfession($st_prof_pere);
				$pere->setCommentaires($st_cmt_pere);
				$a_liste_personnes[]=$pere;
				$nouveaune->setIdfPere($pere->getIdf());
			}
			if ($st_nom_mere=='' && $st_prn_mere!='')
				$st_nom_mere = LIB_MANQUANT;
			if ($st_nom_mere!='')
			{
				$mere = new Personne($connexionBD,$i_acte,IDF_PRESENCE_MERE,'F',$st_nom_mere,$st_prn_mere);
				$stats_patronyme->maj_patro($st_nom_mere,utf8_vers_cp1252(LIB_NAISSANCE),$i_annee);
				$mere->setProfession($st_prof_mere);
				$mere->setCommentaires($st_cmt_mere);
				$a_liste_personnes[]=$mere;
				$nouveaune->setIdfMere($mere->getIdf());
			}
			$a_liste_personnes[]=$nouveaune;
			if ($st_nom_pere!='' && $st_nom_mere!='')
				$union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,utf8_vers_cp1252(LIB_NAISSANCE),$pere->getIdf(),$st_nom_pere,$mere->getIdf(),$st_nom_mere);
			if ($st_nom_tem1=='' && ($st_prn_tem1!='' || $st_cmt_tem1!=''))
				$st_nom_tem1 = LIB_MANQUANT;   
			if ($st_nom_tem1!='')
			{
				$temoin1 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_PARRAIN,'?',$st_nom_tem1,$st_prn_tem1);
				$temoin1->setCommentaires($st_cmt_tem1);
				$stats_patronyme->maj_patro($st_nom_tem1,utf8_vers_cp1252(LIB_NAISSANCE),$i_annee);            
				$a_liste_personnes[]=$temoin1;
			}
			if ($st_nom_tem2=='' && ($st_prn_tem2!='' || $st_cmt_tem2!=''))
				$st_nom_tem2 = LIB_MANQUANT;
			if ($st_nom_tem2!='')
			{
				$temoin2 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_MARRAINE,'?',$st_nom_tem2,$st_prn_tem2);
				$temoin2->setCommentaires($st_cmt_tem2);
				$stats_patronyme->maj_patro($st_nom_tem2,utf8_vers_cp1252(LIB_NAISSANCE),$i_annee);           
				$a_liste_personnes[]=$temoin2;
			}
			// Bufferise la création des personnes par bloc
			if ($i_nb_actes%NB_ACTES_BLOC_CHGMT==0)
			{
				if (count($a_liste_personnes)>0 && count($a_liste_actes)>0)
				{
					$a_liste_personnes[0]->sauveCommunePersonne();
					$a_liste_personnes[0]->sauveProfession();
					$a_liste_personnes[0]->sauvePrenom();
					$a_params=array();
					$a_valeurs=array();
					foreach ($a_liste_personnes as $personne)
					{
						list($st_valeurs_courantes,$a_params_courants)=$personne->ligne_sql_a_inserer();
						$a_params=$a_params+$a_params_courants;
						$a_valeurs[]=$st_valeurs_courantes;
					}
					$connexionBD->initialise_params($a_params);
					$st_requete = Personne::requete_base().join(',',$a_valeurs);
					$connexionBD->execute_requete($st_requete);
					$a_liste_personnes= array();
					$a_params=array();
					$a_valeurs=array();
					foreach ($a_liste_actes as $acte)
					{
						list($st_valeurs_courantes,$a_params_courants)=$acte->ligne_sql_a_inserer();
						$a_params=$a_params+$a_params_courants;
						$a_valeurs[]=$st_valeurs_courantes;
					}
					$connexionBD->initialise_params($a_params);
					$st_requete = Acte::requete_base().join(',',$a_valeurs);
					$connexionBD->execute_requete($st_requete);
					$a_liste_actes= array();
					$union->sauve();
				}
			}
		}
		else
		{
			$this->a_deja_existants[] = "La naissance de ".cp1252_vers_utf8($st_prn)." ".cp1252_vers_utf8($st_nom)." du $st_date existe déjà";
		} 
	} 
	fclose($pf);   	
	if (count($a_liste_personnes)>0 && count($a_liste_actes)>0)
	{
		$a_liste_personnes[0]->sauveCommunePersonne();
		$a_liste_personnes[0]->sauveProfession();
		$a_liste_personnes[0]->sauvePrenom();
		$a_params=array();
		$a_valeurs=array();
		foreach ($a_liste_personnes as $personne)
		{
			list($st_valeurs_courantes,$a_params_courants)=$personne->ligne_sql_a_inserer();
			$a_params=$a_params+$a_params_courants;
			$a_valeurs[]=$st_valeurs_courantes;
		}
		$connexionBD->initialise_params($a_params);
		$st_requete = Personne::requete_base().join(',',$a_valeurs);
		$connexionBD->execute_requete($st_requete);
		$a_params=array();
		$a_valeurs=array();
		foreach ($a_liste_actes as $acte)
		{
			list($st_valeurs_courantes,$a_params_courants)=$acte->ligne_sql_a_inserer();
			$a_params=$a_params+$a_params_courants;
			$a_valeurs[]=$st_valeurs_courantes;
		}
		$connexionBD->initialise_params($a_params);
		$st_requete = Acte::requete_base().join(',',$a_valeurs);
		$connexionBD->execute_requete($st_requete);
		$union->sauve();
	}
	$stats_patronyme->sauvePatronyme();	
	$stats_patronyme->sauveTypeActe();		
	$stats_patronyme->sauve();
	$stats_commune->sauve();
    	
	$connexionBD->execute_requete("UNLOCK TABLES");  
	$this->i_nb_actes = $i_nb_actes;   
	return true;
}

/**
 * Charge les deces d'un fichier au format Nimegue v2 dans la base
 * @param string $pst_fichier localisation du fichier
 * @param integer $pi_idf_commune : identifiant de la commune a charger
 * @param integer $pi_idf_source : identifiant de la source 
 * @param integer $pi_idf_releveur : identifiant de l'adherent releveur 
 * @param array $pa_liste_deces_existants : Liste des deces existants indexées par date, nom , prénom,(valeur=true))  
 * @return boolean : a reussi ou pas  
 */ 
function charge_deces($pst_fichier,$pi_idf_commune,$pi_idf_source,$pi_idf_releveur,$pa_liste_deces_existants)
{
   global $gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd;
   $connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
   $union = Union::singleton($connexionBD);
   $stats_patronyme = new StatsPatronyme($connexionBD,$pi_idf_commune,$pi_idf_source);
   $stats_commune = new StatsCommune($connexionBD,$pi_idf_commune,$pi_idf_source);
   $releveur =  new Releveur($connexionBD);
   $a_liste_personnes = array();
   $a_liste_actes = array();  
   $pf=fopen($pst_fichier,"r") or die("Impossible de lire $pst_fichier");
   // Empeche le chargement de la table le temps de la mise a jour
   $connexionBD->execute_requete("LOCK TABLES `personne` write , `patronyme` write ,`prenom` write,`acte` write, `profession` write, `commune_personne` write, `stats_patronyme` write, `stats_commune` write, `union` write,`acte` as a read, `personne` as p read,`personne` as pers_pere read,`personne` as pers_mere read,`type_acte` as ta read,`type_acte` write, `releveur` write,`adherent` read,`prenom_simple` write, `groupe_prenoms` write");
   // les redondances de donnees seront traitees par sql load data et la contrainte sur l'index nom
   $i_nb_actes =0 ;
   while (!feof($pf))
   {      
		$st_ligne       = rtrim(fgets($pf));
		// saute les lignes vides
		if (preg_match("/^[ ]*$/",$st_ligne)) continue;
		if (preg_match("/^$/",$st_ligne)) continue;
		$a_champs       = explode(SEP_CSV,$st_ligne);
		// Saute les lignes dont le nombre de champs n'est pas valide
		if (count($a_champs)<39) continue;
		list($st_version_nimegue,$st_insee,$st_commune,$i_dept,$st_dept,$st_type_acte,$st_date,$st_date_rep,$st_cote,$st_libre) = array_splice($a_champs,0,10);
		list($st_nom,$st_prn,$st_orig,$st_dnais,$c_sexe,$st_age,$st_cmt,$st_prof,$st_nom_cjt,$st_prn_cjt,$st_cmt_cjt,$st_prof_cjt,$st_nom_pere,$st_prn_pere,$st_cmt_pere,$st_prof_pere,$st_nom_mere,$st_prn_mere,$st_cmt_mere,$st_prof_mere) = array_splice($a_champs,0,20);
      
		list($st_nom_tem1,$st_prn_tem1,$st_cmt_tem1) = array_splice($a_champs,0,3);
		list($st_nom_tem2,$st_prn_tem2,$st_cmt_tem2) = array_splice($a_champs,0,3);
		list($st_cmt_acte,$i_num_enreg,$st_permalien) = array_splice($a_champs,0,3);
		// nettoyage des noms
		nettoie_nom($st_nom);    
		nettoie_nom($st_nom_pere);
		nettoie_nom($st_nom_mere);
		nettoie_nom($st_nom_cjt);
		// Nettoyage des prénoms
		nettoie_prenom($st_prn);
		nettoie_prenom($st_prn_pere);
		nettoie_prenom($st_prn_mere);
		nettoie_prenom($st_prn_cjt);  		
		if (!isset($pa_liste_deces_existants[strval($st_date)][strval($st_nom)][strval($st_prn)]))
		{
			$i_nb_actes++;
			$acte = new Acte($connexionBD,$pi_idf_commune,utf8_vers_cp1252(LIB_DECES),'D',$pi_idf_source,$st_date,$releveur->idf_releveur($pi_idf_releveur));
			$i_annee = $acte->getAnnee();
			$stats_commune->compte_acte(utf8_vers_cp1252(LIB_DECES),$i_annee); 
			$acte->importeNimV3($st_date_rep,$st_cote,$st_libre,$st_cmt_acte);
			$acte->setUrl($st_permalien);
			$acte->setDetailSupp(1); // à compléter si l'on veut affiner le détail
			$i_acte = $acte->getIdf(); 
			$a_liste_actes[]=$acte;
			// Création du nouveau né
			$defunt = new Personne($connexionBD,$i_acte,IDF_PRESENCE_INTV,$c_sexe,$st_nom,$st_prn);
			$defunt->importeDecNimV3($st_orig,$st_dnais,$st_age,$st_prof,$st_cmt);   
			$stats_patronyme->maj_patro($st_nom,utf8_vers_cp1252(LIB_DECES),$i_annee);

			if ($st_nom_cjt!= '')
			{
				$c_sexe_cjt = ($c_sexe=='M')? 'F': 'M';
				$cjt_defunt = new Personne($connexionBD,$i_acte,IDF_PRESENCE_EXCJT,$c_sexe_cjt,$st_nom_cjt,$st_prn_cjt);
				$cjt_defunt->setProfession($st_prof_cjt);
				$cjt_defunt->setCommentaires($st_cmt_cjt);
				$stats_patronyme->maj_patro($st_nom_cjt,utf8_vers_cp1252(LIB_DECES),$i_annee);               
				$a_liste_personnes[]=$cjt_defunt;
				switch($c_sexe)
				{
					case 'M': $union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,utf8_vers_cp1252(LIB_DECES),$defunt->getIdf(),$st_nom,$cjt_defunt->getIdf(),$st_nom_cjt);break;
					case 'F': $union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,utf8_vers_cp1252(LIB_DECES),$cjt_defunt->getIdf(),$st_nom_cjt,$defunt->getIdf(),$st_nom);break;
					default: $union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,utf8_vers_cp1252(LIB_DECES),$defunt->getIdf(),$st_nom,$cjt_defunt->getIdf(),$st_nom_cjt);
				}
             
			}
			if ($st_nom_pere=='' && $st_prn_pere!='')
				$st_nom_pere = LIB_MANQUANT;              
			if ($st_nom_pere!='')
			{
				$pere = new Personne($connexionBD,$i_acte,IDF_PRESENCE_PERE,'M',$st_nom_pere,$st_prn_pere);
				$stats_patronyme->maj_patro($st_nom_pere,utf8_vers_cp1252(LIB_DECES),$i_annee);
				$pere->setProfession($st_prof_pere);
				$pere->setCommentaires($st_cmt_pere);
				$a_liste_personnes[]=$pere;
				$defunt->setIdfPere($pere->getIdf());
			}
			if ($st_nom_mere=='' && $st_prn_mere!='')
                $st_nom_mere = LIB_MANQUANT;
			if ($st_nom_mere!='')
			{
				$mere = new Personne($connexionBD,$i_acte,IDF_PRESENCE_MERE,'F',$st_nom_mere,$st_prn_mere);
				$stats_patronyme->maj_patro($st_nom_mere,utf8_vers_cp1252(LIB_DECES),$i_annee);
				$mere->setProfession($st_prof_mere);
				$mere->setCommentaires($st_cmt_mere);
				$a_liste_personnes[]=$mere;
				$defunt->setIdfMere($mere->getIdf());
			}

			$a_liste_personnes[]=$defunt;
        
			if ($st_nom_pere!='' && $st_nom_mere!='')
				$union->ajoute($pi_idf_source,$pi_idf_commune,$i_acte,utf8_vers_cp1252(LIB_DECES),$pere->getIdf(),$st_nom_pere,$mere->getIdf(),$st_nom_mere);
			if ($st_nom_tem1=='' && ($st_prn_tem1!='' || $st_cmt_tem1!=''))
				$st_nom_tem1 = LIB_MANQUANT;
			if ($st_nom_tem1!='')
			{
				$temoin1 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_TEMOIN,'?',$st_nom_tem1,$st_prn_tem1);
				$temoin1->setCommentaires($st_cmt_tem1);
				$stats_patronyme->maj_patro($st_nom_tem1,utf8_vers_cp1252(LIB_DECES),$i_annee);            
				$a_liste_personnes[]=$temoin1;
			}
			if ($st_nom_tem2=='' && ($st_prn_tem2!='' || $st_cmt_tem2!=''))
				$st_nom_tem2 = LIB_MANQUANT;
			if ($st_nom_tem2!='')
			{
				$temoin2 = new Personne($connexionBD,$i_acte,IDF_PRESENCE_TEMOIN,'?',$st_nom_tem2,$st_prn_tem2);
				$temoin2->setCommentaires($st_cmt_tem2);
				$stats_patronyme->maj_patro($st_nom_tem2,utf8_vers_cp1252(LIB_DECES),$i_annee);           
				$a_liste_personnes[]=$temoin2;
			}
			// Bufferise la création des personnes par bloc
			if ($i_nb_actes%NB_ACTES_BLOC_CHGMT==0)
			{
				if (count($a_liste_personnes)>0 && count($a_liste_actes)>0)
				{
					$a_liste_personnes[0]->sauveCommunePersonne();
					$a_liste_personnes[0]->sauveProfession();
					$a_liste_personnes[0]->sauvePrenom();
					$a_params=array();
					$a_valeurs=array();
					foreach ($a_liste_personnes as $personne)
					{
						list($st_valeurs_courantes,$a_params_courants)=$personne->ligne_sql_a_inserer();
						$a_params=$a_params+$a_params_courants;
						$a_valeurs[]=$st_valeurs_courantes;
					}
					$connexionBD->initialise_params($a_params);
					$st_requete = Personne::requete_base().join(',',$a_valeurs);
					$connexionBD->execute_requete($st_requete);
					$a_liste_personnes= array();
					$a_params=array();
					$a_valeurs=array();
					foreach ($a_liste_actes as $acte)
					{
						list($st_valeurs_courantes,$a_params_courants)=$acte->ligne_sql_a_inserer();
						$a_params=$a_params+$a_params_courants;
						$a_valeurs[]=$st_valeurs_courantes;
					}
					$connexionBD->initialise_params($a_params);
					$st_requete = Acte::requete_base().join(',',$a_valeurs);
					$connexionBD->execute_requete($st_requete);
					$a_liste_actes= array();
					$union->sauve();
				}
			}
		}
		else
		{
			$this->a_deja_existants[] = "Le décès de ".cp1252_vers_utf8($st_prn)." ".cp1252_vers_utf8($st_nom)." du $st_date existe déjà";
		} 
	} 
	fclose($pf);  		
	if (count($a_liste_personnes)>0 && count($a_liste_actes)>0)
	{
		$a_liste_personnes[0]->sauveCommunePersonne();
		$a_liste_personnes[0]->sauveProfession();
		$a_liste_personnes[0]->sauvePrenom();
		$a_params=array();
		$a_valeurs=array();
		foreach ($a_liste_personnes as $personne)
		{
			list($st_valeurs_courantes,$a_params_courants)=$personne->ligne_sql_a_inserer();
			$a_params=$a_params+$a_params_courants;
			$a_valeurs[]=$st_valeurs_courantes;
		}
		$connexionBD->initialise_params($a_params);
		$st_requete = Personne::requete_base().join(',',$a_valeurs);
		$connexionBD->execute_requete($st_requete);
		$a_params=array();
		$a_valeurs=array();
		foreach ($a_liste_actes as $acte)
		{
			list($st_valeurs_courantes,$a_params_courants)=$acte->ligne_sql_a_inserer();
			$a_params=$a_params+$a_params_courants;
			$a_valeurs[]=$st_valeurs_courantes;
		}
		$connexionBD->initialise_params($a_params);
		$st_requete = Acte::requete_base().join(',',$a_valeurs);
		$connexionBD->execute_requete($st_requete);
		$union->sauve();
	}
	$stats_patronyme->sauvePatronyme();	
	$stats_patronyme->sauveTypeActe();		
	$stats_patronyme->sauve();
	$stats_commune->sauve();	
	$connexionBD->execute_requete("UNLOCK TABLES");	
	$this->i_nb_actes = $i_nb_actes;   
	return true;
} 
   
	function nb_actes_charges()
   {
      return $this->i_nb_actes;
   }
   
   function liste_deja_existants()
   {
      return $this->a_deja_existants;
   }
}
?>
