<?php
require_once __DIR__ . '/app/bootstrap.php';

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="content-language" content="fr">
    <link rel="shortcut icon" href="assets/img/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/styles.css" type="text/css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <script src="assets/js/jquery-min.js" type="text/javascript"></script>
    <script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
    <title>Base <?= SIGLE_ASSO; ?> : Aide sur les recherches</title>
</head>

<body>
    <div class="container">

        <?php require_once __DIR__ . '/commun/menu.php'; ?>

        <p style="text-align: center;" class="MsoNormal">
            <span>
                <img alt="MenuRecherches" style="width: 698px; height: 282px;" src="images/MenuV4.png">
            </span>
        </p>
        <div>Cet écran comporte 3 pavés :
        </div>
        <ul>
            <li>
                <div>
                    <span>
                        <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                        </span>
                    </span>Le 1<sup>er</sup> pavé est
                    <span>
                    </span>commun aux deux autres pavés qui sont des modules de recherche. Ce pavé <strong>
                        <span style="font-weight: bold;">optionnel
                        </span> </strong>a pour mission d'affiner la recherche et ainsi limiter le nombre de réponses obtenues.
                </div>
            </li>
        </ul>
        <ol style="margin-left: 80px;">
            <li>
                <div>
                    <span>
                        <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                        </span>
                    </span>Source : liste de l'origine des actes ( AGC, AGL,.. )
                </div>
            </li>
            <li>
                <div>
                    <span>
                        <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                        </span>
                    </span>Type d'acte : rechercher uniquement dans les naissances ou les sépultures ou les mariages
                </div>
            </li>
            <li>
                <div>
                    <span>
                        <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                        </span>
                    </span>Commune/paroisse : la recherche se fait sur la commune sélectionnée
                </div>
            </li>
            <li>
                <div>
                    <span>
                        <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                        </span>
                    </span>Rayon de recherche : cette recherche cible une zone géographique de
                    <span>
                    </span>(X) kms autour de la paroisse sélectionnée
                </div>
            </li>
            <li>
                <div>
                    <span>
                        <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                        </span>
                    </span>Années : limite la recherche sur une fourchette d'années
                </div>
            </li>
        </ol>
        <div><small>
                <br></small>
        </div>
        <ul>
            <li>
                <div>
                    <span>
                        <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                        </span>
                    </span>Le 2<sup>ème</sup> pavé concerne la recherche par couple
                </div>
            </li>
        </ul>
        <p>
        </p>
        <ol style="margin-left: 80px;">
            <li>
                <div>C'est quoi au juste cette recherche ? Il s'agit de rechercher le couple dans tous les actes où il peut se trouver c'est-à-dire en tant qu'époux et épouse, père et mère,
                    <span style="font-size: 12pt; font-family: Times New Roman, Times, serif;">intervenant et ancien conjoint
                    </span>. Imaginez un peu les possibilités ! en 2 clics, en ayant renseigné simplement les deux noms du couple, vous obtenez son mariage, la liste de tous ses enfants (naissance, mariage, décès)
                </div>
            </li>
            <li>
                <div>
                    <span>
                        <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                        </span>
                    </span>Renseignez impérativement les noms de l'époux et de l'épouse
                </div>
            </li>
            <li>
                <div>
                    <span>
                        <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                        </span>
                    </span>Recherche par variantes connues : cette option élargit la recherche aux différentes orthographes du patronyme ( exemple :
                    <span>
                    </span>
                    <span>AIGRETAUD ; AIGRETEAUX ;
                        <span>
                        </span>AIGRETTEAU ;
                        <span>
                        </span>AIGRETTEAUX ;
                        <span>
                        </span>AYGRETEAU ; AYGRETTEAU ; RGRETAU ;
                        <span>
                        </span>HEGRETEAU ....)
                    </span>
                </div>
            </li>
            <li>
                <div>le joker (*) est toujours utilisable et utile car les variantes ne sont pas exaustives. La recherche par variante est desactivable si besoin.
                    <span>
                    </span>
                </div>
                <div>
                    <span>
                    </span>
                </div>
            </li>
        </ol>
        <div>
            <span>
            </span>
        </div>
        <div>
            <span>
            </span>
        </div>
        <div style="margin-left: 1px; width: 969px;"><small></small>
            <ul>
                <li>
                    <span>
                        <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                        </span>
                    </span>Le 3<sup>ème</sup> pavé concerne la recherche d'un individu en particulier
                </li>
            </ul>
        </div>
        <ol style="margin-left: 80px;">
            <li>
                <div>
                    <span>
                        <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                        </span>
                    </span>Renseignez impérativement le nom de la personne
                </div>
            </li>
            <li>
                <div>
                    <span>
                        <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                        </span>
                    </span>Le type de présence permet d'affiner la chercher parmi les intervenants, les parents, les anciens conjoints, les témoins, les parrain/marraine
                </div>
            </li>
        </ol>
        <div style="text-align: center;">
            <span style="font-style: italic; color: red;">Attention seules les 100 premières réponses son
            </span>
            <span>
                <span style="font-style: italic; color: red;">t affichées, alors affinez votre recherche
                </span>
                <br>
                Depuis les AM d'Angoulême et AD sont en ligne, l'AGC procède à des indexations, c'est-à-dire que seule la référence des archives en ligne et notée dans l'acte.
                <br>Vous pouvez obtenir la cote de l'acte en survolant l'icone correspondante avec le nez la souris comme indiqué ci-dessous:
                <br>
                <img alt="Résultat de recherche" src="images\cote_idx.png">
                <br>
                <img alt="Résultat de recherche" src="images\cote_tdi.png">
                <br>
                Ces indexations peuvent être consultées sans aucune limite
                <br>
                <br>Pour consulter le relevé des autres actes, cliquez sur le i en face de l'acte qui vous intéresse comme indiqué dans l'image ci-dessous.
                <br>
                <br>
                <img style="width: 768px; height: 402px;" alt="R�sultat de recherche" src="images/ReponseV4.png">

                <br>Une nouvelle fenêtre apparaît. Si ce n'est pas le cas, vérifiez que votre navigateur n'a pas interdit l'ouverture de la fenêtre.
                <br>Celle-ci se présente ainsi:
                <br>
                <br>
                <img style="width: 600px; height: 626px;" alt="Ajout dans le panier" src="images/DetailV4.png">
                <br>
                <br> Par mois, vous disposez de 500 demandes de naissances, 500 demandes de d&eacute;c&eacute;s et 50 demandes d'actes divers (mariage, CM, Testament...)
                <br>

            </span>
        </div>
    </div>
</body>

</html>