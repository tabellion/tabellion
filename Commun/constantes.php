<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

// types de presence
define('IDF_PRESENCE_INTV', 1);
define('IDF_PRESENCE_TEMOIN', 2);
define('IDF_PRESENCE_PARRAIN', 3);
define('IDF_PRESENCE_MARRAINE', 4);
define('IDF_PRESENCE_EXCJT', 5);
define('IDF_PRESENCE_PERE', 6);
define('IDF_PRESENCE_MERE', 7);

// types d'acte
define('IDF_MARIAGE', 1);
define('IDF_DIVERS', 2);
define('IDF_NAISSANCE', 3);
define('IDF_DECES', 4);
define('IDF_UNION', 6);
define('IDF_CM', 2);
define('IDF_RECENS', 147);

// libelles
define('LIB_MARIAGE', 'Mariage');
define('LIB_DECES', 'Sépulture/Décès');
define('LIB_NAISSANCE', 'Baptême/Naissance');
define('LIB_RECENSEMENT', 'Recensement');

define('SEP_CSV', ';');
define('FDL_CSV', '\n');

$ga_sexe = array('M' => 'M', 'F' => 'F', '?' => '?');
$ga_types_nimegue = array(IDF_NAISSANCE => LIB_NAISSANCE, IDF_MARIAGE => LIB_MARIAGE, IDF_DECES => LIB_DECES, IDF_DIVERS => 'Divers(CM,...)', IDF_RECENS => LIB_RECENSEMENT);

define('LIB_MANQUANT', '!');

define('DROIT_CHARGEMENT', 'CHGMT_EXPT');
define('DROIT_CREATION_ADHERENT', 'CREATADH');
define('DROIT_MODIFICATION_ADHERENT', 'MODADH');
define('DROIT_GESTION_ADHERENT', 'GESTADHT');
define('DROIT_MODIFICATION_DROITS', 'GESTDROITS');
define('DROIT_UTILITAIRES', 'UTILITAIRE');
define('DROIT_VARIANTES', 'VARIANTES');
define('DROIT_PUBLICATION', 'PUBLICATIO');
define('DROIT_RELEVES', 'RELEVES');
define('DROIT_NOTAIRES', 'NOTAIRES');
define('DROIT_VALIDATION_TD', 'TD');
define('DROIT_STATS', 'STATS');
define('DROIT_VALIDATION_PERMALIEN', 'PERMALIEN');
define('DROIT_GENEABANK', 'GENEABANK');
define('DROIT_CONSULT_NOT', 'LNOTCONSUL');


$ga_droits =  array(
    DROIT_CHARGEMENT => 'Chargement/Export',
    DROIT_GESTION_ADHERENT => 'Gestion Adherent',
    DROIT_MODIFICATION_DROITS => 'Modification Droits Adherent',
    DROIT_UTILITAIRES => 'Acces aux utilitaires',
    DROIT_VARIANTES => 'Gestion des variantes',
    DROIT_PUBLICATION => 'Gestion des publications',
    DROIT_RELEVES => 'Suivi des relevés',
    DROIT_NOTAIRES => 'Edition des liasses notariales',
    DROIT_VALIDATION_TD => 'Validation des modifications de TD',
    DROIT_STATS => 'Acces aux statistiques',
    DROIT_VALIDATION_PERMALIEN => 'Validation des modifications de permalien',
    DROIT_GENEABANK => 'Gestion des points GeneaBank',
    DROIT_CONSULT_NOT => 'Consultation des notaires'
);
define('NB_LIGNES_PAR_PAGE', 25);
define('DELTA_NAVIGATION', 5);

define('IDF_ASSO_GBK', 'agcharente');
define('PREFIXE_ADH_GBK', 'AGC');
define('NB_POINTS_GBK', 100);
define('CODE_DPT_GENEABANK', 'F16');
define('CODE_REGION_GENEABANK', 'PCH');
define('CODE_PAYS_GENEABANK', 'FRA');
define('PAYS_GENEABANK', 'France');
define('CODE_TYPE_GENEABANK', 'C');

define('TOUS_ADHERENTS', 'T');
define('ADHESION_BULLETIN', 'B');
define('ADHESION_INTERNET', 'I');
define('ADHESION_HONNEUR', 'H');
define('ADHESION_GRATUIT', 'G');
define('ADHESION_PARIS', 'P');
define('ADHESION_SUSPENDU', 'S');

