<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------?

require_once __DIR__ . '/../Commun/Courriel.php';
class ModificationActe extends Acte
{

    protected $connexionBD;
    protected $typeActe;
    protected $i_idf;
    protected $i_idf_acte;
    protected $i_idf_type_acte;
    protected $st_date;
    protected $i_jour;
    protected $i_mois;
    protected $i_annee;
    protected $st_date_rep;
    protected $st_cote;
    protected $st_libre;
    protected $st_commentaires;
    protected $st_url;
    protected $a_liste_personnes;
    protected $ga_grille_saisie;
    protected $st_date_modif;
    protected $st_email_demandeur;
    protected $st_ip_demandeur;
    protected $st_cmt_modif;
    protected $i_idf_valideur;
    protected $st_date_validation;
    protected $st_statut;
    protected $st_motif_refus;
    protected $a_photos;
    protected $st_commune;
    protected $st_type_acte;
    public static $i_nb_photos = 4;
    protected $a_params_completion_auto;
    protected $courriel;

    public function __construct($pconnexionBD, $pi_idf_acte, $pi_idf_modification, $pst_rep_site, $pst_serveur_smtp, $pst_utilisateur_smtp, $pst_mdp_smtp, $pi_port_smtp)
    {
        $this->connexionBD = $pconnexionBD;
        $this->i_idf_type_acte = null;
        if (!is_null($pi_idf_acte))
            $st_requete = "select a.idf_type_acte,ta.nom,a.idf_commune,ca.nom,concat(ca.nom,' (',lpad(ca.code_insee,5,'0'),')') from acte a join type_acte ta on (a.idf_type_acte=ta.idf) join commune_acte ca on (a.idf_commune=ca.idf) where a.idf=$pi_idf_acte";
        else if (!is_null($pi_idf_modification))
            $st_requete = "select a.idf_type_acte,ta.nom,a.idf_commune,ca.nom,concat(ca.nom,' (',lpad(ca.code_insee,5,'0'),')') from modification_acte ma join acte a on (ma.idf_acte=a.idf) join type_acte ta on (a.idf_type_acte=ta.idf) join commune_acte ca on (a.idf_commune=ca.idf) where ma.idf=$pi_idf_modification";
        list($i_idf_type_acte, $st_type_acte, $i_idf_commune, $st_commune, $st_commune_insee) = $this->connexionBD->sql_select_liste($st_requete);
        $this->i_idf = $pi_idf_modification;
        $this->i_idf_acte = $pi_idf_acte;
        $this->i_idf_type_acte = $i_idf_type_acte;
        $this->st_type_acte = $st_type_acte;
        $this->i_idf_commune = $i_idf_commune;
        $this->st_commune = $st_commune;
        $this->st_commune_insee = $st_commune_insee;
        $this->st_date = null;
        $this->st_date_rep = null;
        $this->st_cote = null;
        $this->st_libre = null;
        $this->st_commentaires = null;
        $this->st_url = null;
        $this->ga_grille_saisie = array(
            IDF_NAISSANCE => array(IDF_PRESENCE_INTV, IDF_PRESENCE_PERE, IDF_PRESENCE_MERE, IDF_PRESENCE_PARRAIN, IDF_PRESENCE_MARRAINE),
            IDF_DECES => array(IDF_PRESENCE_INTV, IDF_PRESENCE_EXCJT, IDF_PRESENCE_PERE, IDF_PRESENCE_MERE, IDF_PRESENCE_TEMOIN, IDF_PRESENCE_TEMOIN),
            IDF_MARIAGE => array(IDF_PRESENCE_INTV, IDF_PRESENCE_EXCJT, IDF_PRESENCE_PERE, IDF_PRESENCE_MERE, IDF_PRESENCE_INTV, IDF_PRESENCE_EXCJT, IDF_PRESENCE_PERE, IDF_PRESENCE_MERE, IDF_PRESENCE_TEMOIN, IDF_PRESENCE_TEMOIN, IDF_PRESENCE_TEMOIN, IDF_PRESENCE_TEMOIN)
        );
        $this->ga_sexe_personne = array(
            IDF_NAISSANCE => array('?', 'M', 'F', 'M', 'F'),
            IDF_DECES => array('?', '?', 'M', 'F', '?', '?'),
            IDF_MARIAGE => array('M', 'F', 'M', 'F', 'F', 'M', 'M', 'F', '?', '?', '?', '?')
        );
        $this->a_liste_personnes = array();
        $this->a_photos = array();
        $this->a_params_completion_auto = parent::getParamsCompletionAuto();
        $this->st_ip_demandeur = $_SERVER['REMOTE_ADDR'];
        $this->st_nom_demandeur = '';
        $this->st_prenom_demandeur = '';
        $this->courriel = new Courriel($pst_rep_site, $pst_serveur_smtp, $pst_utilisateur_smtp, $pst_mdp_smtp, $pi_port_smtp);
    }

