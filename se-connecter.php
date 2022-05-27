<?php

// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

require_once __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/Manager/UtilisateurManager.php';
require __DIR__ . '/app/Manager/PrivilegeManager.php';

$url_retour = $session->getAttribute('url_retour') ?? '/';
$errors = [];

if (isset($_POST['ident']) && isset($_POST['mdp'])) {
    if (verifie_utilisateur($_POST['ident'], $_POST['mdp']) == true) {
        $session->setAttribute('ident', $_POST['ident']);
        $connexionBD->ajoute_params(array(':ident' => $st_ident));
        $st_requete = "UPDATE adherent SET derniere_connexion=now() WHERE ident=:ident";
        $connexionBD->execute_requete($st_requete);
        // date_default_timezone_set($gst_time_zone); // NB: doit etre le timezone du serveur!
        list($i_sec, $i_min, $i_heure, $i_jmois, $i_mois, $i_annee, $i_j_sem, $i_j_an, $b_hiver) = localtime();
        $i_mois++;
        $i_annee += 1900;
        $st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $i_jmois, $i_mois, $i_annee, $i_heure, $i_min, $i_sec);
        $st_chaine_log = join(';', array($st_date_log, $_SESSION['ident'], $gst_adresse_ip));
        $pf = @fopen("../logs/connexions.log", 'a');
        @fwrite($pf, "$st_chaine_log\n");
        @fclose($pf);
        $utilisateurManager = new UtilisateurManager($databasecfg);
        $privilegeManager = new PrivilegeManager($databasecfg);
        // TODO: Statut adherent
        $user = [];
        $user['privileges'] = [];

        if ($session->getAttribute('ident')) {
            $user = $utilisateurManager->findOneByCriteria(['ident' => $session->getAttribute('ident')]);
            $user['privileges'] = $privilegeManager->findAllWhithAdherentId($user['idf']);
        }
        $session->setAttribute('user', $user);
        $session->setAttribute('url_retour', null);
        $session->setAttribute('compteur', 0);
        header("Location: $url_retour");
    } else {
        $session->setAttribute('compteur', $session->getAttribute('compteur') + 1);
        $session->setAttribute('url_retour', null);
        $errors[] = 'Identification érronée.';
        if ($session->getAttribute('compteur') && $session->getAttribute('compteur') == 4) {
            $errors[] = "Vous ne disposer plus que d'un seul essai avant d'etre bloqué!";
        }
    }
}



/**
 * Vérifie que si l'utilisateur est autorisé à se connecter (statut B,I,H)
 * @param string $pst_ident identifiant de l'utilisateur
 * @param string $pst_mdp mot de passe de l'utilisateur
 * @return boolean l'utilisateur est authentifie ou non ?
 * @global $connexionBD identifiant de connexion BD
 */
function verifie_utilisateur($ident, $mdp)
{
    global $connexionBD, $gst_ip_restreinte;
    $connexionBD->ajoute_params(array(':ident' => $ident));
    $st_requete = "SELECT mdp FROM adherent WHERE ident=:ident AND statut in ('B','I','H')";
    $st_mdp_hash = $connexionBD->sql_select1($st_requete);
    if (password_verify($mdp, $st_mdp_hash)) {
        return true;
    } else
        return false;
}

if ($session->getAttribute('compteur') != 5) { echo $session->getAttribute('compteur');?>

    <!DOCTYPE html>

    <head>
        <link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>
        <link href='../assets/css/bootstrap.min.css' rel='stylesheet'>
        <link href='../assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>
        <link href='../assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>
        <link href='../assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>
        <script src='../assets/js/jquery-min.js' type='text/javascript'></script>
        <script src='../assets/js/jquery-ui.min.js' type='text/javascript'></script>
        <script src='../assets/js/jquery.validate.min.js' type='text/javascript'></script>
        <script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>
        <link rel="shortcut icon" href="../assets/img/favicon.ico">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Identification</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

    </head>

    <body>
        <div class="container">
            <div class="text-center"><img src='$gst_logo_association' class="rounded mx-auto d-block" alt="Logo <?= SIGLE_ASSO; ?>"></div>
            <div class="panel panel-primary col-md-offset-2 col-md-8">
                <div class="panel-heading">Authentification requise</div>
                <div class="panel-body">
                    <form method="post" id="identification" class="form-horizontal">
                        <?php if ($errors) { ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error) {
                                    echo $error . '<br>';
                                } ?>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="ident" class="col-md-4 col-form-label"> Identifiant:</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-user"></span>
                                    </span>
                                    <input type="text" name="ident" id="ident" size="30" maxlength="30" class="js-select-avec-recherche form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="mdp" class="col-md-4 col-form-label">Mot de passe:</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-lock"></span>
                                    </span>
                                    <input type="password" name="mdp" id="mdp" size="30" maxlength="30" class="js-select-avec-recherche form-control">
                                </div>
                            </div>
                        </div>
                        <div class="btn-group-vertical col-md-offset-3 col-md-6" role="group">
                            <button type="submit" id="bouton_soumission" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> Se connecter</button>
                            <button class="form-row col-md-offset-2 col-md-8 btn btn-warning" id="DemandeNouveauMDP">
                                <span class="glyphicon glyphicon-warning-sign"></span> J'ai oublié mon mot de passe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>

    </html>

<?php }