$ga_pays = array(
    "Afghanistan",
    "Afrique_Centrale",
    "Afrique_du_sud",
    "Albanie",
    "Algerie",
    "Allemagne",
    "Andorre",
    "Angola",
    "Anguilla",
    "Arabie_Saoudite",
    "Argentine",
    "Armenie",
    "Australie",
    "Autriche",
    "Azerbaidjan",

    "Bahamas",
    "Bangladesh",
    "Barbade",
    "Bahrein",
    "Belgique",
    "Belize",
    "Benin",
    "Bermudes",
    "Bielorussie",
    "Bolivie",
    "Botswana",
    "Bhoutan",
    "Boznie_Herzegovine",
    "Bresil",
    "Brunei",
    "Bulgarie",
    "Burkina_Faso",
    "Burundi",

    "Caiman",
    "Cambodge",
    "Cameroun",
    "Canada",
    "Canaries",
    "Cap_vert",
    "Chili",
    "Chine",
    "Chypre",
    "Colombie",
    "Comores",
    "Congo",
    "Congo_democratique",
    "Cook",
    "Coree_du_Nord",
    "Coree_du_Sud",
    "Costa_Rica",
    "Cote_d_Ivoire",
    "Croatie",
    "Cuba",

    "Danemark",
    "Djibouti",
    "Dominique",

    "Egypte",
    "Emirats_Arabes_Unis",
    "Equateur",
    "Erythree",
    "Espagne",
    "Estonie",
    "Etats_Unis",
    "Ethiopie",

    "Falkland",
    "Feroe",
    "Fidji",
    "Finlande",
    "France",

    "Gabon",
    "Gambie",
    "Georgie",
    "Ghana",
    "Gibraltar",
    "Grece",
    "Grenade",
    "Groenland",
    "Guadeloupe",
    "Guam",
    "Guatemala",
    "Guernesey",
    "Guinee",
    "Guinee_Bissau",
    "Guinee equatoriale",
    "Guyana",
    "Guyane_Francaise ",

    "Haiti",
    "Hawaii",
    "Honduras",
    "Hong_Kong",
    "Hongrie",

    "Inde",
    "Indonesie",
    "Iran",
    "Iraq",
    "Irlande",
    "Islande",
    "Israel",
    "Italie",

    "Jamaique",
    "Jan Mayen",
    "Japon",
    "Jersey",
    "Jordanie",

    "Kazakhstan",
    "Kenya",
    "Kirghizstan",
    "Kiribati",
    "Koweit",

    "Laos",
    "Lesotho",
    "Lettonie",
    "Liban",
    "Liberia",
    "Liechtenstein",
    "Lituanie",
    "Luxembourg",
    "Lybie",

    "Macao",
    "Macedoine",
    "Madagascar",
    "Madère",
    "Malaisie",
    "Malawi",
    "Maldives",
    "Mali",
    "Malte",
    "Man",
    "Mariannes du Nord",
    "Maroc",
    "Marshall",
    "Martinique",
    "Maurice",
    "Mauritanie",
    "Mayotte",
    "Mexique",
    "Micronesie",
    "Midway",
    "Moldavie",
    "Monaco",
    "Mongolie",
    "Montserrat",
    "Mozambique",

    "Namibie",
    "Nauru",
    "Nepal",
    "Nicaragua",
    "Niger",
    "Nigeria",
    "Niue",
    "Norfolk",
    "Norvege",
    "Nouvelle_Caledonie",
    "Nouvelle_Zelande",

    "Oman",
    "Ouganda",
    "Ouzbekistan",

    "Pakistan",
    "Palau",
    "Palestine",
    "Panama",
    "Papouasie_Nouvelle_Guinee",
    "Paraguay",
    "Pays_Bas",
    "Perou",
    "Philippines",
    "Pologne",
    "Polynesie",
    "Porto_Rico",
    "Portugal",

    "Qatar",

    "Republique_Dominicaine",
    "Republique_Tcheque",
    "Reunion",
    "Roumanie",
    "Royaume_Uni",
    "Russie",
    "Rwanda",

    "Sahara Occidental",
    "Sainte_Lucie",
    "Saint_Marin",
    "Salomon",
    "Salvador",
    "Samoa_Occidentales",
    "Samoa_Americaine",
    "Sao_Tome_et_Principe",
    "Senegal",
    "Seychelles",
    "Sierra Leone",
    "Singapour",
    "Slovaquie",
    "Slovenie",
    "Somalie",
    "Soudan",
    "Sri_Lanka",
    "Suede",
    "Suisse",
    "Surinam",
    "Swaziland",
    "Syrie",

    "Tadjikistan",
    "Taiwan",
    "Tonga",
    "Tanzanie",
    "Tchad",
    "Thailande",
    "Tibet",
    "Timor_Oriental",
    "Togo",
    "Trinite_et_Tobago",
    "Tristan da cunha",
    "Tunisie",
    "Turkmenistan",
    "Turquie",

    "Ukraine",
    "Uruguay",

    "Vanuatu",
    "Vatican",
    "Venezuela",
    "Vierges_Americaines",
    "Vierges_Britanniques",
    "Vietnam",

    "Wake",
    "Wallis et Futuma",

    "Yemen",
    "Yougoslavie",

    "Zambie",
    "Zimbabwe",
);