    static public function getNbPhotos()
    {
        return self::$i_nb_photos;
    }

    /**
     * Positionne l'email du demandeur
     */
    public function setEmailDemandeur($pst_email_demandeur)
    {
        $this->st_email_demandeur = $pst_email_demandeur;
    }

    /**
     * Charge la correction de l'acte à partir de la BD
     * 
     * @param integer $pi_idf_modification identifiant de la modification
     */
    public function charge($pi_idf_modification)
    {
        $st_requete = "select ma.idf_acte,a.idf_type_acte,ma.date_modif,ma.email_demandeur,adht.nom,adht.prenom,ma.ip_demandeur,ma.cmt_modif,ma.photo1,ma.photo2,ma.photo3,ma.idf_valideur,ma.date_validation,ma.statut,ma.motif_refus,ma.date,ma.date_rep,ma.cote,ma.libre,ma.commentaires,ma.url from modification_acte ma join acte a  on (ma.idf_acte=a.idf) left join adherent adht on (ma.email_demandeur=adht.email_perso) where ma.idf=$pi_idf_modification";
        list($i_idf_acte, $i_idf_type_acte, $st_date_modif, $st_email_demandeur, $st_nom_demandeur, $st_prenom_demandeur, $st_ip_demandeur, $st_cmt_modif, $st_photo1, $st_photo2, $st_photo3, $i_idf_valideur, $st_date_validation, $st_statut, $st_motif_refus, $st_date, $st_date_rep, $st_cote, $st_libre, $st_commentaires, $st_url) = $this->connexionBD->sql_select_liste($st_requete);
        if (!empty($i_idf_type_acte)) {
            $this->i_idf_type_acte = $i_idf_type_acte;
            $this->i_idf_acte = $i_idf_acte;
            $this->st_date_modif = $st_date_modif;
            $this->st_email_demandeur = $st_email_demandeur;
            $this->st_nom_demandeur = $st_nom_demandeur;
            $this->st_prenom_demandeur = $st_prenom_demandeur;
            $this->st_ip_demandeur = $st_ip_demandeur;
            $this->st_cmt_modif = $st_cmt_modif;
            $this->a_photos[] = $st_photo1;
            $this->a_photos[] = $st_photo2;
            $this->a_photos[] = $st_photo3;
            $this->i_idf_valideur = $i_idf_valideur;
            $this->st_date_validation = $st_date_validation;
            $this->st_statut = $st_statut;
            $this->st_motif_refus = $st_motif_refus;
            $this->st_date = $st_date;
            $this->st_date_rep = str_replace(' ', '', $st_date_rep);
            $this->st_cote = $st_cote;
            $this->st_libre = $st_libre;
            if (preg_match('/(http\:\/\/[\w\:\/\.]+)/', $st_commentaires, $a_champs)) {
                $this->st_url = $a_champs[1];
                $this->st_commentaires = str_replace($this->st_url, '', $st_commentaires);
            } else {
                $this->st_url = $st_url;
                $this->st_commentaires = $st_commentaires;
            }
            $this->a_liste_personnes = array();
            $st_requete = "select idf from modification_personne where idf_modification_acte=$pi_idf_modification order by idf";
            $a_liste_personnes = $this->connexionBD->sql_select($st_requete);
            foreach ($a_liste_personnes as $i_idf_personne) {
                $o_pers = new ModificationPersonne($this->connexionBD, $pi_idf_modification);
                $o_pers->charge($i_idf_personne);
                $this->a_liste_personnes[] = $o_pers;
            }
        } else {
            throw new Exception("L'acte initial a été modifié");
        }
    }

