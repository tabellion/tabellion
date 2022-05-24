<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

class ModificationPersonne extends Personne
 {
    function __autoload($class_name)
    {
         require_once $class_name . '.php';
         } 
    protected $connexionBD;
     protected $compteurPersonne;
     protected $communePersonne;
     protected $profession;
     protected $i_idf;
     protected $i_idf_modification_acte;
     protected $i_idf_type_presence;
     protected $c_sexe;
     protected $st_patronyme;
     protected $st_prenom;
     protected $st_surnom;
     protected $st_origine;
     protected $st_residence;
     protected $st_date_naissance;
     protected $st_age;
     protected $st_profession;
     protected $st_commentaire;
     protected $i_idf_pere;
     protected $i_idf_mere;
     protected $a_filtres_parametres;
    
     public function __construct($pconnexionBD, $pi_idf_modification_acte)
    {
         $this -> connexionBD = $pconnexionBD;
         $this -> i_idf_modification_acte = $pi_idf_modification_acte;
         $this -> c_sexe = null;
         $this -> st_patronyme = null;
         $this -> st_prenom = null;
         $this -> i_idf_type_presence = null;
         $this -> st_residence = null;
         $this -> st_origine = null;
         $this -> st_profession = null;
         $this -> a_filtres_parametres = array();
         } 
    
    public function setModificationActe($pi_idf_modification_acte)
    
    {
         $this -> i_idf_modification_acte = $pi_idf_modification_acte;
         } 
    
    /**
     * Charge la personne à partir de la BD
     * 
     * @param integer $pi_idf_personne identifiant de la personne
     */
    public function charge($pi_idf_modification)
    
    {
         $st_requete = "select idf_modification_acte,idf_type_presence,sexe,patronyme,prenom,surnom,origine,residence,date_naissance,age,profession,commentaires from modification_personne where idf=$pi_idf_modification";
         list($i_idf_modification_acte, $i_idf_type_presence, $c_sexe, $st_patronyme, $st_prenom, $st_surnom, $st_origine, $st_residence, $st_dnais, $st_age, $st_profession, $st_commentaire) = $this -> connexionBD -> sql_select_liste($st_requete);
         $this -> i_idf = $pi_idf_modification;
         $this -> i_idf_modification_acte = $i_idf_modification_acte;
         $this -> i_idf_type_presence = $i_idf_type_presence;
         $this -> c_sexe = $c_sexe;
         $this -> st_patronyme = $st_patronyme;
         $this -> st_prenom = $st_prenom;
         $this -> st_surnom = $st_surnom;
         $this -> st_origine = $st_origine;
         $this -> st_residence = $st_residence;
         $this -> st_date_naissance = str_replace(' ', '', $st_dnais);
         $this -> st_age = $st_age;
         $this -> st_profession = $st_profession;
         $this -> st_commentaire = $st_commentaire;
         } 
    
    /**
     * Cree une nouvelle personne dans la base de données
     * 
     * @return integer identifiant de la personne crée ou null si vide
     */
    public function cree()
    
    {
         $i_idf = $this -> i_idf;
         $i_idf_acte = $this -> i_idf_acte;
         $i_idf_modification_acte = $this -> i_idf_modification_acte;
         $i_idf_type_presence = $this -> i_idf_type_presence;
         $c_sexe = $this -> c_sexe;
         $st_patronyme = $this -> st_patronyme;
         $st_prenom = $this -> st_prenom;
         if (empty($this -> st_patronyme) && empty($this -> st_prenom))
             return null;
         $st_surnom = $this -> st_surnom;
         $st_date_naissance = $this -> st_dnais;
         $st_age = $this -> st_age;
         $st_profession = $this -> st_profession;
         $st_origine = $this -> st_origine;
         $st_residence = $this -> st_residence;
         $st_commentaire = $this -> st_commentaire;
         $this -> connexionBD -> initialise_params(array(':idf_modification_acte' => $i_idf_modification_acte, ':idf_type_presence' => $i_idf_type_presence, ':sexe' => $c_sexe, ':patronyme' => $st_patronyme, ':prenom' => $st_prenom, ':surnom' => $st_surnom, ':origine' => $st_origine, ':residence' => $st_residence, ':date_naissance' => $st_date_naissance, ':age' => $st_age, ':profession' => $st_profession, ':commentaire' => $st_commentaire));
         $st_requete = "insert into modification_personne(idf_modification_acte,idf_type_presence,sexe,patronyme,prenom,surnom,origine,residence,date_naissance,age,profession,commentaires) values(:idf_modification_acte,:idf_type_presence,:sexe,:patronyme,:prenom,:surnom,:origine,:residence,:date_naissance,:age,:profession,:commentaire)";
         $this -> connexionBD -> execute_requete($st_requete);
         return $this -> connexionBD -> dernier_idf_insere();
         } 
    
    /**
     * Modifie la personne dans la base de données
     * 
     * @return integer identifiant de la personne crée ou null si vide
     */
    public function modifie()
    
    {
         $i_idf = $this -> i_idf;
         $i_idf_acte = $this -> i_idf_acte;
         $i_idf_type_presence = $this -> i_idf_type_presence;
         $c_sexe = $this -> c_sexe;
         $st_patronyme = $this -> st_patronyme;
         $st_prenom = $this -> st_prenom;
         if (empty($this -> st_patronyme) && empty($this -> st_prenom))
             return null;
         $st_surnom = $this -> st_surnom;
         if (!empty($this -> st_profession))
             {
            $this -> profession -> ajoute($this -> st_profession);
             $this -> profession -> sauve();
             $i_idf_profession = $this -> profession -> vers_idf($this -> st_profession);
             } 
        else
             $i_idf_profession = 0;
         if (!empty($this -> st_origine))
             {
            $this -> communePersonne -> ajoute($this -> st_origine);
             $this -> communePersonne -> sauve();
             $i_idf_origine = $this -> communePersonne -> vers_idf($this -> st_origine);
             } 
        else
             $i_idf_origine = 0;
         if (!empty($this -> st_residence))
             {
            $this -> communePersonne -> ajoute($this -> st_residence);
             $this -> communePersonne -> sauve();
             $i_idf_residence = $this -> communePersonne -> vers_idf($this -> st_residence);
             } 
        else
             $i_idf_residence = 0;
         $st_date_naissance = $this -> st_dnais;
         $st_age = $this -> st_age;
         $st_commentaire = $this -> st_commentaire;
         $i_est_decede = $this -> i_est_decede;
         $i_idf_pere = $this -> i_idf_pere;
         $i_idf_mere = $this -> i_idf_mere;
         $this -> connexionBD -> initialise_params(array(':idf_acte' => $i_idf_acte, ':idf_type_presence' => $i_idf_type_presence, ':sexe' => $c_sexe, ':patronyme' => $st_patronyme, ':prenom' => $st_prenom, ':surnom' => $st_surnom, ':idf_origine' => $i_idf_origine, ':idf_residence' => $idf_residence, ':date_naissance' => $st_date_naissance, ':age' => $st_age, ':idf_profession' => $i_idf_profession, ':commentaire' => $st_commentaire, ':est_decede' => $i_est_decede, ':idf_pere' => $i_idf_pere, ':idf_mere' => $i_idf_mere, ':idf' => $i_idf));
         $st_requete = "update personne set idf_acte=:idf_acte,idf_type_presence=:idf_type_presence,sexe=:sexe,patronyme=:patronyme,prenom=:prenom,surnom=:surnom,idf_origine=:idf_origine,idf_residence=:idf_residence,date_naissance=:date_naissance,age=:age,idf_profession=:idf_profession,commentaires=:commentaire,est_decede=:est_decede,idf_pere=:idf_pere,idf_mere=:idf_mere where idf=:idf";
         $this -> connexionBD -> execute_requete($st_requete);
         return $i_idf;
         } 
    
    /**
     * Initialise la personne depuis une formulaire post
     * 
     * @param integer $pi_idf_acte identifiant de l'acte
     * @param integer $pi_pi_idf_type_presence type de présence
     */
    public function initialise_depuis_formulaire($pi_idf_acte, $pi_pi_idf_type_presence)
    
    {
        $this -> i_idf_acte = $pi_idf_acte;
        $this -> i_idf_type_presence = $pi_pi_idf_type_presence;
        $i_num_parametre = $this -> i_num_param;
        if (isset($_POST["sexe$i_num_parametre"])) $this -> c_sexe = substr(trim($_POST["sexe$i_num_parametre"]), 0, 1);
        $this -> st_patronyme = isset($_POST["patro$i_num_parametre"])?substr(trim($_POST["patro$i_num_parametre"]), 0, 30):'';
        $this -> st_prenom = isset($_POST["prn$i_num_parametre"])?substr(trim($_POST["prn$i_num_parametre"]), 0, 30):'';
        $this -> st_surnom = isset($_POST["surnom$i_num_parametre"])?substr(trim($_POST["surnom$i_num_parametre"]), 0, 30):'';
        $this -> st_origine = isset($_POST["orig$i_num_parametre"])?substr(trim($_POST["orig$i_num_parametre"]), 0, 50):'';
        $this -> st_residence = isset($_POST["residence$i_num_parametre"])?substr(trim($_POST["residence$i_num_parametre"]), 0, 50):'';
        $this -> st_dnais = isset($_POST["dnais$i_num_parametre"])?substr(trim($_POST["dnais$i_num_parametre"]), 0, 10):'';
        $this -> st_age = !empty($_POST["age$i_num_parametre"])? substr(trim($_POST["age$i_num_parametre"]), 0, 15):'';
        $this -> st_profession = isset($_POST["prof$i_num_parametre"])?substr(trim($_POST["prof$i_num_parametre"]), 0, 35):'';
        $this -> st_commentaire = isset($_POST["cmt$i_num_parametre"])?substr(trim($_POST["cmt$i_num_parametre"]), 0, 70):'';
        $this -> st_patronyme =self::utf8_vers_cp1252($this -> st_patronyme);
		$this -> st_patronyme = self :: patronyme_propre( $this -> st_patronyme );
		$this -> st_prenom =self::utf8_vers_cp1252($this -> st_prenom);
        $this -> st_surnom = self :: prenom_propre($this -> st_surnom);
		$this -> st_origine = self :: utf8_vers_cp1252($this -> st_origine);
		$this -> st_residence = self :: utf8_vers_cp1252($this -> st_residence);
		$this -> st_profession = self :: utf8_vers_cp1252($this -> st_profession);
        // met à jour le champ est_decede en même temps que le commentaire
		$this -> st_commentaire =self::utf8_vers_cp1252($this -> st_commentaire);
        $this -> st_commentaire = self :: commentaire_propre($this -> st_commentaire);
         if (empty($this -> st_patronyme) && (!empty($this -> st_prenom) || !empty($this -> st_commentaire)))
             $this -> st_patronyme = LIB_MANQUANT;
         } 
    
    /**
     * Renvoie un formulaire HTML d'édition d'une personne
     * 
     * @param integer $pi_idf_type_acte identifiant du type d'acte
     * @param string $pst_commune commune de l'acte
     * @param integer $pi_idf_patro_intv identifiant jquery de l'intervenant (pour recopie du patronyme vers les parents)
     */
    public function formulaire_personne ($pi_idf_type_acte, $pst_commune, $pi_idf_patro_intv)
    
    {
         global $ga_sexe, $ga_mois_revolutionnaires, $ga_annees_revolutionnaires, $ga_mois_revolutionnaires_nimegue;
         $i_num_parametre = $this -> i_num_param;
         $st_chaine = '';
         switch ($this -> i_idf_type_presence)
         {
        case IDF_PRESENCE_INTV:
             switch ($pi_idf_type_acte)
             {
            case IDF_NAISSANCE:
                 $st_chaine .= "<tr>";
                 $st_chaine .= sprintf("<th>Patronyme</th><td class=\"lib_erreur\"><input type=text name=\"patro$i_num_parametre\" id=\"patro$i_num_parametre\" value=\"%s\" maxlength=30 class=\"form-control text-uppercase form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_patronyme));
                 $st_chaine .= sprintf("<th>Pr&eacute;nom</th><td><input type=text id=\"prn$i_num_parametre\" name=\"prn$i_num_parametre\" value=\"%s\" maxlength=35 class=\"form-control text-capitalize form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_prenom));
                 $st_chaine .= "<th>Sexe</th><td><select name=sexe$i_num_parametre class=\"form-control\">";
                 $st_chaine .= chaine_select_options($this -> c_sexe, $ga_sexe);
                 $st_chaine .= "</select></td>";
                 $st_chaine .= sprintf("<th>Commentaires</th><td><input type=text id=\"cmt$i_num_parametre\" name=\"cmt$i_num_parametre\" value=\"%s\" maxlength=70 class=\"form-control form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_commentaire));
                 $st_chaine .= "</tr>\n";
                 $this -> a_filtres_parametres["patro$i_num_parametre"] = array(array("required", "true", "Le patronyme est obligatoire"));
                 $this -> a_parametres_completion_auto["patro$i_num_parametre"] = array('patronyme.php', 3);
                 break;
             default:
                /**
                 * la structure de personne est la même pour ces 3 types d'acte
                 */
                 $st_chaine .= "<tr>";
                 $st_chaine .= sprintf("<th>Patronyme</th><td class=\"lib_erreur\"><input type=text name=\"patro$i_num_parametre\" id=\"patro$i_num_parametre\" value=\"%s\" maxlength=30 class=\"form-control text-uppercase form-control-xs \"></td>", self::cp1252_vers_utf8($this -> st_patronyme));
                
                 $st_chaine .= sprintf("<th>Pr&eacute;nom</th><td><input type=text name=\"prn$i_num_parametre\" id=\"prn$i_num_parametre\"  value=\"%s\" maxlength=35 class=\"form-control text-capitalize form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_prenom));
                
                 $st_chaine .= sprintf("<th>Profession</th><td><input type=text name=\"prof$i_num_parametre\" id=\"prof$i_num_parametre\" value=\"%s\" maxlength=30 class=\"form-control form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_profession));
                 $st_chaine_deces = ($pi_idf_type_acte == IDF_DECES) ? "<button type=\"button\" class=\"maj_deces btn btn-primary\" data-cible=\"#cmt$i_num_parametre\">&dagger;</button>" : '';
                 $st_chaine .= sprintf("<th>Commentaires</th><td><input type=text id=\"cmt$i_num_parametre\" name=\"cmt$i_num_parametre\" value=\"%s\" maxlength=70 class=\"form-control form-control-xs\">%s</td>", self::cp1252_vers_utf8($this -> st_commentaire), $st_chaine_deces);
                 $st_chaine .= "</tr><tr>";
                 $st_chaine .= sprintf("<th><a class=\"recopie_commune btn btn-info btn-xs\" data-source=\"".self::cp1252_vers_utf8($pst_commune)."\" data-cible=\"#orig$i_num_parametre\" ><span class=\"glyphicon glyphicon-copy\"></span> Lieu<br>d'origine</a></th><td><input type=text name=\"orig$i_num_parametre\"  id=\"orig$i_num_parametre\"  value=\"%s\" maxlength=50 class=\"form-control\">", self::cp1252_vers_utf8($this -> st_origine));
                
                 $st_chaine .= "</td>";
                 $st_chaine .= $pi_idf_type_acte == IDF_MARIAGE ? "<th>Sexe</th><td><select name=sexe$i_num_parametre disabled>" : "<th>Sexe</th><td><select name=sexe$i_num_parametre class=\"form-control form-control-xs\">";
                 $st_chaine .= chaine_select_options($this -> c_sexe, $ga_sexe);
                 $st_chaine .= "</select></td>";
                 $st_chaine .= sprintf("<th>Age</th><td class=\"lib_erreur\"><input type=text name=\"age$i_num_parametre\" id=\"age$i_num_parametre\" value=\"%s\" maxlength=15 class=\"form-control form-control-xs\"></td>", $this -> st_age);
                 $st_chaine .= "<th>Date °</th><td class=\"lib_erreur\">";
                 $i_jour_rep = null;
                 $i_mois_rep = null;
                 $i_annee_rep = null;
                 if (!empty($this -> st_dnais))
                     {
                    list($i_jour_rep, $st_mois_rep, $st_annee_rep) = explode('/', $this -> st_dnais, 3);
                     $a_mois_rep_nim_vers_entier = array_flip($ga_mois_revolutionnaires_nimegue);
                     $i_mois_rep = array_key_exists(strtolower($st_mois_rep), $a_mois_rep_nim_vers_entier) ? $a_mois_rep_nim_vers_entier[strtolower($st_mois_rep)]: null;
                     $i_annee_rep = (int) $st_annee_rep;
                     }
                $st_chaine_date_rep = '<div class="row form-group">';
				$st_chaine_date_rep .= '<div class="col-xs-2">';					 
                $st_chaine_date_rep .= "<input type=\"text\" name=\"jour_rep\" id=\"jour_rep$i_num_parametre\" size=\"2\" maxlength=\"2\" value=\"$i_jour_rep\" class=\"form-control\">";
				 $st_chaine_date_rep .= '</div>';
		         $st_chaine_date_rep .= '<div class="col-xs-4">';
                 $st_chaine_date_rep .= " <select name=\"mois_rep\" id=\"mois_rep$i_num_parametre\" class=\"form-control\">";
                 $st_chaine_date_rep .= '<option value=""></option>';
                 $st_chaine_date_rep .= chaine_select_options($i_mois_rep, $ga_mois_revolutionnaires,false);
                 $st_chaine_date_rep .= '</select>';
				 $st_chaine_date_rep .= '</div>';
		         $st_chaine_date_rep .= '<div class="col-xs-2">';
                 $st_chaine_date_rep .= " <select name=\"annee_rep\" id=\"annee_rep$i_num_parametre\" class=\"form-control\">";
                 $st_chaine_date_rep .= '<option value=""></option>';
                 $st_chaine_date_rep .= chaine_select_options($i_annee_rep, $ga_annees_revolutionnaires,false);
                 $st_chaine_date_rep .= '</select>';
				 $st_chaine_date_rep .= '</div>';
                 $st_chaine_date_rep .= "<input type=\"button\" class=\"btn btn-primary maj_date_rep\" data-jour_rep=\"#jour_rep$i_num_parametre\" data-mois_rep=\"#mois_rep$i_num_parametre\" data-annee_rep=\"#annee_rep$i_num_parametre\" data-date_greg=\"#dnais$i_num_parametre\" data-date_rep=\"\" data-cmt=\"#cmt$i_num_parametre\" data-id_fenetre=\"#popup_dnais$i_num_parametre\" value=\"Maj date naissance\">";
                 $st_chaine_date_rep .= '</div>';
				 // Contenu du popup
                $st_chaine .= sprintf("<div class=\"popup_date_rep\" id=\"popup_dnais%d\" title=\"Fenetre\">%s</div>", $i_num_parametre, $st_chaine_date_rep);
                 $st_chaine .= sprintf("<div class=\"btn-group-vertical\"><input type=text name=\"dnais%d\" id=\"dnais%d\" value=\"%s\" maxlength=10 class=\"form-control form-control-xs\">", $i_num_parametre, $i_num_parametre, $this -> st_date_naissance);
                 // Bouton d'ouverture du popup
                $st_chaine .= sprintf("<button type=\"button\" class=\"ouvre_popup btn btn-primary btn-xs\" data-id_fenetre=\"#popup_dnais%d\"><span class=\"glyphicon glyphicon-calendar\"></span> Saisir une date r&eacute;publicaine</button>", $i_num_parametre);
                 $st_chaine .= "</div></td></tr>\n";
                 $this -> a_filtres_parametres["patro$i_num_parametre"] = array(array("required", "true", "Le patronyme est obligatoire"));
                 if ($pi_idf_type_acte != IDF_DECES)
                     $this -> a_filtres_parametres["age$i_num_parametre"] = array(array("number", "true", "L'âge doit être un entier"));
                 $this -> a_filtres_parametres["dnais$i_num_parametre"] = array(array("dateITA", "true", "La date de naissance est de la forme JJ/MM/AAAA"));
                 $this -> a_parametres_completion_auto["patro$i_num_parametre"] = array('patronyme.php', 3);
                 $this -> a_parametres_completion_auto["prof$i_num_parametre"] = array('profession.php', 4);
                 $this -> a_parametres_completion_auto["orig$i_num_parametre"] = array('commune_acte_saisie.php', 3);
                 } 
            break;
			case IDF_PRESENCE_PERE:
             case IDF_PRESENCE_MERE:
             case IDF_PRESENCE_EXCJT:
                 switch ($this -> i_idf_type_presence)
                 {
                case IDF_PRESENCE_PERE;
                     $st_lib = 'p&egrave;re';
                     break;
                 case IDF_PRESENCE_MERE;
                     $st_lib = 'm&egrave;re';
                     break;
                 case IDF_PRESENCE_EXCJT:
                     $st_lib = 'Ancien Conjoint';
                     break;
                     } 
                $st_chaine .= "<tr>";
                 $st_chaine .= sprintf("<th><a class=\"recopie_patro btn btn-info btn-xs\" data-source=\"$pi_idf_patro_intv\" data-cible=\"#patro$i_num_parametre\"><span class=\"glyphicon glyphicon-copy\"></span> Patronyme<br>%s</a></th><td><input type=text name=\"patro$i_num_parametre\" id=\"patro$i_num_parametre\" value=\"%s\"  maxlength=30 class=\"form-control text-uppercase form-control-xs\"></td>", $st_lib, self::cp1252_vers_utf8($this -> st_patronyme));
                 $st_chaine .= sprintf("<th>Pr&eacute;nom</th><td><input type=text name=\"prn$i_num_parametre\" id=\"prn$i_num_parametre\" value=\"%s\" maxlength=35 class=\"form-control text-capitalize form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_prenom));
                 $st_chaine .= sprintf("<th>Profession</th><td><input type=text name=\"prof$i_num_parametre\" id=\"prof$i_num_parametre\" value=\"%s\" maxlength=30 class=\"form-control form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_profession));
                 $st_chaine .= sprintf("<th>Commentaires</th><td><div class=\"input-group\"><label for=\"cmt$i_num_parametre\" class=\"sr-only\">Commentaires</label><input type=text id=\"cmt$i_num_parametre\" name=\"cmt$i_num_parametre\" value=\"%s\" maxlength=70 class=\"form-control form-control-xs\"><span class=\"input-group-btn\"><button type=button class=\"maj_deces btn btn-primary\" data-cible=\"#cmt$i_num_parametre\">&dagger;</button></span></div></td>", self::cp1252_vers_utf8($this -> st_commentaire));
                 $st_chaine .= "</tr>\n";
                 $this -> a_parametres_completion_auto["patro$i_num_parametre"] = array('patronyme.php', 3);
                 $this -> a_parametres_completion_auto["prof$i_num_parametre"] = array('profession.php', 4);
                 break;
             case IDF_PRESENCE_PARRAIN:
                 case IDF_PRESENCE_MARRAINE:
                 case IDF_PRESENCE_TEMOIN:
                    /**
                     * la structure de personne est la même pour ces 3 types de présence
                     */
                     switch ($this -> i_idf_type_presence)
                     {
                    case IDF_PRESENCE_PARRAIN:
                         $st_lib = 'Parrain/T&eacute;moin';
                         break;
                     case IDF_PRESENCE_MARRAINE:
                         $st_lib = 'Marraine/T&eacute;moin';
                         break;
                     case IDF_PRESENCE_TEMOIN:
                         $st_lib = 'T&eacute;moin';
                         break;
                     default:
                         $st_lib = '';
                         } 
                    $st_chaine .= "<tr>";
                     $st_chaine .= sprintf("<th>Patronyme %s</th><td><input type=text name=\"patro$i_num_parametre\" id=\"patro$i_num_parametre\" value=\"%s\"  maxlength=30 class=\"form-control text-uppercase form-control-xs \"></td>", $st_lib, self::cp1252_vers_utf8($this -> st_patronyme));
                     $st_chaine .= sprintf("<th>Pr&eacute;nom</th><td><input type=text name=\"prn$i_num_parametre\" id=\"prn$i_num_parametre\" value=\"%s\" maxlength=35 class=\"form-control text-capitalize form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_prenom));
                     $st_chaine .= sprintf("<th>Commentaires</th><td colspan=5><div class=\"input-group\"><input type=text id=\"cmt$i_num_parametre\" name=\"cmt$i_num_parametre\" value=\"%s\" size=70 maxlength=70 class=\"form-control form-control-xs\"><span class=\"input-group-btn\"><button type=button class=\"maj_deces btn btn-primary\" data-cible=\"#cmt$i_num_parametre\">&dagger;</button></span></div></td>", self::cp1252_vers_utf8($this -> st_commentaire));
                     $st_chaine .= "</tr>\n";
                     $this -> a_parametres_completion_auto["patro$i_num_parametre"] = array('patronyme.php', 3);
                     } 
                return $st_chaine;
                 } 
            
            /**
             * Renvoie la personne au format Nimègue V3
             * 
             * @param integer $pi_idf_type_acte identifiant du type d'acte
             * @return array tableau des colonnes
             */
            
            public function colonnes_nimv3($pi_idf_type_acte)
            
            {
             switch ($this -> i_idf_type_presence)
             {
            case IDF_PRESENCE_INTV:
                 switch ($pi_idf_type_acte)
                 {
                case IDF_NAISSANCE:
                     return array($this -> st_patronyme, $this -> st_prenom, $this -> c_sexe, $this -> st_commentaire);
                     break;
                 case IDF_MARIAGE:
                     return array($this -> st_patronyme, $this -> st_prenom, $this -> st_origine, $this -> st_date_naissance, $this -> st_age, $this -> st_commentaire, $this -> st_profession);
                     break;
                 case IDF_DECES:
                     return array($this -> st_patronyme, $this -> st_prenom, $this -> st_origine, $this -> st_date_naissance, $this -> c_sexe, $this -> st_age, $this -> st_commentaire, $this -> st_profession);
                     default:
                     // acte divers
                    return array($this -> st_patronyme, $this -> st_prenom, $this -> c_sexe, $this -> st_origine, $this -> st_date_naissance, $this -> st_age, $this -> st_commentaire, $this -> st_profession);
                     } 
                break;
             case IDF_PRESENCE_PERE:
                 case IDF_PRESENCE_MERE:
                     return array($this -> st_patronyme, $this -> st_prenom, $this -> st_commentaire, $this -> st_profession);
                     break;
                 case IDF_PRESENCE_EXCJT:
                     switch ($pi_idf_type_acte)
                     {
                    case IDF_DECES:
                         return array($this -> st_patronyme, $this -> st_prenom, $this -> st_commentaire, $this -> st_profession);
                         break;
                     default:
                         return array($this -> st_patronyme, $this -> st_prenom, $this -> st_commentaire);
                         } 
                    break;
                 case IDF_PRESENCE_PARRAIN:
                     case IDF_PRESENCE_MARRAINE:
                     case IDF_PRESENCE_TEMOIN:
                         return array($this -> st_patronyme, $this -> st_prenom, $this -> st_commentaire);
                         break;
                     default:
                         return array();
                         } 
                    } 
                
                /**
                 * Renvoie si la personne contient des informations supplémentaires
                 * Une demande n'est possible que si un acte contient au moins un renseignement supplémentaire en plus de la date, la commune et le nom des intervenants
                 * 
                 * @return boolean 
                 */
                public function a_infos()
                
                {
                 switch ($this -> i_idf_type_presence)
                 {
                case IDF_PRESENCE_INTV:
                     return !empty($this -> st_origine) || !empty($this -> st_dnais) || !empty($this -> st_age) || ! empty($this -> st_profession) || !empty($this -> st_commentaire);
                     break;
                 default:
                     return !empty($this -> st_patronyme) || !empty($this -> st_prenom) || ! empty($this -> st_profession) || !empty($this -> st_commentaire);
                     } 
                } 
            } 
        ?>