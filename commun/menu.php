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
        <?php if ($session->isAuthenticated() && in_array('CHGMT_EXPT', $user['privileges'])) { ?>
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
        <?php } 
        if ($session->isAuthenticated() && in_array('UTILITAIRE', $user['privileges'])) { ?>
        <li class="dropdown"><a data-toggle="dropdown" href="#">Utilitaires <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <a href="/utilitaires/ajout-variantes.php">Variantes Patronyme</a>
                    <a href="/utilitaires/gestion-variantes-prenom.php">Variantes Prénom</a>
                    <a href="/utilitaires/publication-chargements.php">Publi/chargement</a>
                    <a href="/utilitaires/lecture-log.php">Log des Adhérents</a>
                    <!-- <a href="/utilitaires/derniers-connectes.php">Derniers connectés</a> -->
                    <a href="/utilitaires/utilisation-tables.php">Occupation tables</a>
                </li>
            </ul>
        </li>
        <?php } 
        if ($session->isAuthenticated() && in_array('STATS', $user['privileges'])) { ?>
        <li class="dropdown"><a data-toggle="dropdown" href="#">Stats <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <a href="/stats/stats-consultations.php">Stats Consultations</a>
                    <a href="/stats/export-histo.php">Export Historique</a>
                    <!-- <a href="/stats/stats-adhesion.php">Stats Adhesions</a> -->
                    <!-- <a href="/stats/verifie-stats-nim.php">Comparaison Nim/V4</a> -->
                </li>
            </ul>
        </li>
        <?php }
        if ($session->isAuthenticated() && in_array('RELEVES', $user['privileges'])) { ?>
        <li class="dropdown"><a data-toggle="dropdown" href="#">Gestion releveurs <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <a href="/releves/gestion-documents.php">Documents Releveurs</a>
                    <a href="/releves/gestion-chantiers.php">Chantiers Releveurs</a>
                    <a href="/releves/gestion-photos.php">Photos Releveurs</a>
                </li>
            </ul>
        </li>
        <?php }
        if ($session->isAuthenticated() && in_array('GENEABANK', $user['privileges'])) { ?>
        <li><a href="/administration/export-geneabank.php">AdminGBK</a></li>
        <?php } ?>
        <li><a href="/aide-recherche.php">Aide</a></li>
        <?php if ($session->isAuthenticated() && null !== $user) { ?>
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