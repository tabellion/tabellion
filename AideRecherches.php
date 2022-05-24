<?php
require_once __DIR__ . '/Commun/Identification.php';
require_once __DIR__ . '/Commun/commun.php';
require_once __DIR__ . '/Commun/constantes.php';
require_once __DIR__ . '/Commun/ConnexionBD.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
print('<meta http-equiv="content-language" content="fr">');
print('<link rel="shortcut icon" href="images/favicon.ico">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='js/jquery-min.js' type='text/javascript'></script>");
print("<script src='js/bootstrap.min.js' type='text/javascript'></script>");
print('<title>Base ' . SIGLE_ASSO . ': Aide sur les recherches</title>');
print("</head>");
print('<body>');
print('<div class="container">');

require_once __DIR__ . '/Commun/menu.php';
?>
<p style="text-align: center;" class="MsoNormal">
    <span>
        <img alt="MenuRecherches" style="width: 698px; height: 282px;" src="images/MenuV4.png">
    </span>
</p>
<div>Cet écran comporte 3 pavés&nbsp;:
</div>
<ul>
    <li>
        <div>
            <span>
                <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                </span>
            </span>Le 1<sup>er</sup> pavé est
            <span>&nbsp;
            </span>commun aux deux autres pavés qui sont des modules de recherche. Ce pavé <strong>
                <span style="font-weight: bold;">optionnel
                </span> </strong>a pour mission d'affiner la recherche et ainsi limiter le nombre de réponses obtenues.
        </div>
    </li>
</ul>
<ol style="margin-left: 80px;">
    <li>
        <div>
            <span>&nbsp;&nbsp;&nbsp;
                <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                </span>
            </span>Source&nbsp;: liste de l'origine des actes ( AGC, AGL,.. )
        </div>
    </li>
    <li>
        <div>
            <span>&nbsp;&nbsp;&nbsp;
                <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                </span>
            </span>Type d'acte&nbsp;: rechercher uniquement dans les naissances ou les sépultures ou les mariages
        </div>
    </li>
    <li>
        <div>
            <span>&nbsp;&nbsp;&nbsp;
                <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                </span>
            </span>Commune/paroisse&nbsp;: la recherche se fait sur la commune sélectionnée
        </div>
    </li>
    <li>
        <div>
            <span>&nbsp;&nbsp;&nbsp;
                <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                </span>
            </span>Rayon de recherche&nbsp;: cette recherche cible une zone géographique de
            <span>&nbsp;
            </span>(X) kms autour de la paroisse sélectionnée
        </div>
    </li>
    <li>
        <div>
            <span>&nbsp;&nbsp;&nbsp;
                <span style="font-style: normal; font-variant: normal; font-weight: normal; font-size: 7pt; line-height: normal; font-size-adjust: none; font-stretch: normal;">
                </span>
            </span>Années&nbsp;: limite la recherche sur une fourchette d'années
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
        <div>C'est quoi au juste cette recherche&nbsp;? Il s'agit de rechercher le couple dans tous les actes où il peut se trouver c'est-à-dire en tant qu'époux et épouse, père et mère,
            <span style="font-size: 12pt; font-family: &quot;Times New Roman&quot;,&quot;serif&quot;;">intervenant et ancien conjoint
            </span>. Imaginez un peu les possibilités&nbsp;! en 2 clics, en ayant renseigné simplement les deux noms du couple, vous obtenez son mariage, la liste de tous ses enfants (naissance, mariage, décès)
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
            </span>Recherche par variantes connues&nbsp;: cette option élargit la recherche aux différentes orthographes du patronyme ( exemple&nbsp;:
            <span>&nbsp;
            </span>
            <span>AIGRETAUD &nbsp;; AIGRETEAUX &nbsp;;
                <span>&nbsp;
                </span>AIGRETTEAU &nbsp;;
                <span>&nbsp;
                </span>AIGRETTEAUX &nbsp;;
                <span>&nbsp;
                </span>AYGRETEAU &nbsp;; AYGRETTEAU&nbsp;; RGRETAU &nbsp;;
                <span>&nbsp;
                </span>HEGRETEAU ....)&nbsp;
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