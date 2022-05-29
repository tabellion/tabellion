<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

class PaginationTableau {

   protected $connexionBD; 
   protected $pst_nom_script;
   protected $pst_param_numpage;       
   protected $i_nb_total_lignes;
   protected $i_nb_lignes_par_page;
   protected $i_nb_pages;
   protected $i_page_cour;
   protected $i_delta_navig;
   protected $st_requete; 
   protected $a_entete;
   protected $i_nb_select_page;
   
   /**
    * Constructeur
    * param string $pst_nom_script Nom du script qui utilise la pagination
    * param string $pst_param_numpage Paramètre GET du numéro de page
    * param integer $pi_nb_total_lignes Nombre total de lignes
    * param integer $pi_nb_lignes_par_page Nombre de lignes par page
    * param integer $pi_delta_navig Nombre de pages à présenter avant et après la page courante 
    * param array $pa_entete Tableau représentantant l'entête du tableau HTML                              
    */       
   public  function __construct ($pst_nom_script,$pst_param_numpage,$pi_nb_total_lignes,$pi_nb_lignes_par_page,$pi_delta_navig,$pa_entete) {     
     $this->st_nom_script=$pst_nom_script;
     $this->st_param_numpage=$pst_param_numpage;     
     $this->i_nb_total_lignes=$pi_nb_total_lignes;
     $this->i_nb_lignes_par_page=$pi_nb_lignes_par_page; 
     $this->i_nb_pages= ceil($pi_nb_total_lignes/$pi_nb_lignes_par_page);
     $this->i_delta_navig=$pi_delta_navig;
     $this->a_entete=$pa_entete;
     $this->i_nb_select_page=0;
   }
    
   /**
    * Initialise les paramètres liés à la connexion BD   
    * param object $pconnexionBD Connexion BD
    * param integer $pst_requete Requete SQL décrivant le résultat      
    */
   public function init_param_bd($pconnexionBD,$pst_requete) {
     $this->connexionBD=$pconnexionBD;
     $this->st_requete=$pst_requete; 
   }
          
   /**
    * Affiche l'entête de navigation liens
    * L'entête se presente sous la forme d'une liste d'ancres HTML [pagecourante - delta ... pagecourante ... pagecourante + delta]     
    */       
   public function affiche_entete_liens_navigation() {
     $i_deb = 1;
     $i_fin = $this->i_nb_pages;
     print('<div class="text-center">');
     print('<ul class="pagination">');
     if ($i_fin>1)
        print("<li class=\"page-item\"><a href=\"$this->st_nom_script?$this->st_param_numpage=1\" class=\"page-item\">D&eacute;but</a></li> "); 
     if ($i_deb<$i_fin)
     {  
        for ($i=$i_deb;$i<=$i_fin;$i++) {
          if ($i==$this->i_page_cour)
			  print("<li class=\"page-item active\"><span class=\"page-link\">$i<span class=\"sr-only\">(current)</span></span></li>");
          else
			print("<li class=\"page-item\"><a href=\"$this->st_nom_script?$this->st_param_numpage=$i\" class=\"page-item\">$i</a></li> ");  
        }
     }
     if ($i_fin<$this->i_nb_pages)
		print("<li class=\"page-item\"><a href=\"$this->st_nom_script?$this->st_param_numpage=$this->i_nb_pages\" class=\"page-item\">Fin</a></li>");  
     print("</ul>");
     print("</div>");  
   }
   
