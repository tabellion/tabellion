<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

class Personne
 {
				/*
				* Renvoie une chaine encodée en cp1252 en UTF8
				* @param string $st_valeur chaine cp1252 à convertir
				* @return string chaine encodée en UTF8
				*/
				static public function cp1252_vers_utf8($st_valeur)
				{
					return mb_convert_encoding($st_valeur,'UTF8','cp1252');
				}
				
				/*
				* Renvoie une chaine encodée en UTF8 en cp1252
				* @param string $st_valeur chaine UTF8 à convertir
				* @return string chaine encodée en UTF8
				*/
				static public function utf8_vers_cp1252($st_valeur)
				{
					return mb_convert_encoding($st_valeur,'cp1252','UTF8');
				}
				
				function __autoload( $class_name )
				
				
				
				{
								 require_once $class_name . '.php';
								 } 
				protected $connexionBD;
				 protected $compteurPersonne;
				 protected $communePersonne;
				 protected $profession;
				 protected $i_idf;
				 protected $i_idf_acte;
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
				 protected $i_num_param;
				 protected $i_nb_lignes;
				
				 public function __construct( $pconnexionBD, $pi_idf_acte, $pi_idf_type_presence, $pc_sexe, $pst_patronyme, $pst_prenom )
				
				
				
				
				{
								 $this -> connexionBD = $pconnexionBD;
								 $this -> communePersonne = CommunePersonne :: singleton( $pconnexionBD );
								 $this -> profession = Profession :: singleton( $pconnexionBD );
								 $this -> prenom = Prenom :: singleton( $pconnexionBD );
								 $this -> compteurPersonne = CompteurPersonne :: singleton( $pconnexionBD );
								 $this -> compteurPersonne -> incremente();
								 $this -> i_idf = $this -> compteurPersonne -> getCompteur();
								 $this -> i_idf_acte = $pi_idf_acte;
								 $this -> c_sexe = $pc_sexe;
								 $this -> st_patronyme = Personne :: patronyme_propre( $pst_patronyme );
								 $this -> st_prenom = Personne :: prenom_propre( $pst_prenom );
								 $this -> prenom -> ajoute( $this -> st_prenom );
								 $this -> i_idf_type_presence = $pi_idf_type_presence;
								 $this -> st_residence = null;
								 $this -> st_origine = null;
								 $this -> a_origine = array();
								 $this -> a_profession = array();
								 $this -> st_age = null;
								 $this -> i_idf_mere = 0;
								 $this -> i_idf_pere = 0;
								 $this -> i_est_decede = null;
								 $this -> a_filtres_parametres = array();
								 $this -> a_parametres_completion_auto = array();
								 $this -> i_num_param = null;
								 $this -> i_nb_lignes = 0;
								 } 
				
				public function setPatronyme( $pst_nom )
				
				
				
				
				{
								 $this -> st_patronyme = patronyme_propre( $pst_nom );
								 } 
				
				public function getPatronyme()
				
				
				
				
				{
								 return $this -> st_patronyme;
								 } 
				
				public function setNumParam( $pi_num_param )
				
				
				
				
				{
								 $this -> i_num_param = $pi_num_param;
								 } 
				
				public function getNumParam()
				
				
				
				
				{
								 return $this -> i_num_param;
								 } 
				
				public function getIdfPatro()
				
				
				
				
				{
								 return sprintf( "#patro%d", $this -> i_num_param );
								 } 
				
				public function setPrenom( $pst_prenom )
				
				
				
				
				{
								 $this -> st_prenom = prenom_propre( $pst_prenom );
								 $this -> prenom -> ajoute( $this -> st_prenom );
								 } 
				
				public function setSurnom( $pst_surnom )
				
				
				
				
				{
								 $this -> st_surnom = $pst_surnom ;
								 }
								 
				public function setSexe( $pst_sexe )
				
				
				
				
				{
								 $this -> c_sexe = $pst_sexe;
								 } 
				
				public function setAge( $pst_age )
				
				
				
				
				{
								
								 if ( !empty( $pst_age ) )
												
												 $this -> st_age = $pst_age;
								
								 } 
				
				public function setAnneeNaissance( $pi_annee_naissance )
				
				
				
				
				{
								 if ( !empty( $pi_annee_naissance ) )
												 $this -> st_date_naissance = sprintf( "__/__/%d", $pi_annee_naissance );
								 } 
				

			public function setDateNaissance($pst_date_naissance)
			{
				if (!empty($pst_date_naissance))
				{ 
					if (preg_match('/^\d+$/',$pst_date_naissance))
					// la date de naissance ne comporte qu'une année
						$this -> st_date_naissance = sprintf("__/__/%04d",$pst_date_naissance);
					else 
						$this -> st_date_naissance = $pst_date_naissance;
				}
			}

				
				public function setOrigine( $pst_origine )
				
				
				
				
				{
								 $pst_origine=trim($pst_origine);
								 if ( !empty( $pst_origine ) )
												 {
												$this -> st_origine = trim($pst_origine);
												 $this -> communePersonne -> ajoute( $pst_origine );
												 } 
								}
				
				public function setResidence( $pst_residence )
				
				
				
				
				{
							    $pst_residence=trim($pst_residence);
								 if ( !empty( $pst_residence ) )
												 {
												$this -> st_residence = trim($pst_residence);
												 $this -> communePersonne -> ajoute( $pst_residence );
												 } 
								} 		
				
				public function getAge( $pst_age )
				
				
				
				
				{
								 return $this -> st_age;
								 } 
				public function getSexe()
				
				
				
				
				{
								 return $this -> c_sexe;
								 } 
				
				public function setIdfActe( $pi_idf_acte )
				
				
				
				
				{
								 $this -> i_idf_acte = $pi_idf_acte;
								 } 
				
				public function getIdfActe()
				
				
				
				
				{
								 return $this -> i_idf_acte;
								 } 
				
				public function getIdfTypePresence()
				
				
				
				
				{
								 return $this -> i_idf_type_presence;
								 } 
				
				public function setIdfTypePresence( $pi_idf_type_presence )
				
				
				
				
				{
								 $this -> i_idf_type_presence = $pi_idf_type_presence;
								 } 
				
				public function setIdfPere( $pi_idf_pers )
				
				
				
				
				{
								 $this -> i_idf_pere = $pi_idf_pers;
								 } 
				
				public function setIdfMere( $pi_idf_pers )
				
				
				
				
				{
								 $this -> i_idf_mere = $pi_idf_pers;
								 } 
				
				public function setCommentaires( $pst_commentaire )
				
				
				
				
				{
								
								 $this -> st_commentaire = $this -> commentaire_propre( $pst_commentaire );
								 } 
				
				public function getNbLignes()
				
				
				
				
				{
								 return $this -> i_nb_lignes;
								 } 
				
				
				public function setProfession( $pst_profession )
				
				
				
				
				{
								
								 $pst_profession=trim($pst_profession);
								 $this -> st_profession = $pst_profession;
								 $this -> profession -> ajoute( $pst_profession );
								 } 
				
				
				
				public function setIdf( $pi_idf )
				
				
				
				
				{
								 $this -> i_idf = $pi_idf;
								 } 
				
				public function getIdf()
				
				
				
				
				{
								 return $this -> i_idf;
								 } 
				
				public function sauveCommunePersonne()
				
				
				
				
				{
								 $this -> communePersonne -> sauve();
								 } 
				
				public function sauveProfession()
				
				
				
				
				{
								 $this -> profession -> sauve();
								 } 
				
				public function sauvePrenom()
				
				
				
				
				{
								 $this -> prenom -> sauve();
								 } 
				
				public function importeMarNimV2( $pst_origine, $pst_date_naissance, $pst_age, $pst_profession )
				
				
				
				
				{
								 $this -> st_origine = trim( $pst_origine );
								 $this -> st_date_naissance = $pst_date_naissance;
								 $this -> st_age = $pst_age;
								 $this -> st_profession = trim( $pst_profession );
								 $this -> communePersonne -> ajoute( $this -> st_origine );
								 $this -> profession -> ajoute( $this -> st_profession );
								 } 
				
				public function importeDecNimV2( $pst_origine, $pst_date_naissance, $pst_age, $pst_profession, $pst_commentaire )
				
				
				
				
				{
								 $this -> st_origine = trim( $pst_origine );
								 $this -> st_date_naissance = $pst_date_naissance;
								 $this -> st_age = $pst_age;
								 $this -> st_profession = trim( $pst_profession );
								 $this -> st_commentaire = $this -> commentaire_propre( $pst_commentaire );
								 $this -> communePersonne -> ajoute( $this -> st_origine );
								 $this -> profession -> ajoute( $this -> st_profession );
								 } 
				
				public function importeDivNimV2( $pst_origine, $pst_date_naissance, $pst_age, $pst_profession )
				
				
				
				
				{
								 $this -> st_origine = trim( $pst_origine );
								 $this -> st_date_naissance = $pst_date_naissance;
								 $this -> st_age = $pst_age;
								 $this -> st_profession = trim( $pst_profession );
								 $this -> communePersonne -> ajoute( $this -> st_origine );
								 $this -> profession -> ajoute( $this -> st_profession );
								 } 
				
				public function importeMarNimV3( $pst_origine, $pst_date_naissance, $pst_age, $pst_profession )
				
				
				
				
				{
								 $this -> st_origine = trim( $pst_origine );
								 $this -> st_date_naissance = $pst_date_naissance;
								 $this -> st_age = $pst_age;
								 $this -> st_profession = trim( $pst_profession );
								 $this -> communePersonne -> ajoute( $this -> st_origine );
								 $this -> profession -> ajoute( $this -> st_profession );
								 } 
				
				public function importeDecNimV3( $pst_origine, $pst_date_naissance, $pst_age, $pst_profession, $pst_commentaire )
				
				
				
				
				{
								 $this -> st_origine = trim( $pst_origine );
								 $this -> st_date_naissance = $pst_date_naissance;
								 $this -> st_age = $pst_age;
								 $this -> st_profession = trim( $pst_profession );
								 $this -> st_commentaire = $this -> commentaire_propre( $pst_commentaire );
								 $this -> communePersonne -> ajoute( $this -> st_origine );
								 $this -> profession -> ajoute( $this -> st_profession );
								 } 
				
				public function importeDivNimV3( $pst_origine, $pst_date_naissance, $pst_age, $pst_profession )
				
				
				
				
				{
								 $this -> st_origine = trim( $pst_origine );
								 $this -> st_date_naissance = $pst_date_naissance;
								 $this -> st_age = $pst_age;
								 $this -> st_profession = trim( $pst_profession );
								 $this -> communePersonne -> ajoute( $this -> st_origine );
								 $this -> profession -> ajoute( $this -> st_profession );
								 } 
				
				/**
				* Renvoie la personne sous forme d'une texte
				*/
				public function versChaine( $pi_idf_type_acte )
				
				
				
				
				{
								 $st_chaine = '';
								 $i_nb_lignes = 0;

								 switch ( $this -> i_idf_type_presence )
								 {
												case IDF_PRESENCE_INTV:
																
																 switch ( $pi_idf_type_acte )
																 {
																				case IDF_NAISSANCE:
																								 $st_chaine .= sprintf( "De: %s %s (%s)\n", self::cp1252_vers_utf8($this -> st_patronyme), self::cp1252_vers_utf8($this -> st_prenom), $this -> c_sexe );
																								 $i_nb_lignes = 1;
																								 if ( !empty( $this -> st_commentaire ) )
																												 {
																												$st_chaine .= self::cp1252_vers_utf8($this -> st_commentaire) . "\n";
																												 $i_nb_lignes++;
																												 } 
																								break;
																				 case IDF_RECENS:
																								 $st_chaine .= sprintf( "%s %s (%s) ", self::cp1252_vers_utf8($this -> st_patronyme), self::cp1252_vers_utf8($this -> st_prenom), $this -> c_sexe );
																								 $i_nb_lignes = 1;
																								 if ( !empty( $this -> st_commentaire ) )
																												 {
																												$st_chaine .= self::cp1252_vers_utf8($this -> st_commentaire);
																												 $i_nb_lignes++;
																												 } 
																								$st_ligne = '';
																								 if ( !preg_match( '/^\s*$/', $this -> st_date_naissance ) )
																												 {
																													 if (preg_match( '/^__\/__\/(\d+)$/', $this -> st_date_naissance,$a_correspondances ) || preg_match( '/^\?\?\/\?\?\/(\d+)$/', $this -> st_date_naissance,$a_correspondances ))
																													 {
																														 $st_lib = $this -> c_sexe != 'F'? 'Né':'Née';
																													 $st_ligne .= sprintf( " $st_lib en %s", $a_correspondances[1] );
																													 }
																													 else
																													 {
																														$st_lib = $this -> c_sexe != 'F'? 'Né':'Née';
																														$st_ligne .= sprintf( " $st_lib le %s", $this -> st_date_naissance );
																													 }	
																												 }
																								 if ( !preg_match( '/^\s*$/', $this -> st_age ) )
																												 {
																												$st_lib = $this -> c_sexe != 'F'? 'Agé':'Agée';
																												 $st_ligne .= sprintf( " $st_lib de %s", $this -> st_age );
																												 if ( preg_match( '/^\d+$/', $this -> st_age ) )
																																 $st_ligne .= " ans";
																												 } 
																								if ( !empty( $this -> st_profession ) )
																												 $st_ligne .= sprintf( " Profession de %s", self::cp1252_vers_utf8($this -> st_profession) );
																								if ( !empty( $this -> st_origine ) )
																												 $st_ligne .= sprintf( " Originaire de %s", self::cp1252_vers_utf8($this -> st_origine) );			 
																								 if ( $st_ligne != '' )
																												 $st_chaine .= "$st_ligne\n";
																								 $i_nb_lignes++;
																								
																								 break;
																				 default:
																								 $st_chaine .= $this -> i_num_param == 1 ? "De: " : "Avec: ";
																								 $st_chaine .= sprintf( "%s %s (%s)\n", self::cp1252_vers_utf8($this -> st_patronyme), self::cp1252_vers_utf8($this -> st_prenom), $this -> c_sexe );
																								
																								 $i_nb_lignes = 1;
																								 if ( !empty( $this -> st_commentaire ) )
																												 {
																												$st_chaine .= self::cp1252_vers_utf8($this -> st_commentaire) . "\n";
																												 $i_nb_lignes++;
																												 } 
																								$st_ligne = '';
																								 if ( !empty( $this -> st_origine ) )
																												 $st_ligne .= sprintf( " Originaire de %s", self::cp1252_vers_utf8($this -> st_origine) );
																								 $this -> st_date_naissance = preg_replace( '/^\s+$/', '', $this -> st_date_naissance );
																								 if ( !preg_match( '/^\s*$/', $this -> st_date_naissance ) )
																												 {
																													 if (preg_match( '/^__\/__\/(\d+)$/', $this -> st_date_naissance,$a_correspondances )|| preg_match( '/^\?\?\/\?\?\/(\d+)$/', $this -> st_date_naissance,$a_correspondances ))
																													 {
																														 $st_lib = $this -> c_sexe != 'F'? 'Né':'Née';
																													 $st_ligne .= sprintf( " $st_lib en %s", $a_correspondances[1] );
																													 }
																													 else
																													 {
																														$st_lib = $this -> c_sexe != 'F'? 'Né':'Née';
																														$st_ligne .= sprintf( " $st_lib le %s", $this -> st_date_naissance );
																													 }	
																												 } 
																								if ( !preg_match( '/^\s*$/', $this -> st_age ) )
																												 {
																												$st_lib = $this -> c_sexe != 'F'? 'Agé':'Agée';
																												 $st_ligne .= sprintf( " $st_lib de %s", $this -> st_age );
																												 if ( preg_match( '/^\d+$/', $this -> st_age ) )
																																 $st_ligne .= " ans";
																												 } 
																								if ( !empty( $this -> st_profession ) )
																												 $st_ligne .= sprintf( " Profession de %s\n", self::cp1252_vers_utf8($this -> st_profession) );
																								 if ( $st_ligne != '' )
																												 $st_chaine .= "$st_ligne\n";
																								 $i_nb_lignes++;
																								 } 
																break;
												 case IDF_PRESENCE_PERE:
												 case IDF_PRESENCE_MERE:
																 if ( !empty( $this -> st_patronyme ) )
																				 $st_chaine .= sprintf( "%s %s", self::cp1252_vers_utf8($this -> st_patronyme), self::cp1252_vers_utf8($this -> st_prenom) );
																 if ( !empty( $this -> st_profession ) )
																				 $st_chaine .= sprintf( " Profession de %s", self::cp1252_vers_utf8($this -> st_profession) );
																 $st_chaine .= ' ' . self::cp1252_vers_utf8($this -> st_commentaire);
																 $st_chaine .= "\n";
																 $i_nb_lignes = 1;
																 break;
												 case IDF_PRESENCE_EXCJT:
																 if ( !empty( $this -> st_patronyme ) )
																				 {
																				$st_chaine .= sprintf( "Ancien conjoint: %s %s", self::cp1252_vers_utf8($this -> st_patronyme), self::cp1252_vers_utf8($this -> st_prenom) );
																				 if ( !empty( $this -> st_profession ) )
																								 $st_chaine .= sprintf( " Profession de %s", self::cp1252_vers_utf8($this -> st_profession) );
																				 $st_chaine .= ' ' . self::cp1252_vers_utf8($this -> st_commentaire);
																				 $st_chaine .= "\n";
																				 $i_nb_lignes = 1;
																				 } 
																break;
												 case IDF_PRESENCE_PARRAIN:
																 if ( !empty( $this -> st_patronyme ) )
																				 {
																				$st_chaine .= sprintf( "Parrain/témoin: %s %s %s\n", self::cp1252_vers_utf8($this -> st_patronyme), self::cp1252_vers_utf8($this -> st_prenom), self::cp1252_vers_utf8($this -> st_commentaire) );
																				 $i_nb_lignes = 1;
																				 } 
																break;
												 case IDF_PRESENCE_MARRAINE:
																 if ( !empty( $this -> st_patronyme ) )
																				 {
																				$st_chaine .= sprintf( "Marraine/témoin: %s %s %s\n", self::cp1252_vers_utf8($this -> st_patronyme), self::cp1252_vers_utf8($this -> st_prenom), self::cp1252_vers_utf8($this -> st_commentaire) );
																				 $i_nb_lignes = 1;
																				 } 
																break;
												 case IDF_PRESENCE_TEMOIN:
																 if ( !empty( $this -> st_patronyme ) )
																				 {
																				$st_chaine .= sprintf( "Témoin: %s %s %s\n", self::cp1252_vers_utf8($this -> st_patronyme), self::cp1252_vers_utf8($this -> st_prenom), self::cp1252_vers_utf8($this -> st_commentaire) );
																				 $i_nb_lignes = 1;
																				 } 
																break;
																 } 
								$this -> i_nb_lignes = $i_nb_lignes;
								 //return self::cp1252_vers_utf8($st_chaine);
								 return $st_chaine;
								 } 
				
				public function versTableauHTML()
				
				
				
				
				{
								 $st_chaine = '<table border=1>';
								 $st_chaine .= sprintf( "<tr><th>Idf</th><td>%d</td>\n", $this -> i_idf );
								 $st_chaine .= sprintf( "<tr><th>Idf Acte</th><td>%d</td>\n", $this -> i_idf_acte );
								 $st_chaine .= sprintf( "<tr><th>Sexe</th><td>%s</td>\n", $this -> c_sexe );
								 $st_chaine .= sprintf( "<tr><th>Patronyme</th><td>%s</td>\n", $this -> st_patronyme );
								 $st_chaine .= sprintf( "<tr><th>Prenom</th><td>%s</td>\n", $this -> st_prenom );
								 $st_chaine .= sprintf( "<tr><th>Idf Type Pr&eacute;sence</th><td>%d</td>\n", $this -> i_idf_type_presence );
								 $st_chaine .= sprintf( "<tr><th>R&eacute;sidence</th><td>%s</td>\n", $this -> st_residence );
								 $st_chaine .= sprintf( "<tr><th>Origine</th><td>%s</td>\n", $this -> st_origine );
								 $st_chaine .= sprintf( "<tr><th>Profession</th><td>%s</td>\n", $this -> st_profession );
								 $st_chaine .= sprintf( "<tr><th>Age</th><td>%s</td>\n", $this -> st_age );
								 $st_chaine .= sprintf( "<tr><th>Dnais</th><td>%s</td>\n", $this -> st_date_naissance );
								 $st_chaine .= sprintf( "<tr><th>Idf P&egrave;re</th><td>%d</td>\n", $this -> i_idf_pere );
								 $st_chaine .= sprintf( "<tr><th>Idf M&egrave;re</th><td>%d</td>\n", $this -> i_idf_mere );
								 $st_chaine .= '</table>';
								 return $st_chaine;
								 } 
				
				/**
				* Rend un prénom propre (Example "JEAn eMILE d'ALENCON" => "Jean Emile D\'alencon")
				*       remplace tous les "-" par des espaces et considère les espaces comme separateurs de champs
				*       apres avoir mis des majuscules a chaque prenom, les recolle en les separant par 1 espace
				* pour finir tous les espaces sont remplaces par un "-"
				* 
				* @param string $pst_prenom 
				*/
				public static function prenom_propre( $pst_prenom )
				
				
				
				
				
				{
								 $pst_prenom = trim($pst_prenom);
								 $a_prns = array_map( "strtolower", preg_split( "/\s/", $pst_prenom ) );
								 $pst_prenom = join( ' ', array_map( "ucfirst", $a_prns ) );
								 $a_prns = array_map( "ucfirst", preg_split( "/-/", $pst_prenom ) );
								 $pst_prenom = join( '-', array_map( "ucfirst", $a_prns ) );
								 return $pst_prenom;
								 } 
				
				/**
				* Rend un nom propre (Example "d'Elbauve" => "D'ELBAUVE")
				* 
				* @param string $pst_nom 
				*/
				public static function patronyme_propre( $pst_nom )
				
				
				
				
				
				{
								 return strtoupper( trim($pst_nom) );
								 } 
				
				/**
				* Nettoie les commentaires et renvoie si la personne est decedee ou non (  presence de † dans le commentaires ou non)
				* 
				* @param string $pst_commentaires commentaire à  nettoyer
				* return integer (0|1)
				*/
				public function commentaire_propre( $pst_commentaires )
				
				
				
				
				
				{
								 $this -> i_est_decede = ( strpos( $pst_commentaires, "†" ) === false ) ? 0 : 1;
								 if ( preg_match( '/dcd/i', $pst_commentaires ) )
												 $this -> i_est_decede = 1;
								 // return addslashes($pst_commentaires);
								return $pst_commentaires;
								 } 
				
				/**
				* Renvoie le contenu de la personne sous la forme d'une ligne SQL à insérer
				* 
				* @return string personne sous forme CSV
				*/
				public function ligne_sql_a_inserer()
				
				
				
				
				{
								 $a_personnes_a_creer[":idf$this->i_idf"] = $this -> i_idf;
								 $a_personnes_a_creer[":idf_acte$this->i_idf"] = $this -> i_idf_acte;
								 $a_personnes_a_creer[":idf_type_presence$this->i_idf"] = $this -> i_idf_type_presence;
								 $a_personnes_a_creer[":sexe$this->i_idf"] = $this -> c_sexe;
								 $a_personnes_a_creer[":patronyme$this->i_idf"] = empty( $this -> st_patronyme ) ? '' : $this -> st_patronyme;
								 $a_personnes_a_creer[":idf_prenom$this->i_idf"] = empty( $this -> st_prenom ) ? null : $this -> prenom -> vers_idf( $this -> st_prenom );
								 $a_personnes_a_creer[":surnom$this->i_idf"] = empty( $this -> st_surnom ) ? null : $this -> st_surnom;
								 $a_personnes_a_creer[":idf_origine$this->i_idf"] = empty( $this -> st_origine ) ? null :$this -> communePersonne -> vers_idf( $this -> st_origine );
								 $a_personnes_a_creer[":idf_residence$this->i_idf"] = empty( $this -> st_residence ) ? null :$this -> communePersonne -> vers_idf( $this -> st_residence );
								 $a_personnes_a_creer[":date_naissance$this->i_idf"] = empty( $this -> st_date_naissance ) ? null : $this -> st_date_naissance;
								 $a_personnes_a_creer[":age$this->i_idf"] = empty( $this -> st_age ) ? null : $this -> st_age ;
								 $a_personnes_a_creer[":idf_profession$this->i_idf"] = empty( $this -> st_profession ) ? null : $this -> profession -> vers_idf( $this -> st_profession );
								 $a_personnes_a_creer[":commentaire$this->i_idf"] = empty( $this -> st_commentaire ) ? null : $this -> st_commentaire;
								 $a_personnes_a_creer[":est_decede$this->i_idf"] = empty( $this -> i_est_decede ) ? null : $this -> i_est_decede;
								 $a_personnes_a_creer[":idf_pere$this->i_idf"] = empty( $this -> i_idf_pere ) ? null : $this -> i_idf_pere;
								 $a_personnes_a_creer[":idf_mere$this->i_idf"] = empty( $this -> i_idf_mere ) ? null : $this -> i_idf_mere;
								
								 return array( "(:idf$this->i_idf,:idf_acte$this->i_idf,:idf_type_presence$this->i_idf,:sexe$this->i_idf,:patronyme$this->i_idf,:idf_prenom$this->i_idf,:surnom$this->i_idf,:idf_origine$this->i_idf,:idf_residence$this->i_idf,:date_naissance$this->i_idf,:age$this->i_idf,:idf_profession$this->i_idf,:commentaire$this->i_idf,:est_decede$this->i_idf,:idf_pere$this->i_idf,:idf_mere$this->i_idf)", $a_personnes_a_creer );
								
								 } 
				
				/**
				* Renvoie la requête de base pour un chargement de personne
				*/
				public static function requete_base()
				
				
				
				
				{
								
								 return "insert INTO `personne` (idf,idf_acte,idf_type_presence,sexe,patronyme,idf_prenom,surnom,idf_origine,idf_residence,date_naissance,age,idf_profession,commentaires,est_decede,idf_pere,idf_mere) values ";
								 } 
				
				/**
				* Renvoie la liste des filtres jquery validator à activer par champ de paramètre
				* 
				* @return array tableau nom du paramètre => (type de filtre, message d'erreur à afficher)
				*/
				public function getFiltresParametres()
				
				
				
				
				
				{
								 return $this -> a_filtres_parametres;
								 } 
				
				/**
				* Renvoie la liste des paramètres avec complètion automatique
				* 
				* @return array tableau nom du paramètre => (nom de la fonction ajax à utiliser pour l'autocomplètion,nombre de caractères minimal)
				*/
				public function parametres_completion_auto()
				
				
				
				
				
				{
								 return $this -> a_parametres_completion_auto;
								 } 
				
				/**
				* Charge la personne à partir de la BD
				* 
				* @param integer $pi_idf_personne identifiant de la personne
				*/
				public function charge( $pi_idf_personne )
				
				
				
				
				
				{
								 $st_requete = "select p.idf_acte,p.idf_type_presence,p.sexe,p.patronyme,prenom.libelle,p.surnom,orig.nom,resid.nom,p.date_naissance,p.age,prof.nom,p.commentaires,p.est_decede,p.idf_pere,p.idf_mere from personne p left join prenom  on (p.idf_prenom=prenom.idf) left join commune_personne orig on (p.idf_origine=orig.idf) left join commune_personne resid on (p.idf_residence=resid.idf) left join profession prof on (p.idf_profession=prof.idf) where p.idf=$pi_idf_personne";
								 list( $i_idf_acte, $i_idf_type_presence, $c_sexe, $st_patronyme, $st_prenom, $st_surnom, $st_origine, $st_residence, $st_dnais, $st_age, $st_profession, $st_commentaire, $i_est_decede, $i_idf_pere, $i_idf_mere ) = $this -> connexionBD -> sql_select_liste( $st_requete );
								 $this -> i_idf = $pi_idf_personne;
								 $this -> i_idf_acte = $i_idf_acte;
								 $this -> i_idf_type_presence = $i_idf_type_presence;
								 $this -> c_sexe = $c_sexe;
								 $this -> st_patronyme = $st_patronyme;
								 $this -> st_prenom = $st_prenom;
								 $this -> st_surnom = $st_surnom;
								 $this -> st_origine = $st_origine;
								 $this -> st_residence = $st_residence;
								 $this -> st_date_naissance = str_replace( ' ', '', $st_dnais );
								 $this -> st_age = $st_age;
								 $this -> st_profession = $st_profession;
								 $this -> st_commentaire = $st_commentaire;
								 $this -> i_idf_pere = $i_idf_pere;
								 $this -> i_idf_mere = $i_idf_mere;
								 } 
				
				/**
				* Cree une nouvelle personne dans la base de données
				* 
				* @return integer identifiant de la personne crée ou null si vide
				* ATTENTION: les communes, professions et prénoms doivent avoir été rechargés auparavant sinon les nouveaux éléments ne seront pas créés
				*/
				public function cree()
				
				
				
				
				
				{
								 $i_idf = $this -> i_idf;
								 $i_idf_acte = $this -> i_idf_acte;
								 $i_idf_type_presence = $this -> i_idf_type_presence;
								 $c_sexe = $this -> c_sexe;
								 $st_patronyme = $this -> st_patronyme;
								 $st_prenom = $this -> st_prenom;
								 if ( empty( $this -> st_patronyme ) && empty( $this -> st_prenom ) )
												 return null;
								 $st_surnom = $this -> st_surnom;
								 $st_date_naissance = $this -> st_date_naissance;
								 $st_age = $this -> st_age;
								 $i_idf_prenom = empty( $this -> st_prenom ) ? 0 : $this -> prenom -> vers_idf( $this -> st_prenom );
								 $i_idf_profession = empty( $this -> st_profession ) ? 0: $this -> profession -> vers_idf( $this -> st_profession );
								 $i_idf_origine = empty( $this -> st_origine ) ? 0 : $this -> communePersonne -> vers_idf( $this -> st_origine );
								 $i_idf_residence = empty( $this -> st_residence ) ? 0 : $this -> communePersonne -> vers_idf( $this -> st_residence);
								 $st_commentaire = $this -> st_commentaire;
								 $i_est_decede = $this -> i_est_decede;
								 $i_idf_pere = $this -> i_idf_pere;
								 $i_idf_mere = $this -> i_idf_mere;
								 $this -> connexionBD -> initialise_params( array( ':idf' => $i_idf, ':idf_acte' => $i_idf_acte, ':idf_type_presence' => $i_idf_type_presence, ':sexe' => $c_sexe, ':patronyme' => $st_patronyme, ':idf_prenom' => $i_idf_prenom, ':surnom' => $st_surnom, ':idf_origine' => $i_idf_origine, ':idf_residence' => $i_idf_residence, ':date_naissance' => $st_date_naissance, ':age' => $st_age, ':idf_profession' => $i_idf_profession, ':commentaire' => $st_commentaire, ':est_decede' => $i_est_decede, ':idf_pere' => $i_idf_pere, ':idf_mere' => $i_idf_mere ) );
								 $st_requete = "insert into personne(idf,idf_acte,idf_type_presence,sexe,patronyme,idf_prenom,surnom,idf_origine,idf_residence,date_naissance,age,idf_profession,commentaires,est_decede,idf_pere,idf_mere) values(:idf,:idf_acte,:idf_type_presence,:sexe,:patronyme,:idf_prenom,:surnom,:idf_origine,:idf_residence,:date_naissance,:age,:idf_profession,:commentaire,:est_decede,:idf_pere,:idf_mere)";
								 $this -> connexionBD -> execute_requete( $st_requete );
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
								 if ( empty( $this -> st_patronyme ) && empty( $this -> st_prenom ) )
												 return null;
								 $st_surnom = $this -> st_surnom;
								 if ( !empty( $this -> st_prenom ) )
												 {
												$this -> prenom -> ajoute( $this -> st_prenom );
												 $this -> prenom -> sauve();
												 $i_idf_prenom = $this -> profession -> vers_idf( $this -> st_prenom );
												 } 
								else
												 $i_idf_prenom = 0;
								 if ( !empty( $this -> st_profession ) )
												 {
												$this -> profession -> ajoute( $this -> st_profession );
												 $this -> profession -> sauve();
												 $i_idf_profession = $this -> profession -> vers_idf( $this -> st_profession );
												 } 
								else
												 $i_idf_profession = 0;
								 if ( !empty( $this -> st_origine ) )
												 {
												$this -> communePersonne -> ajoute( $this -> st_origine);
												 $this -> communePersonne -> sauve();
												 $i_idf_origine = $this -> communePersonne -> vers_idf( $this -> st_origine );
												 } 
								else
												 $i_idf_origine = 0;
								 if ( !empty( $this -> st_residence ) )
												 {
												$this -> communePersonne -> ajoute( $this -> st_residence );
												 $this -> communePersonne -> sauve();
												 $i_idf_residence = $this -> communePersonne -> vers_idf( $this -> st_residence );
												 } 
								else
												 $i_idf_residence = 0;
								 $st_date_naissance = $this -> st_date_naissance;
								 $st_age = $this -> st_age;
								 $i_est_decede = $this -> i_est_decede;
								 $i_idf_pere = $this -> i_idf_pere;
								 $i_idf_mere = $this -> i_idf_mere;
								 $this -> connexionBD -> initialise_params( array( ':idf' => $i_idf, ':idf_acte' => $i_idf_acte, ':idf_type_presence' => $i_idf_type_presence, ':sexe' => $c_sexe, ':patronyme' => $st_patronyme, ':idf_prenom' => $i_idf_prenom, ':surnom' => $st_surnom, ':idf_origine' => $i_idf_origine, ':idf_residence' => $idf_residence, ':date_naissance' => $st_date_naissance, ':age' => $st_age, ':idf_profession' => $i_idf_profession, ':commentaire' => $st_commentaire, ':est_decede' => $i_est_decede, ':idf_pere' => $i_idf_pere, ':idf_mere' => $i_idf_mere ) );
								
								 $st_requete = "update personne set idf_acte=:idf_acte,idf_type_presence=:idf_type_presence,sexe=:sexe,patronyme=:patronyme,idf_prenom=:idf_prenom,surnom=:surnom,idf_origine=:idf_origine,idf_residence=:idf_residence,date_naissance=:date_naissance,age=:age,idf_profession=:idf_profession,commentaires=:commentaire,est_decede=:est_decede,idf_pere=$:idf_pere,idf_mere=:idf_mere where idf=:idf";
								 $this -> connexionBD -> execute_requete( $st_requete );
								 return $i_idf;
								 } 
				
				/**
				* Initialise la personne depuis une formulaire POST
				* 
				* @param integer $pi_idf_acte identifiant de l'acte
				* @param integer $pi_pi_idf_type_presence type de prèsence
				*/
				public function initialise_depuis_formulaire( $pi_idf_acte, $pi_pi_idf_type_presence )
				
				
				
				
				
				{
								 $this -> i_idf_acte = $pi_idf_acte;
								 $this -> i_idf_type_presence = $pi_pi_idf_type_presence;
								 $i_num_parametre = $this -> i_num_param;

								if ( isset( $_POST["sexe$i_num_parametre"] ) ) $this -> c_sexe = substr( trim( $_POST["sexe$i_num_parametre"] ), 0, 1 );
								$this -> st_patronyme = isset( $_POST["patro$i_num_parametre"] )?substr( trim( $_POST["patro$i_num_parametre"] ), 0, 30 ):'';
								$this -> st_prenom = isset( $_POST["prn$i_num_parametre"] )?substr( trim( $_POST["prn$i_num_parametre"] ), 0, 30 ):'';
								$this -> st_surnom = isset( $_POST["surnom$i_num_parametre"] )?substr( trim( $_POST["surnom$i_num_parametre"] ), 0, 30 ):'';
								$this -> st_origine = isset( $_POST["orig$i_num_parametre"] )?substr( trim( $_POST["orig$i_num_parametre"] ), 0, 50 ):'';
								$this -> st_residence = isset( $_POST["residence$i_num_parametre"] )?substr( trim( $_POST["residence$i_num_parametre"] ), 0, 50 ):'';
								$this -> st_date_naissance = isset( $_POST["dnais$i_num_parametre"] )?substr( trim( $_POST["dnais$i_num_parametre"] ), 0, 10 ):'';
								$this -> st_age = !empty( $_POST["age$i_num_parametre"] )? substr( trim( $_POST["age$i_num_parametre"] ), 0, 15 ):'';
								$this -> st_profession = isset( $_POST["prof$i_num_parametre"] )?substr( trim( $_POST["prof$i_num_parametre"] ), 0, 35 ):'';
								$this -> st_commentaire = isset( $_POST["cmt$i_num_parametre"] )?substr( trim( $_POST["cmt$i_num_parametre"] ), 0, 70 ):'';

								$this -> st_patronyme =self::utf8_vers_cp1252($this -> st_patronyme);
								$this -> st_patronyme = self :: patronyme_propre( $this -> st_patronyme );
								 
								 $this -> st_prenom =self::utf8_vers_cp1252($this -> st_prenom);
								 $this -> st_prenom = self :: prenom_propre( $this -> st_prenom );
								 $this -> prenom -> ajoute( $this -> st_prenom );
								 $this -> st_profession =self::utf8_vers_cp1252($this -> st_profession);
								 $this -> profession -> ajoute( $this -> st_profession );
								 $this -> st_origine =self::utf8_vers_cp1252($this -> st_origine);
								 $this -> communePersonne -> ajoute( $this -> st_origine );
								 $this -> st_residence =self::utf8_vers_cp1252($this -> st_residence);
								 $this -> communePersonne -> ajoute( $this -> st_residence );
								 // met à jour le champ est_decede en même temps que le commentaire
								 $this -> st_commentaire =self::utf8_vers_cp1252($this -> st_commentaire);
								$this -> st_commentaire = self :: commentaire_propre( $this -> st_commentaire );
								 
								 if ( empty( $this -> st_patronyme ) && ( !empty( $this -> st_prenom ) || !empty( $this -> st_commentaire ) ) )
												 $this -> st_patronyme = LIB_MANQUANT;
								 } 
				
				/**
				* Renvoie un formulaire HTML d'édition d'une personne
				* 
				* @param integer $pi_idf_type_acte identifiant du type d'acte
				* @param string $pst_commune commune de l'acte
				* @param integer $pi_idf_patro_intv identifiant jquery de l'intervenant (pour recopie du patronyme vers les parents)
				*/
				public function formulaire_personne ( $pi_idf_type_acte, $pst_commune, $pi_idf_patro_intv )
				
				
				
				
				
				{
								 global $ga_sexe, $ga_mois_revolutionnaires, $ga_annees_revolutionnaires, $ga_mois_revolutionnaires_nimegue;
								 $i_num_parametre = $this -> i_num_param;
								 $st_chaine = '';
								 switch ( $this -> i_idf_type_presence )
								 {
												case IDF_PRESENCE_INTV:
																
																 switch ( $pi_idf_type_acte )
																 {
																				case IDF_NAISSANCE:
																								 $st_chaine = "<tr>";
																								 $st_chaine .= sprintf( "<th>Patronyme</th><td><input type=text name=\"patro$i_num_parametre\" id=\"patro$i_num_parametre\" value=\"%s\" maxlength=30 class=\"form-control text-uppercase form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_patronyme));
																								 $st_chaine .= sprintf( "<th>Pr&eacute;nom</th><td><input type=text id=\"prn$i_num_parametre\" name=\"prn$i_num_parametre\" value=\"%s\" maxlength=35 class=\"form-control text-capitalize form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_prenom) );
																								 $st_chaine .= "<th>Sexe</th><td><select name=sexe$i_num_parametre class=\"form-control\">";
																								 $st_chaine .= chaine_select_options( $this -> c_sexe, $ga_sexe );
																								 $st_chaine .= "</select></td>";
																								 $st_chaine .= sprintf( "<th>Commentaires</th><td><input type=text id=\"cmt$i_num_parametre\" name=\"cmt$i_num_parametre\" value=\"%s\" maxlength=70 class=\"form-control form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_commentaire ));
																								 $st_chaine .= "</tr>\n";
																								 $this -> a_filtres_parametres["patro$i_num_parametre"] = array( array( "required", "true", "Le patronyme est obligatoire" ) );
																								 $this -> a_parametres_completion_auto["patro$i_num_parametre"] = array( 'patronyme.php', 3 );
																								 break;
																				 default:
																								
																								/**
																								* la structure de personne est la même pour ces 3 types d'acte
																								*/
																								 $st_chaine = "<tr>";
																								 $st_chaine .= sprintf( "<th>Patronyme</th><td class=\"lib_erreur\"><input type=text name=\"patro$i_num_parametre\" id=\"patro$i_num_parametre\" value=\"%s\" maxlength=30 class=\"form-control text-uppercase col-md-3 form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_patronyme) );
																								
																								 $st_chaine .= sprintf( "<th>Pr&eacute;nom</th><td><input type=text name=\"prn$i_num_parametre\" id=\"prn$i_num_parametre\"  value=\"%s\" maxlength=35 class=\"form-control text-capitalize col-md-3 form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_prenom) );
																								
																								 $st_chaine .= sprintf( "<th>Profession</th><td><input type=text name=\"prof$i_num_parametre\" id=\"prof$i_num_parametre\" value=\"%s\" maxlength=30 class=\"form-control form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_profession) );
																								 $st_chaine_deces = ( $pi_idf_type_acte == IDF_DECES ) ? "<button type=\"button\" data-cible=\"#cmt$i_num_parametre\" class=\"btn btn-primary maj_deces\">&dagger;</button>" : '';
																								 $st_chaine .= sprintf( "<th>Commentaires</th><td><input type=text id=\"cmt$i_num_parametre\" name=\"cmt$i_num_parametre\" value=\"%s\" maxlength=70 class=\"form-control form-control-xs\">%s</td>", self::cp1252_vers_utf8($this -> st_commentaire), $st_chaine_deces );
																								 $st_chaine .= "</tr><tr>";
																								 $st_chaine .= sprintf( "<th><a class=\"recopie_commune btn btn-info btn-xs\" data-source=\"".self::cp1252_vers_utf8($pst_commune)."\" data-cible=\"#orig$i_num_parametre\" ><span class=\"glyphicon glyphicon-copy\"></span> Lieu<br>d'origine</a></th><td><input type=text name=\"orig$i_num_parametre\"  id=\"orig$i_num_parametre\"  value=\"%s\" maxlength=50 class=\"form-control form-control-xs\">", self::cp1252_vers_utf8($this -> st_origine) );
																								
																								 $st_chaine .= "</td>";
																								 $st_chaine .= $pi_idf_type_acte == IDF_MARIAGE ? "<th>Sexe</th><td><select name=sexe$i_num_parametre disabled class=\"form-control\">" : "<th>Sexe</th><td><select name=sexe$i_num_parametre class=\"form-control form-control-xs\">";
																								 $st_chaine .= chaine_select_options( $this -> c_sexe, $ga_sexe );
																								 $st_chaine .= "</select></td>";
																								 $st_chaine .= sprintf( "<th>Age</th><td class=\"lib_erreur\"><input type=text name=\"age$i_num_parametre\" id=\"age$i_num_parametre\" value=\"%s\" maxlength=15 class=\"form-control form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_age ));
																								 $st_chaine .= "<th>Date &deg;</th><td class=\"lib_erreur\">";
																								 $i_jour_rep = null;
																								 $i_mois_rep = null;
																								 $i_annee_rep = null;
																								 if ( !empty( $this -> st_date_naissance ) )
																												 {
																												list( $i_jour_rep, $st_mois_rep, $st_annee_rep ) = explode( '/', $this -> st_date_naissance, 3 );
																												 $a_mois_rep_nim_vers_entier = array_flip( $ga_mois_revolutionnaires_nimegue );
																												 $i_mois_rep = array_key_exists( strtolower( $st_mois_rep ), $a_mois_rep_nim_vers_entier ) ? $a_mois_rep_nim_vers_entier[strtolower( $st_mois_rep )]: null;
																												 $i_annee_rep = ( int ) $st_annee_rep;
																												 } 
																								$st_chaine_date_rep = '<div class="row form-group">';
																								 $st_chaine_date_rep .= '<div class="col-xs-2">';
																								 $st_chaine_date_rep .= "<input type=\"text\" name=\"jour_rep\" id=\"jour_rep$i_num_parametre\"  size=\"2\" maxlength=\"2\" value=\"$i_jour_rep\" class=\"form-control\">";
																								 $st_chaine_date_rep .= '</div>';
																								 $st_chaine_date_rep .= '<div class="col-xs-4">';
																								 $st_chaine_date_rep .= "<select name=\"mois_rep\" id=\"mois_rep$i_num_parametre\" class=\"form-control\">";
																								 $st_chaine_date_rep .= '<option value=""></option>';
																								 $st_chaine_date_rep .= chaine_select_options( $i_mois_rep, $ga_mois_revolutionnaires, false);
																								 $st_chaine_date_rep .= '</select>';
																								 $st_chaine_date_rep .= '</div>';
																								 $st_chaine_date_rep .= '<div class="col-xs-2">';
																								 $st_chaine_date_rep .= " <select name=\"annee_rep\" id=\"annee_rep$i_num_parametre\" class=\"form-control\">";
																								 $st_chaine_date_rep .= '<option value=""></option>';
																								 $st_chaine_date_rep .= chaine_select_options( $i_annee_rep, $ga_annees_revolutionnaires, false );
																								 $st_chaine_date_rep .= '</select>';
																								 $st_chaine_date_rep .= '</div>';
																								 $st_chaine_date_rep .= "<button type=\"button\" class=\"maj_date_rep btn btn-primary col-xs-4\" data-jour_rep=\"#jour_rep$i_num_parametre\" data-mois_rep=\"#mois_rep$i_num_parametre\" data-annee_rep=\"#annee_rep$i_num_parametre\" data-date_greg=\"#dnais$i_num_parametre\" data-date_rep=\"\" data-cmt=\"#cmt$i_num_parametre\" data-id_fenetre=\"#popup_dnais$i_num_parametre\">Maj date naissance</button>";
																								 $st_chaine_date_rep .= '</div>';
																								 // Contenu du popup
																								$st_chaine .= sprintf( "<div class=\"popup_date_rep\" id=\"popup_dnais%d\" title=\"Fenetre\">%s</div>", $i_num_parametre, $st_chaine_date_rep );
																								 $st_chaine .= sprintf( "<div class=\"btn-group-vertical\"><input type=text name=\"dnais%d\" id=\"dnais%d\" value=\"%s\" maxlength=10 class=\"form-control form-control-xs\">", $i_num_parametre, $i_num_parametre, $this -> st_date_naissance );
																								 // Bouton d'ouverture du popup
																								$st_chaine .= sprintf( "<button type=\"button\" class=\"ouvre_popup btn btn-primary btn-xs\" data-id_fenetre=\"#popup_dnais%d\"><span class=\"glyphicon glyphicon-calendar\"></span>  Saisir une date r&eacute;publicaine</button>", $i_num_parametre );
																								 $st_chaine .= "</div></td></tr>\n";
																								 $this -> a_filtres_parametres["patro$i_num_parametre"] = array( array( "required", "true", "Le patronyme est obligatoire" ) );
																								 if ( $pi_idf_type_acte != IDF_DECES )
																												 $this -> a_filtres_parametres["age$i_num_parametre"] = array( array( "number", "true", "L'âge doit être un entier" ) );
																								 $this -> a_filtres_parametres["dnais$i_num_parametre"] = array( array( "dateITA", "true", "La date de naissance est de la forme JJ/MM/AAAA" ) );
																								 $this -> a_parametres_completion_auto["patro$i_num_parametre"] = array( 'patronyme.php', 3 );
																								 $this -> a_parametres_completion_auto["prof$i_num_parametre"] = array( 'profession.php', 4 );
																								 $this -> a_parametres_completion_auto["orig$i_num_parametre"] = array( 'commune_acte_saisie.php', 3 );
																								 } 
																break;
												 case IDF_PRESENCE_PERE:
												 case IDF_PRESENCE_MERE:
												 case IDF_PRESENCE_EXCJT:
																 switch ( $this -> i_idf_type_presence )
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
																$st_chaine = "<tr>";
																 $st_chaine .= sprintf( "<th><a class=\"recopie_patro btn btn-info btn-xs\" data-source=\"$pi_idf_patro_intv\" data-cible=\"#patro$i_num_parametre\" role=\"button\"><span class=\"glyphicon glyphicon-copy\"></span>  Patronyme<br>%s</a></th><td><input type=text name=\"patro$i_num_parametre\" id=\"patro$i_num_parametre\" value=\"%s\"  maxlength=30 class=\"form-control text-uppercase form-control-xs\"></td>", $st_lib, self::cp1252_vers_utf8($this -> st_patronyme ));
																 $st_chaine .= sprintf( "<th>Pr&eacute;nom</th><td><input type=text name=\"prn$i_num_parametre\" id=\"prn$i_num_parametre\" value=\"%s\" maxlength=35 class=\"form-control text-capitalize form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_prenom) );
																 $st_chaine .= sprintf( "<th>Profession</th><td><input type=text name=\"prof$i_num_parametre\" id=\"prof$i_num_parametre\" value=\"%s\" maxlength=30 class=\"form-control\"></td>", self::cp1252_vers_utf8($this -> st_profession ));
																 $st_chaine .= '<th>Commentaires</th>';
																 $st_chaine .= sprintf( "<td><div class=\"input-group\"><label for=\"cmt$i_num_parametre\" class=\"sr-only\">Commentaires</label><input type=text id=\"cmt$i_num_parametre\" name=\"cmt$i_num_parametre\" value=\"%s\" maxlength=70 class=\"form-control form-control-xs\"><span class=\"input-group-btn\"><button type=\"button\" class=\"maj_deces btn btn-primary\" data-cible=\"#cmt$i_num_parametre\">&dagger;</button></span></div></td>", self::cp1252_vers_utf8($this -> st_commentaire) );
																 $st_chaine .= "</tr>\n";
																 $this -> a_parametres_completion_auto["patro$i_num_parametre"] = array( 'patronyme.php', 3 );
																 $this -> a_parametres_completion_auto["prof$i_num_parametre"] = array( 'profession.php', 4 );
																 break;
												 case IDF_PRESENCE_PARRAIN:
												 case IDF_PRESENCE_MARRAINE:
												 case IDF_PRESENCE_TEMOIN:
																/**
																* la structure de personne est la même pour ces 3 types de prèsence
																*/
																 switch ( $this -> i_idf_type_presence )
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
																$st_chaine = "<tr>";
																 $st_chaine .= sprintf( "<th>Patronyme %s</th><td><input type=text name=\"patro$i_num_parametre\" id=\"patro$i_num_parametre\" value=\"%s\"  maxlength=30 class=\"form-control text-uppercase form-control-xs\"></td>", $st_lib, self::cp1252_vers_utf8($this -> st_patronyme));
																 $st_chaine .= sprintf( "<th>Pr&eacute;nom</th><td><input type=text name=\"prn$i_num_parametre\" id=\"prn$i_num_parametre\" value=\"%s\" maxlength=35 class=\"form-control text-capitalize form-control-xs\"></td>", self::cp1252_vers_utf8($this -> st_prenom ));
																 $st_chaine .= sprintf( "<th>Commentaires</th><td colspan=5><div class=\"input-group\"><label for=\"cmt$i_num_parametre\" class=\"sr-only\">Commentaires</label><input type=text id=\"cmt$i_num_parametre\" name=\"cmt$i_num_parametre\" value=\"%s\" maxlength=70 class=\"form-control form-control-xs\"><span class=\"input-group-btn\"><button type=\"button\" class=\"maj_deces btn btn-primary\" data-cible=\"#cmt$i_num_parametre\">&dagger;</button></span></div></td>", self::cp1252_vers_utf8($this -> st_commentaire) );
																 $st_chaine .= "</tr>\n";
																 $this -> a_parametres_completion_auto["patro$i_num_parametre"] = array( 'patronyme.php', 3 );
																 } 
								return $st_chaine;
								 } 
				
				/**
				* Renvoie la personne au format Nimègue V3
				* 
				* @param integer $pi_idf_type_acte identifiant du type d'acte
				* @return array tableau des colonnes
				*/
				
				public function colonnes_nimv3( $pi_idf_type_acte )
				
				
				
				
				
				{
								 switch ( $this -> i_idf_type_presence )
								 {
												case IDF_PRESENCE_INTV:
																 switch ( $pi_idf_type_acte )
																 {
																				case IDF_NAISSANCE:
																								 return array( $this -> st_patronyme, $this -> st_prenom, $this -> c_sexe, $this -> st_commentaire );
																								 break;
																				 case IDF_MARIAGE:
																								 return array( $this -> st_patronyme, $this -> st_prenom, $this -> st_origine, $this -> st_date_naissance, $this -> st_age, $this -> st_commentaire, $this -> st_profession );
																								 break;
																				 case IDF_DECES:
																								 return array( $this -> st_patronyme, $this -> st_prenom, $this -> st_origine, $this -> st_date_naissance, $this -> c_sexe, $this -> st_age, $this -> st_commentaire, $this -> st_profession );
																								 default:
																								 // acte divers
																								return array( $this -> st_patronyme, $this -> st_prenom, $this -> c_sexe, $this -> st_origine, $this -> st_date_naissance, $this -> st_age, $this -> st_commentaire, $this -> st_profession );
																								 } 
																break;
												 case IDF_PRESENCE_PERE:
												 case IDF_PRESENCE_MERE:
																 return array( $this -> st_patronyme, $this -> st_prenom, $this -> st_commentaire, $this -> st_profession );
																 break;
												 case IDF_PRESENCE_EXCJT:
																 switch ( $pi_idf_type_acte )
																 {
																				case IDF_DECES:
																								 return array( $this -> st_patronyme, $this -> st_prenom, $this -> st_commentaire, $this -> st_profession );
																								 break;
																				 default:
																								 return array( $this -> st_patronyme, $this -> st_prenom, $this -> st_commentaire );
																								 } 
																break;
												 case IDF_PRESENCE_PARRAIN:
												 case IDF_PRESENCE_MARRAINE:
												 case IDF_PRESENCE_TEMOIN:
																 return array( $this -> st_patronyme, $this -> st_prenom, $this -> st_commentaire );
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
								 switch ( $this -> i_idf_type_presence )
								 {
												case IDF_PRESENCE_INTV:
																 return !empty( $this -> st_origine ) || !empty( $this -> st_date_naissance ) || !empty( $this -> st_age ) || ! empty( $this -> st_profession ) || !empty( $this -> st_commentaire );
																 break;
												 default:
																 return !empty( $this -> st_patronyme ) || !empty( $this -> st_prenom ) || ! empty( $this -> st_profession ) || !empty( $this -> st_commentaire );
																 } 
								} 
				
				/**
				* Initialise l'objet à partir des variables de sessions
				*/
				public function intialise_variables_sessions ()
				
				
				
				
				
				{
								 $i_num_parametre = $this -> i_num_param;
								 if ( isset( $this -> c_sexe ) ) $_SESSION["sexe$i_num_parametre"] = $this -> c_sexe;
								 if ( isset( $this -> st_patronyme ) ) $_SESSION["patro$i_num_parametre"] = $this -> st_patronyme;
								 if ( isset( $this -> st_prenom ) ) $_SESSION["prenom$i_num_parametre"] = $this -> st_prenom;
								 if ( isset( $this -> st_surnom ) ) $_SESSION["surnom$i_num_parametre"] = $this -> st_surnom;
								 if ( isset( $this -> st_origine ) ) $_SESSION["orig$i_num_parametre"] = $this -> st_origine;
								 if ( isset( $this -> st_residence ) ) $_SESSION["residence$i_num_parametre"] = $this -> st_residence;
								 if ( isset( $this -> st_date_naissance ) ) $_SESSION["dnais$i_num_parametre"] = $this -> st_date_naissance;
								 if ( isset( $this -> st_age ) ) $_SESSION["age$i_num_parametre"] = $this -> st_age;
								 if ( isset( $this -> st_profession ) ) $_SESSION["prof$i_num_parametre"] = $this -> st_profession;
								 if ( isset( $this -> st_commentaire ) ) $_SESSION["cmt$i_num_parametre"] = $this -> st_commentaire;
								 } 
				
				/**
				* Charge l'objet à partir des variables de session
				*/
				public function charge_variables_sessions()
				
				
				
				
				
				{
								 $i_num_parametre = $this -> i_num_param;
								 $this -> c_sexe = isset( $_SESSION["sexe$i_num_parametre"] ) ? $_SESSION["sexe$i_num_parametre"] : $this -> c_sexe;
								 $this -> st_patronyme = isset( $_SESSION["patro$i_num_parametre"] ) ? $_SESSION["patro$i_num_parametre"] : $this -> st_patronyme;
								 $this -> st_prenom = isset( $_SESSION["prenom$i_num_parametre"] ) ? $_SESSION["prenom$i_num_parametre"] : $this -> st_prenom;
								 $this -> st_surnom = isset( $_SESSION["surnom$i_num_parametre"] ) ? $_SESSION["surnom$i_num_parametre"] : $this -> st_surnom;
								 $this -> st_origine = isset( $_SESSION["orig$i_num_parametre"] ) ? $_SESSION["orig$i_num_parametre"] : $this -> st_origine;
								 $this -> st_residence = isset( $_SESSION["residence$i_num_parametre"] ) ? $_SESSION["residence$i_num_parametre"] : $this -> st_residence;
								 $this -> st_date_naissance = isset( $_SESSION["dnais$i_num_parametre"] ) ? $_SESSION["dnais$i_num_parametre"] : $this -> st_date_naissance;
								 $this -> st_age = isset( $_SESSION["age$i_num_parametre"] ) ? $_SESSION["age$i_num_parametre"] : $this -> st_age;
								 $this -> st_profession = isset( $_SESSION["prof$i_num_parametre"] ) ?$_SESSION["prof$i_num_parametre"] : $this -> st_profession;
								 $this -> st_commentaire = isset( $_SESSION["cmt$i_num_parametre"] ) ?$_SESSION["cmt$i_num_parametre"] : $this -> st_commentaire;
								 } 
				
				/**
				* Supprime les variables de session
				*/
				public function detruit_variables_sessions()
				
				
				
				
				
				{
								 $i_num_parametre = $this -> i_num_param;
								 if ( isset( $_SESSION["sexe$i_num_parametre"] ) ) unset( $_SESSION["sexe$i_num_parametre"] );
								 if ( isset( $_SESSION["patro$i_num_parametre"] ) ) unset( $_SESSION["patro$i_num_parametre"] );
								 if ( isset( $_SESSION["prenom$i_num_parametre"] ) ) unset( $_SESSION["prenom$i_num_parametre"] );
								 if ( isset( $_SESSION["surnom$i_num_parametre"] ) ) unset( $_SESSION["surnom$i_num_parametre"] );
								 if ( isset( $_SESSION["orig$i_num_parametre"] ) ) unset( $_SESSION["orig$i_num_parametre"] );
								 if ( isset( $_SESSION["residence$i_num_parametre"] ) ) unset( $_SESSION["residence$i_num_parametre"] );
								 if ( isset( $_SESSION["dnais$i_num_parametre"] ) ) unset( $_SESSION["dnais$i_num_parametre"] );
								 if ( isset( $_SESSION["age$i_num_parametre"] ) ) unset( $_SESSION["age$i_num_parametre"] );
								 if ( isset( $_SESSION["prof$i_num_parametre"] ) ) unset( $_SESSION["prof$i_num_parametre"] );
								 if ( isset( $_SESSION["cmt$i_num_parametre"] ) ) unset( $_SESSION["cmt$i_num_parametre"] );
								 } 
				
				} 
?>