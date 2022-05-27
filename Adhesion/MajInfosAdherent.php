<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Commun/commun.php';
require_once __DIR__ . '/Commun/VerificationDroits.php';
require_once __DIR__ . '/Commun/Adherent.php';

// Redirect to identification
if (!$session->getAttribute('ident')) {
    $session->setAttribute('url_retour', '/MesDemandes.php');
    header('HTTP/1.0 401 Unauthorized');
    header('Location: /se-connecter.php');
    exit;
}

if (!$_GET['idf']) {
    header('HTTP/1.0 404 Not Found');
    header('Location: /');
}

$id_utilisateur = $_GET['idf']; // L'id de l utilisateur qui doit etre affiché


$adherent = [];
$statuts = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM statut_adherent ORDER BY nom");

print('<!DOCTYPE html>');
print("<head>");
print('<link rel="shortcut icon" href="assets/img/favicon.ico">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>\n");
print("<link href='assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>\n");
print("<link href='assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>\n");
print("<link href='assets/css/select2.min.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>");
print("<script src='assets/js/jquery-min.js' type='text/javascript'></script>\n");
print("<script src='assets/js/jquery-ui.min.js' type='text/javascript'></script>\n");
print("<script src='assets/js/jquery.validate.min.js' type='text/javascript'></script>\n");
print("<script src='assets/js/additional-methods.min.js' type='text/javascript'></script>\n");
print("<script src='assets/js/select2.min.js' type='text/javascript'></script>");
print("<script src='assets/js/bootstrap.min.js' type='text/javascript'></script>");
print("<title>Base " . SIGLE_ASSO . ": Vos informations personnelles</title>\n");
print('</head>');

print('<body>');
print('<div class="container">');

require_once __DIR__ . '/Commun/menu.php';

print("<form   id=\"maj_infos_adherent\" method=\"post\" class=\"form-horizontal\">\n");
print("<input type=hidden name=mode value=MODIFIER>\n");
print('<div class="row col-md-12">');

print('<div class="col-md-6">');

?>
<input type="hidden" id="idf_adht" name="idf_adht" value="<?= $i_idf; ?>">
<div class="form-row">
    <?php if (a_droits($session->getAttribute('ident'), DROIT_GESTION_ADHERENT)) { ?>
        <div class="form-group row">
            <label for="no_adht" class="col-md-4 col-form-label control-label">N° d'adhérent</label>
            <div class="col-md-8">
                <input type="text" value="<?= $i_idf; ?>" id="no_adht" size="5" readonly class="form-control">
                <label for="statut_adherent" class="sr-only">Statut</label>
                <select name=statut_adherent id=statut_adherent class="form-control">
                    <?= chaine_select_options($adherent['statut'], $statuts); ?>
                </select>
            </div>
        </div>
    <?php } else {
        // $this->connexionBD->initialise_params(array(':statut' => $this->st_statut));
        // $st_statut = $this->connexionBD->sql_select1("select nom from statut_adherent where idf=:statut");
    ?>
        <div class="form-group row col-md-12">
            <label for="no_adht" class="col-md-4 col-form-label">N° d'adhérent</label>
            <div class="col-md-2">';
                <input type="text" value="<?= $i_idf; ?>" id="no_adht" size="5" readonly>
            </div>
            <label for="statut_adherent" class="col-form-label col-md-4">Année de cotisation</label>
            <div class="col-md-2">
                <input type="text" value="<?= $i_annee_cotisation; ?>" id="statut_adherent" size="5">
            </div>
        </div>
    <?php }
    // L'administrateur n'est pas supposé changer l'identifiant d'un utilisateur 
    $readonly = $session->getAttribute('ident') !== $adherent['idf'] ? 'readonly' : ''; ?>
    <div class="form-group row">
        <label for="ident_adh" class="col-md-4 col-form-label">Votre identifiant</label>
        <div class="col-md-8">
            <input type="text" maxlength="12" size="8" name="ident_adh" id="ident_adh" value="<?= $adherent['ident']; ?>" <?= $readonly; ?>>
        </div>
        }
    </div>

    <div class="form-group row">
        <label for="nom" class="col-md-4 col-form-label control-label">Nom</label>
        <div class="col-md-8">
            <input type="text" maxlength="30" size="20" name="nom" id="nom" value="<?= cp1252_vers_utf8($adherent['nom']); ?>" class="form-control text-uppercase">
        </div>
    </div>

    <div class="form-group row">
        <label for="prenom" class="col-md-4 col-form-label control-label">Prénom</label>
        <div class="col-md-8">
            <input type="text" maxlength="20" name="prenom" id="prenom" value="<?= cp1252_vers_utf8($adherent['prenom']); ?>" class="form-control text-capitalize">
        </div>
    </div>

    <div class="form-group row">
        <label for="adr1" class="col-md-4 col-form-label control-label">Adresse 1</label>
        <div class="col-md-8">
            <input type="text" maxlength="40" name="adr1" id="adr1" value="<?= cp1252_vers_utf8($adherent['adr1']); ?>" class="form-control col-md-8">
        </div>
    </div>

    <div class="form-group row">
        <label for="adr2" class="col-md-4 col-form-label control-label">Adresse 2</label>
        <div class="col-md-8">
            <input type="text" maxlength="40" name="adr2" id="adr2" value="<?= cp1252_vers_utf8($adherent['adr2']); ?>" class="form-control col-md-8">
        </div>
    </div>

    <div class="form-group row">
        <label for="code_postal" class="col-md-4 col-form-label control-label">Code Postal</label>
        <div class="col-md-8">
            <input type="text" maxlength="12" name="code_postal" id="code_postal" value="<?= cp1252_vers_utf8($adherent['code_postal']); ?>" class="form-control col-md-8">
        </div>
    </div>

    <div class="form-group row">
        <label for="ville" class="col-md-4 col-form-label control-label">Localité</label>
        <div class="col-md-8">
            <input type="text" maxlength="40" name="ville" id="ville" value="<?= cp1252_vers_utf8($adherent['ville']); ?>" class="form-control col-md-8">
        </div>
    </div>

    <div class="form-group row">
        <label for="pays" class="col-md-4 col-form-label control-label">Pays</label>
        <div class="col-md-8">
            <select name="pays" id="pays" class="form-control col-md-8 js-select-avec-recherche">
                <?= chaine_select_options_simple(cp1252_vers_utf8($adherent['pays']), $ga_pays); ?>
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label for="site_adht" class="col-md-4 col-form-label control-label">Site web</label>
        <div class="col-md-8">
            <input type="text" maxlength="60" name="site" id="site" value="<?= cp1252_vers_utf8($adherent['site']); ?>" class="form-control">
        </div>
    </div>

    <div class="form-group row">
        <label for="email_perso" class="col-md-4 col-form-label control-label">Email perso</label>
        <div class="col-md-8">
            <input type="text" maxlength="60" name="email_perso" id="email_perso" value="<?= cp1252_vers_utf8($adherent['email_perso']); ?>" class="form-control" aria-describedby="UsageEmailPerso">
            <small id="UsageEmailPerso">Données accessibles uniquement aux gestionnaires de l'association</small>
        </div>
    </div>

    <div class="form-group row">
        <label for="telephone" class="col-md-4 col-form-label control-label">Téléphone</label>
        <div class="col-md-8">
            <input type="text" maxlength="15" name="tel" id="tel" value="<?= cp1252_vers_utf8($adherent['tel']); ?>" aria-describedby="UsageTelephone" class="form-control">
            <small id="UsageTelephone">Données accessibles uniquement aux gestionnaires de l'association</small>
        </div>
    </div>