    public function affiche_entete_liens_navlimite() {
		if( $this->i_nb_pages > 1 )	{
			print('<div class="text-center">');
			print('<ul class="pagination">');
			if( $this->i_nb_pages <= 11 ){
				$i_fin = $this->i_nb_pages;
				for ($i=1;$i<=$i_fin;$i++) {
					if ($i==$this->i_page_cour)
						print("<li class=\"page-item active\"><span class=\"page-link\">$i<span class=\"sr-only\">(current)</span></span></li>");
					else
						print("<li class=\"page-item\"><a href=\"$this->st_nom_script?$this->st_param_numpage=$i\" class=\"page-item\">$i</a></li> ");  
				}
			}
			else {
				print("<li class=\"page-item\"><a href=\"$this->st_nom_script?$this->st_param_numpage=1\" class=\"page-item\">D&eacute;but</a></li> "); 
				if( $this->i_page_cour <=6 ) {
					$i_deb = 1;
					$i_fin = 11;
				}
				else if ( $this->i_page_cour >= $this->i_nb_pages - 5 ) {
					$i_deb = $this->i_nb_pages - 10;
					$i_fin = $this->i_nb_pages;
				}
				else {
					$i_deb = $this->i_page_cour - 5;
					$i_fin = $this->i_page_cour + 5;
				}
				if( $i_fin > $this->i_nb_pages ) {
					$i_fin = $this->i_nb_pages;
				}
				for ($i=$i_deb;$i<=$i_fin;$i++) {
					if ($i==$this->i_page_cour)
						print("<li class=\"page-item active\"><span class=\"page-link\">$i<span class=\"sr-only\">(current)</span></span></li>");
					else
						print("<li class=\"page-item\"><a href=\"$this->st_nom_script?$this->st_param_numpage=$i\" class=\"page-item\">$i</a></li> ");  
				}
				print("<li class=\"page-item\"><a href=\"$this->st_nom_script?$this->st_param_numpage=$this->i_nb_pages\" class=\"page-item\">Fin</a></li> "); 
			}                                                             
			print("</ul>");
			print("</div>");  
		}
		else {
			print('<div class="text-center">&nbsp</div>');
		}
	}
  
   /**
    * Affiche l'entête sous la forme d'une liste déroulante
    * @param string $pst_nom_formulaire nom du formulaire   
    */
    public function affiche_entete_liste_select($pst_nom_formulaire) {
    $this->i_nb_select_page++;    
     if ($this->i_nb_pages>1)
     {
        $st_nom_select = $this->st_param_numpage."_".$this->i_nb_select_page;
        $i_index_choisi = "document.$pst_nom_formulaire.$st_nom_select.selectedIndex";
        print("<div class=\"form-group row col-md-12\">");
		print("<label for=\"$st_nom_select\" class=\"col-form-label col-md-2 col-md-offset-3\">Page:</label>");
		print('<div class="col-md-2">');
		print("<select name=$st_nom_select id=\"$st_nom_select\" onChange=\"document.$pst_nom_formulaire.$this->st_param_numpage.value=document.$pst_nom_formulaire.$st_nom_select.options[$i_index_choisi].value;document.$pst_nom_formulaire.submit();\" class=\"form-control\">");        
        for ($i=1;$i<=$this->i_nb_pages;$i++)
        {
           if ($i==$this->i_page_cour)
              print("<option value=$i selected>$i</option>\n");
           else
              print("<option value=$i>$i</option>\n");
        }
        print("</select>");
		print("</div></div>");
     } 
   }
     
   /**
    * Affiche le contenu du tableau correspondant à $i_nb_lignes_par_page lignes de la page courante . La requete SQL est utilisée
    */       
   public function affiche_tableau_simple_requete_sql() {
      $st_requete = $this->st_requete;
      $i_limite_inf = ($this->i_page_cour-1)*$this->i_nb_lignes_par_page;
      $st_requete .= " limit $i_limite_inf,$this->i_nb_lignes_par_page" ;
      print("<table class=\"table table-bordered table-striped\">");
      print("<thead><tr>");
      foreach ($this->a_entete as $st_cell_entete) {
         print("<th>".cp1252_vers_utf8($st_cell_entete)."</th>");
      }
      print("</tr></thead>\n");
	  print('<tbody>');
      $a_lignes = $this->connexionBD->sql_select_multiple($st_requete);
      $i=0;
      foreach ($a_lignes as $a_ligne) {
         print("<tr>");
         foreach ($a_ligne as $st_champ)
         {
            if ($st_champ!="")
              print("<td class=\"lib_erreur\">".cp1252_vers_utf8($st_champ)."</td>");
            else
              print("<td>&nbsp;</td>");
         }         
         print("</tr>\n");
         $i++;
      }
      print('</tbody>');      
      print("</table>");
      // paramètre pour gérer le numéro de page dans le cas d'un numéro de page envoyé par méthode POST
      print("<input type=hidden name=$this->st_param_numpage value=\"\">"); 
   }   