    /**
     * Crée l'enregistrement en base
     * 
     * @return integer identifiant de l'enregistrement créé
     */
    public function cree()
    {
        for ($i = 1; $i <= count($this->a_photos); $i++) {
            $a_nom_cols_photos[] = "photo$i";
            $a_val_cols_photos[] = "'" . $this->a_photos[$i - 1] . "'";
        }
        $st_nom_cols_photos = join(',', $a_nom_cols_photos);
        $st_val_cols_photos = join(',', $a_val_cols_photos);
        $this->connexionBD->initialise_params(array(':idf_acte' => $this->i_idf_acte, ':email_demandeur' => $this->st_email_demandeur, ':ip_demandeur' => $this->st_ip_demandeur, ':cmt_modif' => $this->st_cmt_modif, ':date' => $this->st_date, ':date_rep' => $this->st_date_rep, ':cote' => $this->st_cote, ':libre' => $this->st_libre, ':commentaires' => $this->st_commentaires, ':url' => $this->st_url));
        $st_requete = sprintf("insert into modification_acte(idf_acte,date_modif,email_demandeur,ip_demandeur,cmt_modif,%s,date,date_rep,cote,libre,commentaires,url) values(:idf_acte,now(),:email_demandeur,:ip_demandeur,:cmt_modif,%s,:date,:date_rep,:cote,:libre,:commentaires,:url)", $st_nom_cols_photos, $st_val_cols_photos);
        $this->connexionBD->execute_requete($st_requete);
        $i_idf_modification = $this->connexionBD->dernier_idf_insere();
        foreach ($this->a_liste_personnes as $o_pers) {
            $o_pers->setModificationActe($i_idf_modification);
            $o_pers->cree();
        }
        return $i_idf_modification;
    }

    /**
     * Renvoie le formulaire d'édition d'un acte
     */
    public function formulaire_liste_personnes()
    {
        // les actes divers correspondent à une grille de mariage élaborée dans Nimègue
        $a_grille = array_key_exists($this->i_idf_type_acte, $this->ga_grille_saisie) ? $this->ga_grille_saisie[$this->i_idf_type_acte] : $this->ga_grille_saisie[IDF_MARIAGE];
        $st_chaine = "";
        $i = 0;
        $a_liste_personnes = $this->a_liste_personnes;
        $st_idf_patro_intv = null;
        $i_nb_intv = 0;
        $i_nb_temoins = 0;
        foreach ($a_grille as $i_idf_type_presence) {
            switch ($i_idf_type_presence) {
                case IDF_PRESENCE_INTV:
                    $i_nb_intv++;
                    switch ($this->i_idf_type_acte) {
                        case IDF_MARIAGE:
                            $st_lib = $i_nb_intv % 2 == 0 ? "Epouse" : "Epoux";
                            break;
                        default:
                            $st_lib = "Intervenant $i_nb_intv";
                    }
                    $st_chaine .= "<tr class=\"table-primary\"><td  colspan=8>$st_lib</td></tr>";
                    break;
                case IDF_PRESENCE_TEMOIN;
                    $i_nb_temoins++;
                    if ($i_nb_temoins == 1)
                        $st_chaine .= "<tr class=\"table-primary\" ><td colspan=8>T&eacute;moins</td></tr>";
                    break;
                case IDF_PRESENCE_PARRAIN;
                    $st_chaine .= "<tr class=\"table-primary\"><td  colspan=8>Parrain/Marraine</td></tr>";
                    break;
            }
            if (count($a_liste_personnes) > 0 && $a_liste_personnes[0]->getIdfTypePresence() == $i_idf_type_presence) {
                // la personne existe déjà dans la BD
                $o_pers = array_shift($a_liste_personnes);
                $o_pers->setNumParam($i);
                if ($i_idf_type_presence == IDF_PRESENCE_INTV)
                    $st_idf_patro_intv = $o_pers->getIdfPatro();
                $st_chaine .= $o_pers->formulaire_personne($this->i_idf_type_acte, $this->st_commune_insee, $st_idf_patro_intv);
            } else {
                // personne vide dans le type de présence attendu doit êre créé
                $o_pers = new ModificationPersonne($this->connexionBD, $this->i_idf, null, null, null, null);
                $o_pers->setIdfTypePresence($i_idf_type_presence);
                $o_pers->setNumParam($i);
                $st_chaine .= $o_pers->formulaire_personne($this->i_idf_type_acte, $this->st_commune, $st_idf_patro_intv);
            }
            $this->a_params_completion_auto = array_merge($this->a_params_completion_auto, $o_pers->parametres_completion_auto());
            $i++;
        }
        return $st_chaine;
    }