</div>

<div class="col-md-6">

    <label for="aides">Je souhaite m'impliquer dans le fonctionnement de l'association en:</label>
    <div class="form-group" id="aides">
        <div class="checkbox">
            <?php $st_coche = ($this->i_aide & AIDE_RELEVES) ? 'checked' : ''; ?>
            <label>
                <input type="checkbox" name="aide[]" value="<?= AIDE_RELEVES; ?>" id="<?= AIDE_RELEVES; ?>" class="form-check-input" <?= $st_coche; ?>>
                Effectuant des relevés
            </label>
        </div>
        <div class="checkbox">
            <?php $st_coche = ($this->i_aide & AIDE_INFORMATIQUE) ? 'checked' : ''; ?>
            <label>
                <input type="checkbox" name="aide[]" value="<?= AIDE_INFORMATIQUE; ?>" id="<?= AIDE_INFORMATIQUE; ?>" class="form-check-input" <?= $st_coche; ?>>
                Participant à l'informatique (développement, administration du site)
            </label>
        </div>

        <div class="checkbox">
            <?php $st_coche = ($this->i_aide & AIDE_AD) ? 'checked' : ''; ?>
            <label>
                <input type="checkbox" name="aide[]" value="<?= AIDE_AD; ?>" id="<?= AIDE_AD; ?>" class="form-check-input" <?= $st_coche; ?>>
                Faisant de l'entraide aux AD
            </label>
        </div>
        <?php $st_coche = ($this->i_aide & AIDE_BULLETIN) ? 'checked' : ''; ?>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="aide[]" value="<?= AIDE_BULLETIN; ?>" id="<?= AIDE_BULLETIN; ?>" class="form-check-input" <?= $st_coche; ?>>
                Participant au Bulletin
            </label>
        </div>
    </div>
    <div class="form-row text-center">Merci de cocher la case correspondante:</div>
    <label for="origines">Comment nous avez-vous connu ?</label>
    <div class="form-group" id="origines">
        <?php $st_coche  = ($i_origine == ORIGINE_INTERNET) ? 'checked' : ''; ?>
        <div class="radio">
            <label>
                <input type="radio" id="OrigineInternet" name="type_origine" value="<?= ORIGINE_INTERNET; ?>" class="form-check-input" <?= $st_coche; ?>>
                Site Internet
            </label>
        </div>
        <?php $st_coche = ($i_origine == ORIGINE_FORUM) ? 'checked' : ''; ?>
        <div class="radio">
            <label>
                <input type="radio" id="OrigineForum" name="type_origine" value="<?= ORIGINE_FORUM; ?>" class="form-check-input" <?= $st_coche; ?>>
                Forum de discussion
            </label>
        </div>
        <?php $st_coche = ($i_origine == ORIGINE_PRESSE) ? 'checked' : ''; ?>
        <div class="radio">
            <label>
                <input type="radio" id="OriginePresse" name="type_origine" value="<?= ORIGINE_PRESSE; ?>" class="form-check-input" <?= $st_coche; ?>>
                Article de presse
            </label>
        </div>
        <?php $st_coche = ($i_origine == ORIGINE_MANIFESTATION) ? 'checked' : ''; ?>
        <div class="radio">
            <label>
                <input type="radio" id="OrigineManifestation" name="type_origine" value="<?= ORIGINE_MANIFESTATION; ?>" class="form-check-input" <?= $st_coche; ?>>
                Manifestation spécifique
            </label>
        </div>
        <?php $st_coche = ($i_origine == ORIGINE_AD) ? 'checked' : ''; ?>
        <div class="radio">
            <label>
                <input type="radio" id="OrigineAD" name="type_origine" value="<?= ORIGINE_AD; ?>" class="form-check-input" <?= $st_coche; ?>>
                Visite aux AD
            </label>
        </div>
        <?php $st_coche  = ($i_origine == ORIGINE_CONNAISSANCE) ? 'checked' : ''; ?>
        <div class="radio">
            <label>
                <input type="radio" id="OrigineConnaissance" name="type_origine" value="<?= ORIGINE_CONNAISSANCE; ?>" class="form-check-input" <?= $st_coche; ?>>
                Bouche à oreille</label>
        </div>
        <?php $st_coche  = ($i_origine == ORIGINE_AUTRE) ? 'checked' : ''; ?>
        <div class="radio">
            <label>
                <input type="radio" id="OrigineAutre" name="type_origine" value="<?= ORIGINE_AUTRE; ?>" class="form-check-input" <?= $st_coche; ?>>
                Autre
            </label>
        </div>
    </div>
    <div class="form-group">
        <label for="description_origine">Veuillez préciser SVP dans tous les cas:</label>
        <input type="text" maxlength="80" size="20" name="description_origine" id="description_origine" value="<?= self::cp1252_vers_utf8($adherent['description_origine']); ?>" class="form-control">
    </div>
</div>
</div>
<div class="row">
    <button type="submit" class="btn btn-primary col-md-offset-4 col-md-4">
        <span class="glyphicon glyphicon-save"></span> Modifier toutes vos informations
    </button>
</div>
</form>

</div>
</body>

</html>