   /**
    * Affiche le contenu du tableau correspondant à $i_nb_lignes_par_page lignes de la page courante . La requete SQL est utilisée
    */       
   public function affiche_tableau_simple($pa_tableau,$pb_conversion_encodage=true) {
      $i_limite_inf = ($this->i_page_cour-1)*$this->i_nb_lignes_par_page;
      $pa_tableau=array_slice($pa_tableau,$i_limite_inf,$this->i_nb_lignes_par_page);
      print("<table class=\"table table-bordered table-striped\">");
      print("<thead><tr>");
      foreach ($this->a_entete as $st_cell_entete) {
         print("<th>$st_cell_entete</th>");
      }
      print("</tr></thead>\n");
	  print('<tbody>');
      $i=0;
      foreach ($pa_tableau as $a_ligne) {
         print("<tr>");
         foreach ($a_ligne as $st_champ)
         {
            if ($st_champ!="")
			  if ($pb_conversion_encodage)
				print("<td>".cp1252_vers_utf8($st_champ)."</td>");
			  else
				print("<td>$st_champ</td>");  
			else
              print("<td>&nbsp;</td>");   
         }         
         print("</tr>\n");
         $i++;
      }
      print('</tbody>');      
      print("</table>");
      // paramètre pour gérer le numéro de page dans le cas d'un numéro de page envoyé par méthode POST
      print("<input type=hidden name=$this->st_param_numpage value=\"\">"); 
   }   

   /**
    * Affiche le contenu du tableau correspondant à $i_nb_lignes_par_page lignes de la page courante . La requete SQL est utilisée
    */       
   public function affiche_tableau_simple_mev($pa_tableau,$pb_conversion_encodage=true) {
      $i_limite_inf = ($this->i_page_cour-1)*$this->i_nb_lignes_par_page;
      $pa_tableau=array_slice($pa_tableau,$i_limite_inf,$this->i_nb_lignes_par_page);
      print("<table class=\"table table-bordered\">");
      print("<thead><tr>");
      foreach ($this->a_entete as $st_cell_entete) {
         print("<th>$st_cell_entete</th>");
      }
      print("</tr></thead>\n");
	  print('<tbody>');
      $i=0;
      foreach ($pa_tableau as $a_ligne) {
		 $i_mev = array_shift($a_ligne);
		 if($i_mev) {
			print("<tr bgcolor='#FDFBB3'>");			 
		 }
		 else {
			print("<tr>");			 
		 }
         foreach ($a_ligne as $st_champ)
         {
            if ($st_champ!="")
			  if ($pb_conversion_encodage)
				print("<td>".cp1252_vers_utf8($st_champ)."</td>");
			  else
				print("<td>$st_champ</td>");  
			else
              print("<td>&nbsp;</td>");   
         }         
         print("</tr>\n");
         $i++;
      }
      print('</tbody>');      
      print("</table>");
      // paramètre pour gérer le numéro de page dans le cas d'un numéro de page envoyé par méthode POST
      print("<input type=hidden name=$this->st_param_numpage value=\"\">"); 
   }   