    /**
     * Renvoie l'extension du fichier en fonction de son mime type
     * 
     * @param string $ pst_type_mime (gif|jpeg|png)
     * @return string extension (gif|jpg|png)
     */
    private function type_mime_vers_ext($pst_type_mime)
    {
        switch ($pst_type_mime) {
            case 'image/gif':
                $st_ext = 'gif';
                break;
            case 'image/jpeg':
                $st_ext = 'jpg';
                break;
            case 'image/png':
                $st_ext = 'png';
                break;
            default:
                die("Mauvaise extension $pst_type_mime\n");
        }
        return $st_ext;
    }

    /**
     * renvoie le nom d'une photo dont le fichier n'existe pas
     * 
     * @param integer $pi_idf_commune_acte identifiant de la commune
     * @param integer $pi_idf_acte identifiant de l'acte
     * @param integer $pi_num_photo numéro de la photo
     * @param string $pst_ext extension du nom de fichier
     * @return string nom de la photo
     * @global $gst_rep_photos_modifs
     */
    private function nom_photo($pi_idf_commune_acte, $pi_idf_acte, $pi_num_photo, $pst_ext)
    {
        global $gst_rep_photos_modifs;
        $st_photo = sprintf("%s/%d_%d_%d.%s", $gst_rep_photos_modifs, $pi_idf_commune_acte, $pi_idf_acte, $pi_num_photo, $pst_ext);
        while (file_exists($st_photo)) {
            $pi_num_photo += 3;
            $st_photo = sprintf("%s/%d_%d_%d.%s", $gst_rep_photos_modifs, $pi_idf_commune_acte, $pi_idf_acte, $pi_num_photo, $pst_ext);
            // Sécurité
            if ($pi_num_photo > 50)
                break;
        }
        return $st_photo;
    }