$ga_scripts_demande = array('InfosActe.php', 'InfosAGL.php', 'InfosChabatz.php', 'InfosCGSS.php', 'InfosRepNot.php', 'InfosTD.php');
$ga_icones_source = array('infos.png', 'ninfos.png', 'td.png', 'tdv.png', 'agl.png', 'nagl.png', 'rnot.png', 'RGD.png', 'chabatz.png', 'nchabatz.png', 'tdi.png', 'idx.png', 'nidx.png', 'cgss.png', 'ncgss.png');
$ga_booleen_oui_non = array(true => 'oui', false => 'non');

define('AIDE_RELEVES', 1);
define('AIDE_INFORMATIQUE', 2);
define('AIDE_AD', 4);
define('AIDE_BULLETIN', 8);
define('TYPE_READHESION', 'R');
define('TYPE_INSCRIPTION', 'I');

define('ORIGINE_INTERNET', 1);
define('ORIGINE_FORUM', 2);
define('ORIGINE_PRESSE', 3);
define('ORIGINE_MANIFESTATION', 4);
define('ORIGINE_AD', 5);
define('ORIGINE_CONNAISSANCE', 6);
define('ORIGINE_AUTRE', 7);

$ga_tarifs = array(
    'internet' => 15,
    'bulletin_metro' => 33,
    'bulletin_etranger' => 43
);

$ga_mois = array(
    1 => 'Janvier',
    2 => 'Février',
    3 => 'Mars',
    4 => 'Avril',
    5 => 'Mai',
    6 => 'Juin',
    7 => 'Juillet',
    8 => 'Août',
    9 => 'Septembre',
    10 => 'Octobre',
    11 => 'Novembre',
    12 => 'Décembre'
);

$ga_mois_revolutionnaires       = array(
    1 => 'Vendémiaire',
    2 => 'Brumaire',
    3 => 'Frimaire',
    4 => 'Nivôse',
    5 => 'Pluviôse',
    6 => 'Ventôse',
    7 => 'Germinal',
    8 => 'Floréal',
    9 => 'Prairial',
    10 => 'Messidor',
    11 => 'Thermidor',
    12 => 'Fructidor',
    13 => 'Complémentaires'
);

$ga_mois_revolutionnaires_nimegue       = array(
    1 => 'Vend',
    2 => 'Brum',
    3 => 'Frim',
    4 => 'Nivo',
    5 => 'Pluv',
    6 => 'Vent',
    7 => 'Germ',
    8 => 'Flor',
    9 => 'Prai',
    10 => 'Mess',
    11 => 'Ther',
    12 => 'Fruc',
    13 => 'Comp',
);

$ga_annees_revolutionnaires      = array(
    2 => 'An II',
    3 => 'An III',
    4 => 'An IV',
    5 => 'An V',
    6 => 'An VI',
    7 => 'An VII',
    8 => 'An VIII',
    9 => 'An IX',
    10 => 'An X',
    11 => 'An XI',
    12 => 'An XII',
    13 => 'An XIII',
    14 => 'An XIV'
);
define('IDF_SOURCE_RELEVES_AGC', 1);
define('IDF_SOURCE_TD', 4);
define('SEUIL_RETENTION_ADHTS', 5);
define('NB_ACTES_BLOC_CHGMT', 2000);
define('PAGE_RECHERCHE', 'Recherches.php');

define('LIB_ASSO', "Association Généalogique de la Charente");
define('LIB_ASSO_AVEC', "L'Association Généalogique de la Charente");
define('SIGLE_ASSO', 'AGC');

define('EMAIL_INFOASSO', 'info@assoactes.fr');
define('EMAIL_INSCRIPTION_FORUM', '');

define('EMAIL_FORUM', 'agc16-forum@googlegroups.com');
define('EMAIL_DIRASSO', 'agc-dir@genea16.net');
define('EMAIL_GBKADMIN', 'agc-geneabank@genea16.net');
define('EMAIL_PRESASSO', 'agc-dir@genea16.net');