 /**
    * Affiche le contenu du tableau correspondant à $i_nb_lignes_par_page lignes de la page courante
	* @param string $pst_nom_script Nom du script
    * @param integer $pi_type_identifiant type d'identifiant utilisé (1: entier (défaut)| 2: chaine)  	
    */       
   public function affiche_tableau_edition($pst_nom_script='',$pi_type_identifiant=1,$pb_conversion_encodage=true) {
      $st_requete = $this->st_requete;
      $i_limite_inf = ($this->i_page_cour-1)*$this->i_nb_lignes_par_page;
      $st_requete .= " limit $i_limite_inf,$this->i_nb_lignes_par_page" ;
      print("<table class=\"table table-bordered table-striped\">");
      print("<thead><tr>");
      foreach ($this->a_entete as $st_cell_entete) {
         print("<th>".cp1252_vers_utf8($st_cell_entete)."</th>");
      }
      print("</tr></thead>\n");
      print('<tbody>');
	  $a_lignes = $this->connexionBD->sql_select_multiple($st_requete);
      $i=0;
      foreach ($a_lignes as $a_ligne) {
         $idf_element = array_shift($a_ligne);
         print("<tr>");
         $st_nom_col1 = preg_replace('/\s/','_',$a_ligne[0]);
         foreach ($a_ligne as $st_nom_element)
         {
            if ($st_nom_element!= '')
			    if ($pb_conversion_encodage)
					print("<td>".cp1252_vers_utf8($st_nom_element)."</td>");
				else
					print("<td>$st_nom_element)</td>");
			else
               print("<td>&nbsp;</td>");   
         }
         switch ($pi_type_identifiant)
         {
			 case 1: print(sprintf("<td><a class=\"btn btn-primary btn-block\" type=button id=\"bouton%d\" href=\"%s?mod=%d\" role=\"button\"><span class=\"glyphicon glyphicon-edit\"></span> Modifier</a></td>",$idf_element,$pst_nom_script,$idf_element));
			 break;
			 case 2: print(sprintf("<td><a class=\"btn btn-primary btn-block\" type=button id=\"bouton%d\" href=\"%s?mod=%s\" role=\"button\"><span class=\"glyphicon glyphicon-edit\"></span> Modifier</a></td>",$idf_element,$pst_nom_script,$idf_element));
			 break;
			 default:
				die("<div class=\"alert alert-danger\">Type d'identifiant $ pi_type_identifiant inconnu</div>");
		 }
		 
         print("<td><div class=\"lib_erreur\"><div class=\"checkbox\"><label><input type=checkbox name=\"supp[]\" id=\"$st_nom_col1\" value=$idf_element class=\"form-check-input\"></label></div></div></td>"); 
         print("</tr>\n");
         $i++;
      }
      print('</tbody>');      
      print("</table>");
      // paramètre pour gérer le numéro de page dans le cas d'un numéro de page envoyé par méthode POST
      print("<input type=hidden name=$this->st_param_numpage value=\"\">"); 
   }   
  /**
    * Affiche le contenu du tableau correspondant à $i_nb_lignes_par_page lignes de la page courante
    * @param integer $pi_type_identifiant type d'identifiant utilisé (1: entier (défaut)| 2: chaine)  petit bouton	
    */       
   public function affiche_tableau_edition_sil($pi_type_identifiant=1) {
      $st_requete = $this->st_requete;
      $i_limite_inf = ($this->i_page_cour-1)*$this->i_nb_lignes_par_page;
      $st_requete .= " limit $i_limite_inf,$this->i_nb_lignes_par_page" ;
      print("<table class=\"table table-bordered table-striped\">");
      print("<thead><tr>");
      foreach ($this->a_entete as $st_cell_entete) {
         print("<th>".$st_cell_entete."</th>");
      }
      print("</tr></thead>\n");
      print('<tbody>');
	  $a_lignes = $this->connexionBD->sql_select_multipleUtf8($st_requete);
      $i=0;
      foreach ($a_lignes as $a_ligne) {
         $idf_element = array_shift($a_ligne);
         print("<tr>");
         $st_nom_col1 = preg_replace('/\s/','_',$a_ligne[0]);
         foreach ($a_ligne as $st_nom_element)
         {
            if ($st_nom_element!= '')
				print("<td>$st_nom_element</td>");
			else
               print("<td>&nbsp;</td>");   
         }

         switch ($pi_type_identifiant)
         {
			 case 1: 
				print(sprintf("<td><a class='btn btn-primary btn-sm btn-block' type='button' id=\"bouton%d\" href=\"%s?mod=%d\" role='button'><span class='glyphicon glyphicon-edit'></span> Modifier</a></td>",
							  $idf_element,basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),$idf_element));
				break;
			 case 2: 
				print(sprintf("<td><a class='btn btn-primary btn-sm btn-block' type='button' id=\"bouton%s\" href=\"%s?mod=%s\" role='button'><span class='glyphicon glyphicon-edit'></span> Modifier</a></td>",
							  $idf_element,basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),$idf_element));
				break;
				break;
			 default:
				die("<div class=\"alert alert-danger\">Type d'identifiant $ pi_type_identifiant inconnu</div>");
		 }
		 
