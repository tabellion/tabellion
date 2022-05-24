<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publie par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
class RequeteRecherche {
   protected $connexionBD;
   protected $a_variantes_trouvees;
   protected $a_communes_voisines;
   
   function __autoload($class_name) {
    require_once $class_name . '.php';
   }
   
   public function __construct($pconnexionBD) {
     $this->connexionBD =  $pconnexionBD;
     $this->a_variantes_trouvees=array();
     $this->a_variantes_prenoms_trouvees =array(); 
	 $this->a_patronymes_trouves=array();
     $this->a_communes_voisines=array();     
   }
   
   /**
   * Renvoie la partie droite de l'egalite dans la clause de recherche par nom (Gère le joker* ) 
   * @param string $pst_patronyme : patronyme à chercher 
   * @param string $pst_variantes : variantes à chercher (si non vide)
   * @param integer $pi_num_param : numéro du paramètre 
   */
   public function clause_droite_patronyme($pst_patronyme,$pst_variantes,$pi_num_param) {
      $st_clause = '';
	  $pst_patronyme=utf8_vers_cp1252($pst_patronyme);
      if (($pst_variantes=='') || preg_match('/\%/',$pst_patronyme))
      {      
         if (preg_match('/\%/',$pst_patronyme))
           $st_clause = " like :patro$pi_num_param";
         else
           $st_clause = "=:patro$pi_num_param";
         $this->a_variantes_trouvees = array();
         $this->connexionBD->ajoute_params(array(":patro$pi_num_param"=>$pst_patronyme));
         
      } 
      else
      {
		$a_params_precedents=$this->connexionBD->params();  
		if ($pst_variantes=='oui')
		{
          $this->connexionBD->initialise_params(array(":patro"=>$pst_patronyme));
          $st_requete = "select vp1.patronyme from variantes_patro vp1, variantes_patro vp2 where vp2.patronyme = :patro COLLATE latin1_general_ci and vp1.idf_groupe=vp2.idf_groupe";
          $a_variantes=$this->connexionBD->sql_select($st_requete);
          $this->a_variantes_trouvees = $a_variantes;   
		}
		else
		{
		  // variantes phonetiques
		  $this->connexionBD->initialise_params(array(":patro"=>$pst_patronyme));
          $st_requete = "select p2.libelle from `patronyme` p1 join `patronyme` p2 on (truncate(p1.phonex,7)=truncate(p2.phonex,7)) where p1.libelle=:patro COLLATE latin1_general_ci ";
          $a_variantes=$this->connexionBD->sql_select($st_requete);
          $this->a_variantes_trouvees = $a_variantes;
		}
		$this->connexionBD->initialise_params($a_params_precedents);
          if (count($a_variantes)==0)
          {
            $st_clause = "=:patro$pi_num_param";
            $this->connexionBD->ajoute_params(array(":patro$pi_num_param"=>$pst_patronyme));
          }
          else
          {
            $i=0;
            $a_params_variantes= array();
            foreach($a_variantes as $st_variante)
            {
              $this->connexionBD->ajoute_params(array(":variante$pi_num_param$i"=>$st_variante));
              $a_params_variantes[]=":variante$pi_num_param$i";
              $i++; 
            }
            $st_variantes=join(',',$a_params_variantes);
            $st_clause = "in ($st_variantes) ";
          }
      }
      return $st_clause;
   }
   
    /**
   * Renvoie la liste d'identifiants de patronymes correspondant à la recherche du patronyme donné (Gère le joker* ) 
   * @param string $pst_patronyme : patronyme à chercher 
   * @param string $pst_variantes : variantes à chercher (si non vide)
   * @param integer $pi_num_param : numéro du paramètre
   */
   public function liste_idf_patronymes($pst_patronyme,$pst_variantes,$pi_num_param) {
	  $pst_patronyme=utf8_vers_cp1252($pst_patronyme);
      $a_patronymes_trouves = array();	  
      if (($pst_variantes=='') || preg_match('/\%/',$pst_patronyme))
      {      
         if (preg_match('/\%/',$pst_patronyme))
           $st_clause = " like :patro$pi_num_param";
         else
           $st_clause = "=:patro$pi_num_param";
         $st_requete = "select idf,libelle from patronyme where libelle $st_clause";
         $this->connexionBD->ajoute_params(array(":patro$pi_num_param"=>$pst_patronyme));
		 $a_patronymes_trouves=$this->connexionBD->liste_valeur_par_clef($st_requete);
		 $this->a_variantes_trouvees = array();
      } 
      else
      {
        $a_params_precedents=$this->connexionBD->params();
        $this->connexionBD->initialise_params(array(":patro"=>$pst_patronyme));
        $st_requete = "select pat.idf,pat.libelle from variantes_patro vp1 join patronyme pat on (vp1.patronyme=pat.libelle), variantes_patro vp2 where vp2.patronyme = :patro COLLATE latin1_general_ci and vp1.idf_groupe=vp2.idf_groupe";
		$a_patronymes_trouves=$this->connexionBD->liste_valeur_par_clef($st_requete);
        $this->a_variantes_trouvees=array_values($a_patronymes_trouves);
        $this->connexionBD->initialise_params($a_params_precedents);
      }
	  return $a_patronymes_trouves;
   }
   
