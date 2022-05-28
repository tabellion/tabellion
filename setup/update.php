<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Configuration.php';
// The last config file not exist
if (!file_exists(__DIR__ . '/../config.yaml.php')) {
    $config = new Configuration(true);
    $params = $config->getAll();
}

if (isset($_POST)) {

}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <title>Tabellion | Mise a jour</title>
</head>

<body>
    <div class="container">
    <h1>Mise à jour de Tabellion</h1>
  
    <form method="post">
        <h3>General</h3>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Locale</label>
            <div class="col-sm-10">
                <select name="default_locale" id="default_locale">
                    <option value="fr">fr</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Nom :</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" name="nom" value="<?= $params['nom']; ?>" required>
            </div>
            <label class="col-sm-2 col-form-label">Sigle :</label>
            <div class="col-sm-2">
                <input type="text" class="form-control form-control-sm" name="sigle" value="<?= $params['sigle']; ?>">
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Libele :</label>
            <div class="col-sm-8">
                <input type="text" class="form-control form-control-sm" name="libele" value="<?= $params['libele']; ?>">
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Description :</label>
            <div class="col-sm-10">
                <input type="text" class="form-control form-control-sm" name="description" value="<?= $params['description']; ?>">
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Domaine tabellion</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" name="domaine_tabellion" value="<?= $params['domaine_tabellion']; ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Domaine site web</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" name="domaine_site" value="<?= $params['domaine_site']; ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Domaine compte utilisateur</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" name="domaine_mon-compte" value="<?= $params['domaine_mon-compte']; ?>" required>
            </div>
        </div>
        
        <h3>Database</h3>

        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Serveur</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" name="database[host]" value="<?= $params['database']['host']; ?>" required>
            </div>
            <label class="col-sm-2 col-form-label">Port</label>
            <div class="col-sm-2">
                <input type="text" class="form-control form-control-sm" name="database[port]" value="<?= $params['database']['port']; ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Utilisateur</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" name="database[user]" value="<?= $params['database']['user']; ?>" required>
            </div>
            <label class="col-sm-2 col-form-label">Mot de passe</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" name="database[pass]" value="<?= $params['database']['pass']; ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Nom de la base</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" name="database[dbname]" value="<?= $params['database']['dbname']; ?>" required>
            </div>
        </div>

        <h3>Fichier</h3>

        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Dossier du stockage local</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" name="local_storage" value="<?= $params['local_storage']; ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Dossier des logs</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" name="local_logs" value="<?= $params['local_logs']; ?>" required>
            </div>
        </div>

        <button type="submit" class="btn btn-secondary btn-sm">Valider</button>
    </form>  
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
</body>

</html>