         //print("<td><div class=\"checkbox\"><label><input type=checkbox name=\"supp[]\" id=\"$st_nom_col1\" value=$idf_element class=\"form-check-input\"></label></div></td>"); 
         print("<td><label><input type=checkbox name=\"supp[]\" id=\"$st_nom_col1\" value=$idf_element class=\"form-check-input\"></label></td>"); 
        print("</tr>");
         $i++;
      }
      print('</tbody>');      
      print("</table>");
      // paramètre pour gérer le numéro de page dans le cas d'un numéro de page envoyé par méthode POST
      print("<input type=hidden name=$this->st_param_numpage value=\"\">"); 
   }   
    
  /**
    * Affiche le contenu du tableau correspondant à $i_nb_lignes_par_page lignes de la page courante
    * @param integer $pi_type_identifiant type d'identifiant utilisé (1: entier (défaut)| 2: chaine)  petit bouton	
    */       
   public function affiche_tableau_sil($pi_type_identifiant=1) {
      $st_requete = $this->st_requete;
      $i_limite_inf = ($this->i_page_cour-1)*$this->i_nb_lignes_par_page;
      $st_requete .= " limit $i_limite_inf,$this->i_nb_lignes_par_page" ;
      print("<table class=\"table table-bordered table-striped\">");
      print("<thead><tr>");
      foreach ($this->a_entete as $st_cell_entete) {
         print("<th>".$st_cell_entete."</th>");
      }
      print("</tr></thead>\n");
      print('<tbody>');
	  $a_lignes = $this->connexionBD->sql_select_multipleUtf8($st_requete);
      $i=0;
      foreach ($a_lignes as $a_ligne) {
         $idf_element = array_shift($a_ligne);
         print("<tr>");
         $st_nom_col1 = preg_replace('/\s/','_',$a_ligne[0]);
         foreach ($a_ligne as $st_nom_element)
         {
            if ($st_nom_element!= '')
				print("<td>$st_nom_element</td>");
			else
               print("<td>&nbsp;</td>");   
         }

        print("</tr>");
         $i++;
      }
      print('</tbody>');      
      print("</table>");
      // paramètre pour gérer le numéro de page dans le cas d'un numéro de page envoyé par méthode POST
      print("<input type=hidden name=$this->st_param_numpage value=\"\">"); 
   }   
    
   
   
