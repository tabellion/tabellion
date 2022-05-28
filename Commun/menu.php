<?php

// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

// ========= Default
$a_categories_menu = $connexionBD->sql_select_multiple("SELECT libelle, script, droit FROM categorie_menu ORDER BY rang");
$a_elements_menu = $connexionBD->liste_valeur_par_doubles_clefs("SELECT categorie, libelle, script, droit FROM element_menu ORDER BY categorie,rang");
$a_privileges_utilisateur = [];
if ($session->getAttribute('user')) {
    $user = $session->getAttribute('user');
    foreach ($user['privileges'] as $privilege) {
        $a_privileges_utilisateur[] = $privilege['droit'];
    }
}

// =================
$menu = [
    'Accueil' => 'index.php',
    'Les Recherches' => [
        'Couple/Individu' => '/recherche.php',
        'Etat des relevés' => '/affiche-stats-commune.php',
        'Stats/Commune' => '/affiche-stats-type-acte-commune.php',
        'Patronymes/Commune' => '/affiche-patros-commune.php',
        'Patronymes' => '/affiche-patros.php',
        'Statistiques NMD' => '/stats-nmd.php',
        'Répertoires Notaire' => '/RepNot/Recherche-RepNot.php',
        // 'Aide' => '/aide-recherche.php', //-- Ajouté au menu 'Aide'
    ],
    /* 'Compte Personnel' => [
        // 'Mes Demandes' => '/mes-demandes.php', //-- Ajouté au menu 'Profil'
        // 'Mes informations' => '/maj-infos-adherent.php', //-- Ajouté à l'espace Adhesion(espace renommé mon-compte)
        // 'Changer mon mot de passe' => '/change-mdp.php', //-- Ajouté à l'espace Adhesion(espace renommé mon-compte)
    ], */
    /* 'Membres' => [
        'Liste' => '/liste-adherents.php', //-- Ajouté à l'espace Adhesion(espace renommé mon-compte)
    ], */
    'Administration' => [
        'Chargement/Export' => '/administration/gestion-donnees.php',
        'Notification' => '/administration/notification-commune.php',
        'Suppression' => '/administration/suppression-donnees.php',
        'Gestion Variantes' => '/administration/chargement-variantes.php',
        'Communes' => '/administration/gestion-communes.php',
        'Types d`\'actes' => '/administration/gestion-types-actes-divers.php',
        'Sources' => '/administration/gestion-sources.php',
        'Optimisation base' => '/administration/optimisation-tables.php',
        'Rep Notaires' => '/repnot/gestion-repnot.php',
    ],
    /* 'Suivi corrections' => [
        'Corrections demandées' => '/suivi-corrections/corrections-demandees.php',
        'Corrections faites' => '/suivi-corrections/corrections-faites.php',
    ], */
    'Utilitaires' => [
        'Variantes Patronyme' => '/utilitaires/ajout-variantes.php',
        'Variantes Prénom' => '/utilitaires/gestion-variantes-prenom.php',
        'Publi/chargement' => '/utilitaires/publication-chargements.php',
        'Log des Adhérents' => '/utilitaires/lecture-log.php',
        'Derniers connectés' => '/utilitaires/derniers-connectes.php',
        'Occupation tables' => '/utilitaires/utilisation-tables.php',
    ],
    'Stats' => [
        'Stats Consultations' => '/stats/stats-consultations.php',
        'Export Historique' => '/stats/export-histo.php',
        'Stats Adhesions' => '/stats/stats-adhesion.php',
        'Comparaison Nim/V4' => '/stats/verifie-stats-nim.php',
    ],
    'Gestion releveurs' => [
        'Documents Releveurs' => '/releves/gestion-documents.php',
        'Chantiers Releveurs' => '/releves/gestion-chantiers.php',
        'Photos Releveurs' => '/releves/gestion-photos.php',
    ],
    'AdminGBK' => '/Administration/ExportGeneabank.php',
    // 'Sortie' => '/sortie.php', -- remplacé par se deconnecter
    'Aide' => '/aide-recherche.php', // 
    'Profil' => [
        'Mes Demandes' => '/mes-demandes.php',
        'Se déconnecter' => '/se-deconnecter.php', // ++
    ],

    'Se connecter' => '/se-connecter.php' // ++
];
?>
<nav class="navbar navbar-default navbar-static-top">
    <ul class="nav navbar-nav">
        <li><a href="/">Accueil</a></li>
        <li class="dropdown"><a data-toggle="dropdown" href="#">Les Recherches <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <a href="/recherche.php">Couple/Individu</a>
                    <a href="/affiche-stats-commune.php">Etat des relevés</a>
                    <a href="/affiche-stats-type-acte-commune.php">Stats/Commune</a>
                    <a href="/affiche-patros-commune.php">Patronymes/Commune</a>
                    <a href="/affiche-patros.php">Patronymes</a>
                    <a href="/stats-nmd.php">Statistiques NMD</a>
                    <a href="/repnot/recherche-repnot.php">Répertoires Notaire</a>
                </li>
            </ul>
        </li>
        <li class="dropdown"><a data-toggle="dropdown" href="#">Administration <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <a href="/administration/gestion-donnees.php">Chargement/Export</a>
                    <a href="/administration/notification-commune.php">Notification</a>
                    <a href="/administration/suppression-donnees.php">Suppression</a>
                    <a href="/administration/chargement-variantes.php">Gestion Variantes</a>
                    <a href="/administration/gestion-communes.php">Communes</a>
                    <a href="/administration/gestion-types-actes-divers.php">Types d'actes</a>
                    <a href="/administration/gestion-sources.php">Sources</a>
                    <a href="/administration/optimisation-tables.php">Optimisation base</a>
                    <a href="/repnot/gestion-repnot.php">Rep Notaires</a>
                </li>
            </ul>
        </li>
        <li class="dropdown"><a data-toggle="dropdown" href="#">Utilitaires <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <a href="/utilitaires/ajout-variantes.php">Variantes Patronyme</a>
                    <a href="/utilitaires/gestion-variantes-prenom.php">Variantes Prénom</a>
                    <a href="/utilitaires/publication-chargements.php">Publi/chargement</a>
                    <a href="/utilitaires/lecture-log.php">Log des Adhérents</a>
                    <a href="/utilitaires/derniers-connectes.php">Derniers connectés</a>
                    <a href="/utilitaires/utilisation-tables.php">Occupation tables</a>
                </li>
            </ul>
        </li>
        <li class="dropdown"><a data-toggle="dropdown" href="#">Stats <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <a href="/stats/stats-consultations.php">Stats Consultations</a>
                    <a href="/stats/export-histo.php">Export Historique</a>
                    <a href="/stats/stats-adhesion.php">Stats Adhesions</a>
                    <a href="/stats/verifie-stats-nim.php">Comparaison Nim/V4</a>
                </li>
            </ul>
        </li>
        <li class="dropdown"><a data-toggle="dropdown" href="#">Gestion releveurs <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <a href="/releves/gestion-documents.php">Documents Releveurs</a>
                    <a href="/releves/gestion-chantiers.php">Chantiers Releveurs</a>
                    <a href="/releves/gestion-photos.php">Photos Releveurs</a>
                </li>
            </ul>
        </li>
        <li><a href="/administration/export-geneabank.php">AdminGBK</a></li>
        <li><a href="/aide-recherche.php">Aide</a></li>
        <?php if ($session->getAttribute('user') && null !== $user) { ?>
            <li class="dropdown"><a data-toggle="dropdown" href="#"><?= $user['prenom']; ?> <b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="/mes-demandes.php">Mes Demandes</a>
                        <a href="/se-deconnecter.php">Me déconnecter</a>
                    </li>
                </ul>
            </li>
        <?php } else { ?>
            <li><a href="/se-connecter.php">Me connecter</a></li>
        <?php } ?>
    </ul>
</nav>