   /**
   * Renvoie la partie droite de l'egalite dans la clause de recherche par prénom (Gère le joker* )
   * @param string $pst_prenom : prénom à chercher 
   * @param string $pst_variantes : variantes à chercher (si non vide)
   * @param integer $pi_num_param : numéro du paramètre
   */
   function clause_droite_prenom($pst_prenom,$pst_variantes,$pi_num_param)
   {
     $st_clause = '';
     if (!empty($pst_prenom))
     {  
        $pst_prenom=utf8_vers_cp1252($pst_prenom);
        if (($pst_variantes=='') || preg_match('/\%/',$pst_prenom))
        { 
          if (preg_match('/\%/',$pst_prenom))
             $st_clause = "like :prenom$pi_num_param collate latin1_german1_ci";
          else
             $st_clause = "= :prenom$pi_num_param collate latin1_german1_ci"; 
          $this->a_variantes_prenoms_trouvees =array(); 
          $this->connexionBD->ajoute_params(array(":prenom$pi_num_param"=>$pst_prenom));
        }
        else
        { 
           $a_params_precedents=$this->connexionBD->params();
           $pst_prenom=ucfirst(strtolower(trim($pst_prenom))); 
           $this->connexionBD->initialise_params(array(":prn"=>$pst_prenom));
           $st_requete = "select vp1$pi_num_param.libelle from variantes_prenom vp1$pi_num_param, variantes_prenom vp2$pi_num_param where vp2$pi_num_param.libelle = :prn COLLATE latin1_general_cs and vp1$pi_num_param.idf_groupe=vp2$pi_num_param.idf_groupe";
           
           $a_variantes=$this->connexionBD->sql_select($st_requete);
           $this->connexionBD->initialise_params($a_params_precedents);
           if (count($a_variantes)==0)
           {
              $st_clause = "=:prn$pi_num_param collate latin1_german1_ci";
              $this->connexionBD->ajoute_params(array(":prn$pi_num_param"=>$pst_prenom));
           }
           else
           {
              $i=0;
              $a_params_variantes= array();
              foreach($a_variantes as $st_variante)
              {
                 $this->connexionBD->ajoute_params(array(":variante_prn$pi_num_param$i"=>$st_variante));
                 $a_params_variantes[]=":variante_prn$pi_num_param$i";
                 $i++; 
              }
              $st_variantes=join(',',$a_params_variantes);
              $st_clause = "in ($st_variantes) ";
           }
           $this->a_variantes_prenoms_trouvees = $a_variantes;
        }
     }
     return $st_clause;
   }
   
   /**
   * Renvoie la partie droite de l'egalite dans la clause de recherche de commune 
   * @param integer $pi_idf_commune : identifiant de la commune de recherche     
   * @param integer $pi_rayon : rayon de recherche en km
   * @param string $pst_paroisses_rattachees : recherche dans les paroisses de même code insee ('oui'|'')   
   */   
   function clause_droite_commune($pi_idf_commune,$pi_rayon,$pst_paroisses_rattachees)
   {

     if ($pi_rayon!='' && $pi_idf_commune!=0)
     {
             $a_params_precedents=$this->connexionBD->params();
        $this->a_communes_voisines= $this->connexionBD->liste_valeur_par_clef("select tk.idf_commune2,ca.nom from `tableau_kilometrique` tk join `commune_acte` ca on (tk.idf_commune2=ca.idf) where tk.distance <=$pi_rayon and tk.idf_commune1=$pi_idf_commune order by nom");
         $a_champs = array_keys($this->a_communes_voisines);
         $a_champs[] = $pi_idf_commune;
         $this->connexionBD->initialise_params($a_params_precedents);
         return "in (".join(',',$a_champs).")";   
    }
    else 
      if ($pi_idf_commune !=0)
      {
         if ($pst_paroisses_rattachees=='oui')
         {
            $a_params_precedents=$this->connexionBD->params();
            $this->a_communes_voisines= $this->connexionBD->liste_valeur_par_clef("select ca1.idf,ca1.nom from commune_acte ca1 join commune_acte ca2 on (ca1.code_insee=ca2.code_insee) where ca2.idf=$pi_idf_commune");
            $a_champs = array_keys($this->a_communes_voisines);
            $a_champs[] = $pi_idf_commune;
            $this->connexionBD->initialise_params($a_params_precedents);
            return "in (".join(',',$a_champs).")";
         }
         else
            return "=$pi_idf_commune";
      }
      
   }
   
   public function variantes_trouvees() {
     return $this->a_variantes_trouvees;
   }
   
   public function variantes_prenoms() {
     return $this->a_variantes_prenoms_trouvees;
   }
   
   public function communes_voisines() {
     return $this->a_communes_voisines;
   }
   

}

?>