/**
    * Affiche le contenu du tableau correspondant à $i_nb_lignes_par_page lignes de la page courante  
    */       
   public function affiche_tableau_edition_remplacer() {
      $st_requete = $this->st_requete;
      $i_limite_inf = ($this->i_page_cour-1)*$this->i_nb_lignes_par_page;
      $st_requete .= " limit $i_limite_inf,$this->i_nb_lignes_par_page" ;
      print("<table class=\"table table-bordered table-striped\">");
      print("<thead><tr>");
      foreach ($this->a_entete as $st_cell_entete) {
         print("<th>".cp1252_vers_utf8($st_cell_entete)."</th>");
      }
      print("</tr></thead>\n");
	  print('<tbody>');
      $a_lignes = $this->connexionBD->sql_select_multiple($st_requete);
      $i=0;
      foreach ($a_lignes as $a_ligne)
	  {
         $idf_element = array_shift($a_ligne);
         print("<tr>");
         $st_nom_col1 = preg_replace('/\s/','_',$a_ligne[0]);
         foreach ($a_ligne as $st_nom_element)
         {
            if ($st_nom_element!= '')
               print("<td>".cp1252_vers_utf8($st_nom_element)."</td>");
            else
               print("<td>&nbsp;</td>");   
         }
                  
         print("<td><input type=button id=\"bouton$idf_element\" class=\"btn btn-primary btn-block\" value=\"Modifier\" onClick=\"document.location.href='$this->st_nom_script?mod=$idf_element'\"></td>");
         print("<td><input type=button id=\"boutonR$idf_element\" class=\"btn btn-primary btn-block\" value=Fusionner onClick=\"document.location.href='$this->st_nom_script?remp=$idf_element'\"></td>");
         print("</tr>\n");
         $i++;
      }
      print('</tbody>');       
      print("</table>");
      // paramètre pour gérer le numéro de page dans le cas d'un numéro de page envoyé par méthode POST
      print("<input type=hidden name=$this->st_param_numpage value=\"\">"); 
   }     
   
   /**
    * Met à jour le numéro de page courante   
    * param integer $pi_page_cour Numéro de la page courante 
    */       
   public function init_page_cour($pi_page_cour) {
     if ($pi_page_cour<1)
        $pi_page_cour=1;
     if ($pi_page_cour>$this->i_nb_pages)
        $pi_page_cour=$this->i_nb_pages;
     $this->i_page_cour=$pi_page_cour;       
   }
   
   /*
   * Renvoie le nombre de pages à afficher
   * @return integer nombre de pages   
   */
   public function nb_pages() {
      return $this->i_nb_pages;
   }
   
	/**
	 * Affiche le contenu du tableau correspondant à $i_nb_lignes_par_page lignes de la page courante bouton modifier remplacé par un bouton Sélectionner, par des cases à cocher
	 */       
	public function affiche_tableau_edition_select() {
		$st_requete = $this->st_requete;
		$i_limite_inf = ($this->i_page_cour-1)*$this->i_nb_lignes_par_page;
		$st_requete .= " limit $i_limite_inf,$this->i_nb_lignes_par_page" ;
		print("<table class=\"table table-bordered table-striped\">");
		print("<thead><tr>");
		foreach ($this->a_entete as $st_cell_entete) {
			print("<th>".cp1252_vers_utf8($st_cell_entete)."</th>");
		}
		print("</tr></thead>\n");
		print('<tbody>');
		$a_lignes = $this->connexionBD->sql_select_multiple($st_requete);
		$i=0;
		foreach ($a_lignes as $a_ligne) {
			$idf_element = array_shift($a_ligne);
			print("<tr>");
			$st_nom_col1 = preg_replace('/\s/','_',$a_ligne[0]);
			foreach ($a_ligne as $st_nom_element)
			{
				if ($st_nom_element!= '')
				print("<td>".cp1252_vers_utf8($st_nom_element)."</td>");
				else
				print("<td>&nbsp;</td>");   
			}
			
			print("<td><input type=button class=\"btn btn-primary btn-block\" id=\"bouton$idf_element\" value=Sélectionner onClick=\"document.location.href='$this->st_nom_script?mod=$idf_element'\"></td>");
			print("</tr>\n");
			$i++;
		}
        print('<t/body>');		
		print("</table>");
		// paramètre pour gérer le numéro de page dans le cas d'un numéro de page envoyé par méthode POST
		print("<input type=hidden name=$this->st_param_numpage value=\"\">"); 
	}
  
	/**
	 * Affiche le contenu du tableau correspondant à $i_nb_lignes_par_page lignes de la page courante bouton modifier remplacé par un bouton Sélectionner, par des cases à cocher
     * petit bouton
	 */       
	public function affiche_tableau_edition_select_sil() {
		$st_requete = $this->st_requete;
		$i_limite_inf = ($this->i_page_cour-1)*$this->i_nb_lignes_par_page;
		$st_requete .= " limit $i_limite_inf,$this->i_nb_lignes_par_page" ;
		print("<table class=\"table table-bordered table-striped\">");
		print("<thead><tr>");
		foreach ($this->a_entete as $st_cell_entete) {
			print("<th>".cp1252_vers_utf8($st_cell_entete)."</th>");
		}
		print("</tr></thead>\n");
		print('<tbody>');
		$a_lignes = $this->connexionBD->sql_select_multiple($st_requete);
		$i=0;
		foreach ($a_lignes as $a_ligne) {
			$idf_element = array_shift($a_ligne);
			print("<tr>");
			$st_nom_col1 = preg_replace('/\s/','_',$a_ligne[0]);
			foreach ($a_ligne as $st_nom_element)
			{
				if ($st_nom_element!= '')
				print("<td>".cp1252_vers_utf8($st_nom_element)."</td>");
				else
				print("<td>&nbsp;</td>");   
			}
			
			print("<td><input type=button class=\"btn btn-primary btn-sm btn-block\" id=\"bouton$idf_element\" value=Sélectionner onClick=\"document.location.href='$this->st_nom_script?mod=$idf_element'\"></td>");
			print("</tr>\n");
			$i++;
		}
        print('<t/body>');		
		print("</table>");
		// paramètre pour gérer le numéro de page dans le cas d'un numéro de page envoyé par méthode POST
		print("<input type=hidden name=$this->st_param_numpage value=\"\">"); 
	}
  
  /**
   * Affiche la liste des pages
   *@param string $pst_nom_script nom du script appelant
   * @param integer $total  nombre total de résultats
   * @param integer $per_page nombre de résultats par page
   * @param integer $current_page numéro de la courante
   */
  public function get_pagination($pst_nom_script,$total, $per_page, $current_page = 0) {

  $nb_pages = ceil($total/$per_page);
  $nav = '<div class="text-center"><ul class="pagination">';
  if($current_page > 0){
    $nav .= '<li class="page-item"> <a href="'.$pst_nom_script.'?page=0" aria-label="Premi&eacute;re page"><span aria-hidden="true">&laquo;</span> <span class="sr-only">Premi&eagre;re page </span></a></li>';
    $nav .= '<li class="page-item">  <a href="'.$pst_nom_script.'?page='. ($current_page-1) .'" aria-label="Page pr&eacute;c&eacute;dente"> Page pr&eacute;c&eacute;dente</a></li>';
  }
  if($current_page + 1 < $nb_pages){
    $nav .= '<li class="page-item">  <a href="'.$pst_nom_script.'?page='. ($current_page+1) .'" aria-label="Page suivante"> Page suivante > </a> </li>';
    $nav .= '<li class="page-item"> <a href="'.$pst_nom_script.'?page='. ($nb_pages - 1) .'" aria-label="Derni&egrave;re page"> <span aria-hidden="true">&raquo;</span><span class="sr-only">Derni&egrave;re Page</span></a></li>';
  }
  $nav .= '</ul>';
  $nav .= '<ul class="pagination justify-content-center">'; 
  for($i = 0; $i < $nb_pages; $i++) {
       if($i == $current_page){
           $nav .= '<li class="page-item active"> <span class="page-link">'. ($i+1) .'<span class="sr-only">(current)</span></span></li>';
       } else {
            $nav .= '<li class="page-item"> <a href="'.$pst_nom_script.'?page='. $i .'">' . ($i+1) . '</a></li>';
       }
  }
  $nav .= '</ul></div>';
  return $nav;
}   
	
}
?>