<?php

// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

use App\Manager\PrivilegeManager;
use App\Manager\UtilisateurManager;
use App\Service\AuthentificationService;

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/Service/AuthentificationService.php';
require __DIR__ . '/app/Manager/UtilisateurManager.php';
require __DIR__ . '/app/Manager/PrivilegeManager.php';

$url_retour = $session->getAttribute('url_retour') ?? '/';

if (isset($_POST['identifier']) && isset($_POST['password'])) {
    $authentificationService = new AuthentificationService($dbconfig);
    $user = $authentificationService->login(['identifier' => $_POST['identifier'], 'password' => $_POST['password']]);
    if ($user && $session->isAuthenticated() === true) {
        // Log de connexion
        //$logger = new LocalLogger();
        //$logger->addLog();
        list($i_sec, $i_min, $i_heure, $i_jmois, $i_mois, $i_annee, $i_j_sem, $i_j_an, $b_hiver) = localtime();
        $i_mois++;
        $i_annee += 1900;
        $st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $i_jmois, $i_mois, $i_annee, $i_heure, $i_min, $i_sec);
        $st_chaine_log = join(';', array($st_date_log, $session->getAttribute('ident'), $adresse_ip));
        $pf = @fopen("../logs/connexions.log", 'a');
        @fwrite($pf, "$st_chaine_log\n");
        @fclose($pf);
        // TODO: Statut adherent
        // $user = [];
        $session->setAttribute('user', $user);
        $session->setAttribute('url_retour', null);
        $session->setAttribute('compteur', 0);
        header("Location: $url_retour");
    } else {
        $session->setAttribute('compteur', $session->getAttribute('compteur') + 1);
        $session->setAttribute('url_retour', null);
        $errors[] = ['type' => 'warning', 'message' => 'Identification érronée.'];
        if ($session->getAttribute('compteur') && $session->getAttribute('compteur') == 4) {
            $errors[] = ['type' => 'danger', 'message' => "Vous ne disposer plus que d'un seul essai avant d'etre bloqué!"];
        }
    }
}

?>

    <!DOCTYPE html>
    <html lang="fr">

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
            <div class="text-center"><img src="<?= $gst_logo_association; ?>" class="rounded mx-auto d-block" alt="Logo <?= SIGLE_ASSO; ?>"></div>
            <div class="panel panel-primary col-md-offset-2 col-md-8">
                <div class="panel-heading">Authentification requise</div>
                <div class="panel-body">
                    <form method="post" id="identification" class="form-horizontal">
                        <?php if ($errors) {  
                            foreach ($errors as $error) { ?>
                                    <div class="alert alert-<?= $error['type']; ?>">
                                    <?= $error['message']; ?>
                                </div>
                            <?php } 
                        } ?>
                        <div class="form-group">
                            <label for="identifier" class="col-md-4 col-form-label"> Identifiant:</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-user"></span>
                                    </span>
                                    <input type="text" name="identifier" id="identifier" size="30" maxlength="30" class="js-select-avec-recherche form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password" class="col-md-4 col-form-label">Mot de passe:</label>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-lock"></span>
                                    </span>
                                    <input type="password" name="password" id="password" size="30" maxlength="30" class="js-select-avec-recherche form-control">
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