    /**
     * Initialise l'acte depuis un formulaire post
     * 
     * @param integer $pi_idf_acte identifiant de l'acte
     */
    public function initialise_depuis_formulaire($pi_idf_acte)
    {
        parent::initialise_depuis_formulaire($pi_idf_acte);
        $this->st_email_demandeur = isset($_POST["email_demandeur"]) ? substr(trim($_POST["email_demandeur"]), 0, 60) : '';
        $this->st_cmt_modif = isset($_POST["cmt_modif"]) ? trim($_POST["cmt_modif"]) : '';
        $this->st_cmt_modif = self::utf8_vers_cp1252($this->st_cmt_modif);
        for ($i = 1; $i <= self::$i_nb_photos; $i++) {
            $st_photo = '';
            if (isset($_FILES["photo$i"]['tmp_name']) && !empty($_FILES["photo$i"]['tmp_name'])) {
                $st_ext = $this->type_mime_vers_ext($_FILES["photo$i"]['type']);
                $st_photo = $this->nom_photo($this->i_idf_commune, $this->i_idf_acte, $i, $st_ext);
                if (!move_uploaded_file($_FILES["photo$i"]['tmp_name'], $st_photo)) {
                    print("Erreur de telechargement : impossible de copier en $st_photo$i:<br>");
                    switch ($_FILES["photo$i"]['error']) {
                        case 2:
                            print("Fichier trop gros par rapport a MAX_FILE_SIZE");
                            break;
                        default:
                            print("Erreur inconnue");
                            print_r($_FILES);
                    }
                }
            }
            $this->a_photos[] = basename($st_photo);
        }
        $a_grille = array_key_exists($this->i_idf_type_acte, $this->ga_grille_saisie) ? $this->ga_grille_saisie[$this->i_idf_type_acte] : $this->ga_grille_saisie[IDF_MARIAGE];
        $a_sexe_personne = array_key_exists($this->i_idf_type_acte, $this->ga_sexe_personne) ? $this->ga_sexe_personne[$this->i_idf_type_acte] : $this->ga_sexe_personne[IDF_MARIAGE];
        $i_nb_personnes = count($a_grille);
        $this->a_liste_personnes = array();
        $i_nb_intv = 0;
        for ($i = 0; $i < $i_nb_personnes; $i++) {
            $o_pers = new ModificationPersonne($this->connexionBD, $this->i_idf);
            $o_pers->setNumParam($i);
            $o_pers->initialise_depuis_formulaire($this->i_idf, $a_grille[$i]);
            if ($this->i_idf_type_acte == IDF_MARIAGE) {
                // le sexe de l'intervenant est le maitre pour décider des couples
                // ceux-ci sont crées dans la fonction maj_liste_personnes de Acte en fonction de la grille  définie pour le type d'acte
                if ($o_pers->getIdfTypePresence() == IDF_PRESENCE_INTV) {
                    if ($i_nb_intv % 2 == 0) {
                        $c_sexe_intv = 'M';
                        $o_pers->setSexe($c_sexe_intv);
                        $i_nb_intv++;
                    } else {
                        $c_sexe_intv = 'F';
                        $o_pers->setSexe($c_sexe_intv);
                    }
                } else
                    $o_pers->setSexe($a_sexe_personne[$i]);
            } else
         if (empty($o_pers->getSexe()))
                $o_pers->setSexe($a_sexe_personne[$i]);
            $this->a_liste_personnes[] = $o_pers;
        }
    }

    /**
     * Renvoie la chaine de visualisation des photos
     */
    public function visualisation_photos()
    {
        $st_chaine = '<div class="text-center"><div class="wrapper">';
        $i = 1;
        foreach ($this->a_photos as $st_photo) {
            if (!empty($st_photo)) {
                $st_chaine .= "<div id=\"photo$i\" class=\"viewer\" style=\"width: 80%;\" ></div><br>";
                $st_chaine .= "<script type='text/javascript'>var iv$i = $(\"#photo$i\").iviewer({src: \"../photos/$st_photo\"});</script>\n";
                $i++;
            }
        }
        $st_chaine .= "</div></div>\n";
        return $st_chaine;
    }

    /**
     * Renvoie la chaine des commentaires demandeur
     */
    public function commentaires_demandeur()
    {
        $st_chaine = "<fieldset><legend>Commentaires &agrave; destination du valideur:</legend>";
        $st_chaine .= "<div class=\"text-center\">Commentaires:<textarea name=cmt_modif rows=4 cols=80 class=\"form-control jqte_edit\">";
        $st_chaine .= cp1252_vers_utf8($this->st_cmt_modif);
        $st_chaine .= "</textarea></div>";
        $st_chaine .= "</fieldset>";
        $st_chaine .= "<fieldset><legend>Commentaires &agrave; destination du demandeur:</legend>";
        $st_chaine .= "<div class=\"text-center\">Commentaires:<textarea name=cmt_valideur rows=4 cols=80 class=\"form-control jqte_edit\">";
        $st_chaine .= "</textarea></div>";
        $st_chaine .= "</fieldset>";
        return $st_chaine;
    }


