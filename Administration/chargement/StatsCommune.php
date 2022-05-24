<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

class StatsCommune {
  // Statistique divers à compléter
  protected $connexionBD;
  protected $i_idf_commune;
  protected $i_idf_source;
  protected $type_acte;
  protected $a_stats_commune;

  public function __construct($pconnexionBD,$pi_idf_commune,$pi_idf_source) {
       $this->connexionBD = $pconnexionBD;
       $this->i_idf_commune=$pi_idf_commune;
       $this->i_idf_source=$pi_idf_source;
       $this->type_acte = TypeActe::singleton($pconnexionBD);
       $this->a_stats_commune=$this->connexionBD->sql_select_multiple_par_idf("select type_acte.nom,annee_min,annee_max,nb_actes from `stats_commune` join `type_acte` on (stats_commune.idf_type_acte=type_acte.idf) where idf_commune=$pi_idf_commune and idf_source=$pi_idf_source");       
  }
  
  /**
  * Met à jour les date minimale, maximale, le nombre d'actes en fonction de l'annee courante $pi_annee
  *
  * @param string $pi_annee annee courante
  * @return array(date_min, date_max)
  */

  public function compte_acte($pst_type_acte,$pi_annee)
  {
     if (array_key_exists(strval($pst_type_acte),$this->a_stats_commune))
     {
       list($i_annee_min,$i_annee_max,$i_nb_actes) = $this->a_stats_commune[strval($pst_type_acte)];
       //9999 est une année inconnue pour Nimgue
       //Nimègue autorise le caractère ? dans l'année
       if (($pi_annee != 9999) && ($pi_annee != 0))
       {
         if ($pi_annee<$i_annee_min || $i_annee_min==0) $i_annee_min=$pi_annee;
         if ($pi_annee>$i_annee_max || $i_annee_max==9999) $i_annee_max=$pi_annee;
       }
       $i_nb_actes++;
       $this->a_stats_commune[strval($pst_type_acte)] = array($i_annee_min,$i_annee_max,$i_nb_actes);
     }
     else
       // si l'année est 0 ou 9999, on espère qu'il existera au moins un acte avec une année valide dans le fichier pour corriger la fourchette
       $this->a_stats_commune[strval($pst_type_acte)] = array($pi_annee,$pi_annee,1); 
     
  }

  /**
   * Sauve les statistiques dans la base
   */     
  public function sauve()
  {
     $i_nb_stats=$this->connexionBD->execute_requete("delete from `stats_commune` where idf_commune=$this->i_idf_commune and idf_source=$this->i_idf_source");
     foreach ($this->a_stats_commune as $st_type_acte => $a_champs)
     {
        list($i_annee_min,$i_annee_max,$i_nb_actes) = $a_champs;
        $this->connexionBD->execute_requete("insert into `stats_commune` (idf_commune,idf_type_acte,idf_source,annee_min,annee_max,nb_actes) values ($this->i_idf_commune,".$this->type_acte->vers_idf($st_type_acte).",$this->i_idf_source,$i_annee_min,$i_annee_max,$i_nb_actes)");
     }   
  }
  
  /*
   Met à jour les statistiques de la commune en cours suite à une modification
   @param integer $pi_idf_type_acte identifiant du type d'acte
   */
   function maj_stats($pi_idf_type_acte) {
       $st_requete=sprintf("select min(annee), max(annee),count(*) from acte where idf_commune=%d and idf_type_acte=%d and idf_source=%d and annee!=0 and annee!=9999",$this->i_idf_commune,$pi_idf_type_acte,$this->i_idf_source);
        list($i_annnee_min,$i_annee_max,$i_nb_actes)=$this->connexionBD->sql_select_liste($st_requete);
        if (empty($i_annnee_min) && empty($i_annnee_max))
          $st_requete=sprintf("delete from `stats_commune` where idf_commune=%d and idf_type_acte=%d and idf_source=%d",$this->i_idf_commune,$pi_idf_type_acte,$this->i_idf_source);
        else    
          $st_requete=sprintf("update `stats_commune` set annee_min=%d,annee_max=%d,nb_actes=%d where idf_commune=%d and idf_type_acte=%d and idf_source=%d",$i_annnee_min,$i_annee_max,$i_nb_actes,$this->i_idf_commune,$pi_idf_type_acte,$this->i_idf_source);
        $this->connexionBD->execute_requete($st_requete);
   }
}
?>