    /**
     * Renvoie les différences entre la modification et l'acte d'origine
     */
    public function differences()
    {
        $o_acte = new Acte($this->connexionBD, null, null, null, null, null, null);
        $o_acte->charge($this->i_idf_acte);
        $st_description_acte = $o_acte->versChaine();
        $st_description_modif = $this->versChaine();
        setlocale(LC_CTYPE, 'fr_FR.UTF8');
        //$o_FineDiff = new FineDiff(iconv( "UTF-8","cp1252", $st_description_acte), iconv( "UTF-8","cp1252", $st_description_modif), FineDiff :: $wordGranularity);
        $o_FineDiff = new FineDiff($st_description_acte, $st_description_modif, FineDiff::$wordGranularity);
        $st_chaine = "<fieldset>";
        $st_chaine .= "<legend>Diff&eacute;rences</legend>";
        $st_diffs = $o_FineDiff->renderDiffToHTML();
        $st_chaine .= nl2br($st_diffs);
        $st_chaine .= "</fieldset>";
        return $st_chaine;
    }

    /**
     * Renvoie le formulaire de refus
     */
    public function formulaire_refus()
    {
        $st_chaine = "<fieldset>\n";
        $st_chaine .= "<legend>Refus de la demande</legend>\n";
        $st_chaine .= "<form id=\"modification_refusee\" method=post>\n";
        $st_chaine .= "<input type=\"hidden\" name=\"MODE\" value=\"REFUS\">\n";
        $st_chaine .= sprintf("<input type=\"hidden\" name=\"idf_modification\" value=\"%d\">", $this->i_idf);
        $st_chaine .= '<div class="lib_erreur">';
        $st_chaine .= "<label for=\"motif_refus\">Motif du refus:</Label><textarea name=motif_refus id=motif_refus rows=10 cols=80 class=\"form-control jqte_edit\"></textarea>\n";
        $st_chaine .= '</div>';
        $st_chaine .= "<button type=\"submit\" class=\"col-md-4 col-md-offset-4 alert alert-danger\"><span class=\"glyphicon glyphicon-remove\"></span> Refuser la demande</button>\n";
        $st_chaine .= "</form>\n";
        $st_chaine .= "</fieldset>\n";
        return $st_chaine;
    }

    /**
     * Renvoie la chaine de validation du formulaire de refus
     */
    public function validation_formulaire_refus()
    {
        $st_chaine = '$("#modification_refusee").validate({
  rules: {
      motif_refus: "required"  
  },
  messages: {
     motif_refus: {
        required : "Le motif du refus est obligatoire"
    }
 },';
        $st_chaine .= '
 errorElement: "em",
 errorPlacement: function ( error, element ) {
			// Add the `help-block` class to the error element
			error.addClass( "help-block" );

			// Add `has-feedback` class to the parent div.form-group
			// in order to add icons to inputs
			element.parents( ".lib_erreur" ).addClass( "has-feedback" );

			if ( element.prop( "type" ) === "checkbox" ) {
				error.insertAfter( element.parent( "label" ) );
			} else {
				error.insertAfter( element );
			}
			// Add the span element, if doesn\'t exists, and apply the icon classes to it.
			if ( !element.next( "span" )[ 0 ] ) {
				$( "<span class=\'glyphicon glyphicon-remove form-control-feedback\'></span>" ).insertAfter( element );
			}
		},
		success: function ( label, element ) {
			// Add the span element, if doesn\'t exists, and apply the icon classes to it.
			if ( !$( element ).next( "span" )[ 0 ] ) {
				$( "<span class=\'glyphicon glyphicon-ok form-control-feedback\'></span>" ).insertAfter( $( element ) );
			}
		},
		highlight: function ( element, errorClass, validClass ) {
			$( element ).parents( ".lib_erreur" ).addClass( "has-error" ).removeClass( "has-success" );
			$( element ).next( "span" ).addClass( "glyphicon-remove" ).removeClass( "glyphicon-ok" );
		},
		unhighlight: function ( element, errorClass, validClass ) {
			$( element ).parents( ".lib_erreur" ).addClass( "has-success" ).removeClass( "has-error" );
			$( element ).next( "span" ).addClass( "glyphicon-ok" ).removeClass( "glyphicon-remove" );
		}
});';
        return "\n$st_chaine\n\n";
    }

    /**
     * Refuse la modification (changement du statut + envoil d'un email au demandeur)
     * 
     * @param integer $pi_idf_valideur identifiant du valideur
     * @param string $pst_prenom_valideur prénom du valideur
     * @param string $pst_nom_valideur nom du valideur
     * @param string $pst_email_valideur email du valideur
     * @param string $pst_motif motif du refus
     */
    public function refuse($pi_idf_valideur, $pst_prenom_valideur, $pst_nom_valideur, $pst_email_valideur, $pst_motif_refus)
    {
        global $gst_url_site;
        $st_requete = "update modification_acte set idf_valideur=:idf_valideur,date_validation=now(),statut='R',motif_refus=:motif where idf=:idf";
        $this->connexionBD->initialise_params(array(':idf_valideur' => $pi_idf_valideur, ':motif' => utf8_vers_cp1252($pst_motif_refus), ':idf' => $this->i_idf));
        $this->connexionBD->execute_requete($st_requete);
        print("<div class=\"alert alert-danger\">La modification a &eacute;t&eacute; refus&eacute;e</div>");
        $st_description_acte = $this->versChaine();
        if (!empty($this->st_email_demandeur)) {
            $this->courriel->setExpediteur($pst_email_valideur, "$pst_prenom_valideur $pst_nom_valideur");
            $this->courriel->setAdresseRetour($pst_email_valideur);
            $this->courriel->setEnCopieCachee($pst_email_valideur);
            $this->courriel->setDestinataire($this->st_email_demandeur, '');
            $this->courriel->setSujet('Base ' . SIGLE_ASSO . ': Votre demande de modification');
            $st_message_html = 'Bonjour,<br><br>';
            $st_message_html .= 'Merci de votre proposition de modification:<br><br>';
            $st_message_html .= "<pre>$st_description_acte</pre>";
            $st_message_html .= "Celle-ci n'a malheureusement pas &eacute;t&eacute; accept&eacute;e pour la raison suivante:<br><br>";
            $st_message_html .= "<font color=red>$pst_motif_refus</font>";
            $st_message_html .= "<br><br>Nous vous invitons &agrave; renouveler votre demande en respectant les remarques qui vous sont indiqu&eacute;es<br><br>";
            $st_message_html .= sprintf("<a href=\"$gst_url_site/PropositionModification.php?idf_acte=%d\">$gst_url_site/PropositionModification.php?idf_acte=%d</a><br>", $gst_url_site, $this->i_idf_acte, $gst_url_site, $this->i_idf_acte);
            $st_message_html .= "Merci encore pour votre d&eacute;marche<br>";
            $st_message_html .= "<br>Cordialement<br>";
            $st_message_html .= "$pst_prenom_valideur $pst_nom_valideur";
            $this->courriel->setTexte($st_message_html);
            if ($this->courriel->envoie()) {
                print("<div class=\"alert alert-success\">L'email a &eacute;t&eacute; envoy&eacute; avec succ&egrave;s</div>");
            } else {
                print("<div class=\"alert alert-danger\">La modification a &eacute;t&eacute; accept&eacute;e mais l'envoi du mail a &eacute;chou&eacute;" . $this->courriel->get_erreur() . "</div>");
            }
        }
    }

    /**
     * Accepte la modification (changement du statut + envoil d'un email au demandeur)
     * 
     * @param integer $pi_idf_valideur identifiant du valideur
     * @param string $pst_prenom_valideur prénom du valideur
     * @param string $pst_nom_valideur nom du valideur
     * @param string $pst_email_valideur email du valideur
     * @param string $pst_cmt_valideur commentaires du valideur
     */
    public function accepte($pi_idf_valideur, $pst_prenom_valideur, $pst_nom_valideur, $pst_email_valideur, $pst_cmt_valideur)
    {
        global $gst_url_site;
        $go_acte = new Acte($this->connexionBD, null, null, null, null, null, null);
        $go_acte->charge($this->i_idf_acte);
        $go_acte->initialise_depuis_formulaire($this->i_idf_acte);
        $stats_commune = new StatsCommune($this->connexionBD, $go_acte->getIdfCommune(), $go_acte->getIdfSource());
        $unions = Union::singleton($this->connexionBD);
        $st_requete = "LOCK TABLES `personne` write , `patronyme` write , `patronyme` as pat read ,`prenom` write ,`acte` write, `profession` write, `commune_personne` write, `union` write, `stats_patronyme` as sp read,`stats_patronyme` write,`stats_commune` write,`acte` as a read,`personne` as p read, `type_acte` read, `type_acte` as ta read,`prenom_simple` write, `groupe_prenoms` write";
        $this->connexionBD->execute_requete($st_requete);
        $go_acte->maj_liste_personnes($go_acte->getIdfSource(), $go_acte->getIdfCommune(), $unions);
        $go_acte->sauve();
        $stats_commune->maj_stats($go_acte->getIdfTypeActe());
        $this->connexionBD->execute_requete("UNLOCK TABLES");
        print("<div class=\"alert alert-success\">Modification effectu&eacute;e</div>\n");
        $st_requete = sprintf("update modification_acte set idf_valideur=%d,date_validation=now(),statut='A' where idf=%d", $pi_idf_valideur, $this->i_idf);
        $this->connexionBD->execute_requete($st_requete);
        $this->courriel->setExpediteur($pst_email_valideur, "$pst_prenom_valideur $pst_nom_valideur");
        $this->courriel->setAdresseRetour($pst_email_valideur);
        $this->courriel->setEnCopieCachee($pst_email_valideur);
        $this->courriel->setDestinataire($this->st_email_demandeur, '');
        $this->courriel->setSujet('Base ' . SIGLE_ASSO . ': Demande de modification de relevé de la base ' . SIGLE_ASSO);
        $st_message_html = 'Bonjour,<br><br>';
        $st_message_html .= "Votre proposition de modification d'un acte de la base " . SIGLE_ASSO . " vient d'&ecirc;tre accept&eacute;e<br>";
        $st_message_html .= "Vous pouvez consulter l'acte &agrave l'adresse suivante:<br>";
        $st_message_html .= sprintf("<a href=\"%s/InfosTD.php?idf_acte=%d\">$gst_url_site/InfosTD.php?idf_acte=%d</a><br>", $gst_url_site, $this->i_idf_acte, $this->i_idf_acte);
        if (!empty($pst_cmt_valideur)) {
            $st_message_html .= "<br>Commentaires du valideur: $pst_cmt_valideur<br>";
        }
        $st_message_html .= "<br>Merci de votre contribution<br>";
        $st_message_html .= "<br>Cordialement<br>";
        $st_message_html .= "$pst_prenom_valideur $pst_nom_valideur";
        $this->courriel->setTexte($st_message_html);

        if ($this->courriel->envoie()) {
            print("<div class=\"alert alert-success\">L'email a &eacute;t&eacute; envoy&eacute; avec succ&egrave;s</div>");
        } else {
            print("<div class=\"alert alert-danger\">La modification a &eacute;t&eacute; accept&eacute;e mais l'envoi du mail a &eacute;chou&eacute;" . $this->courriel->get_erreur() . "</div>");
        }
    }

    /**
     * Renvoie les informations concernant le demandeur
     */
    public function infos_demandeur()



    {
        $st_chaine = '';
        if (!empty($this->st_email_demandeur)) {
            $st_chaine = "<fieldset>\n";
            $st_chaine .= "<legend>demandeur</legend>\n";
            $st_chaine .= sprintf("<div class=\"text-center\">%s %s (%s)</div>", cp1252_vers_utf8($this->st_nom_demandeur), cp1252_vers_utf8($this->st_prenom_demandeur), $this->st_email_demandeur);
            $st_chaine .= "</fieldset>\n";
        }
        return $st_chaine;
    }
}
