<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------‌

class ConnexionBD
{
	private $db;
	private static $connexionBD;
	private $stmt;
	private $a_params = [];


	private function __construct($host, $dbname, $user, $pass)
	{
		$this->db = $this->connexion($host, $dbname, $user, $pass);
	} 

	public static function singleton($config)
	{
		if (!isset(self::$connexionBD)) {
			$c = __CLASS__;
			self::$connexionBD = new $c($config['host'], $config['dbname'], $config['user'], $config['pass']);
		}
		return self::$connexionBD;
	}

	private function connexion($host, $dbname, $user, $pass)
	{
		try {
			$connexionBD = new PDO("mysql:host=$host;dbname=$dbname;charset=latin1", $user, $pass);
			$connexionBD->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			throw new Exception("Connexion base impossible", 1);
		}
		return $connexionBD;
	}

	public function desactive_cache()
	{
		$this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
	}

	public function initialise_params($pa_params)
	{
		$this->a_params = $pa_params;
	}

	public function ajoute_params($pa_params)
	{
		$this->a_params = array_merge($this->a_params, $pa_params);
	}

	public function params()
	{
		return $this->a_params;
	}

	public function sql_select1($pst_requete)
	{
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			print_r($this->db->errorInfo());
			throw new Exception("sql_select1 impossible: ");
		}
		$this->stmt = $stmt;
		$a_resultat = $stmt->fetch(PDO::FETCH_NUM);
		list($st_val) = $a_resultat;
		$this->a_params = array();
		return $st_val;
	}

	public function sql_select($pst_requete)
	{
		$a_resultat = array();
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("sql_select impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		while (list($st_champ) = $stmt->fetch(PDO::FETCH_NUM)) {
			$a_resultat[] = $st_champ;
		}
		$this->a_params = array();
		return $a_resultat;
	}

	public function sql_select_multiple($pst_requete)
	{
		$a_resultat = array();
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {

			print_r($this->db->errorInfo());
			throw new Exception("sql_select_multiple impossible: ");
		}
		$this->stmt = $stmt;
		while ($a_champs = $stmt->fetch(PDO::FETCH_NUM)) {
			$a_resultat[] = $a_champs;
		}
		$this->a_params            = array();
		return $a_resultat;
	}

	public function sql_select_multiple_par_idf($pst_requete)
	{
		$a_resultat = array();
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("sql_select_multiple_par_idf impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		while ($a_champs = $stmt->fetch(PDO::FETCH_NUM)) {
			$i_idf = array_shift($a_champs);
			$a_resultat["$i_idf"] = $a_champs;
		}
		$this->a_params            = array();
		return $a_resultat;
	}

	public function sql_select_liste($pst_requete)
	{
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("sql_select_liste impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		$this->a_params = array();
		return	$stmt->fetch(PDO::FETCH_NUM);
	}

	public function sql_select_liste1($pst_requete)
	{
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("sql_select_liste impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		$this->a_params = array();
		return	$stmt->fetchAll();
	}

	public function sql_select_stats_actes($i_idf_adherent, $gi_idf_acte, $i_idf_type_acte)
	{
		switch ($i_idf_type_acte) {
			case IDF_NAISSANCE:
				$type = " idf_type_acte=" . IDF_NAISSANCE;
				break;
			case IDF_DECES:
				$type = " idf_type_acte=" . IDF_DECES;
				break;
			default:
				$type = " idf_type_acte!=" . IDF_NAISSANCE . " and idf_type_acte!=" . IDF_DECES;
				break;
		}

		$pst_requete = "select count(*) AS counter_type, (select count(*) from demandes_adherent where idf_adherent=$i_idf_adherent and idf_acte=$gi_idf_acte and month(date_demande)=month(now()) and year(date_demande)=year(now())) AS counter_acte from demandes_adherent where idf_adherent=$i_idf_adherent and " . $type . " and month(date_demande)=month(now()) and year(date_demande)=year(now())";

		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("sql_select_liste impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		$this->a_params            = array();
		return	$stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function liste_clef_par_valeur($pst_requete)
	{
		$a_resultat = array();
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("liste_clef_par_valeur impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		while (list($i_idf, $st_nom) = $stmt->fetch(PDO::FETCH_NUM)) {
			$a_resultat[strval($st_nom)] = $i_idf;
		}
		$this->a_params            = array();
		return $a_resultat;
	}

	public function liste_valeur_par_clef($pst_requete)
	{
		$a_resultat = array();
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("liste_valeur_par_clef impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		while (list($i_idf, $st_nom) = $stmt->fetch(PDO::FETCH_NUM)) {
			$a_resultat[$i_idf] = strval($st_nom);
		}
		$this->a_params            = array();
		return $a_resultat;
	}

	public function liste_valeur_par_doubles_clefs($pst_requete)
	{
		$a_resultat = array();
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("liste_valeur_par_doubles_clefs impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		while ($a_champs = $stmt->fetch(PDO::FETCH_NUM)) {
			$st_clef1 = array_shift($a_champs);
			$st_clef2 = array_shift($a_champs);
			$a_resultat[strval($st_clef1)][strval($st_clef2)] = $a_champs;
		}
		$this->a_params            = array();
		return $a_resultat;
	}

	/**
	 *  Dans un résultat à deux colonnes A et B, regroupe les éléments B dans un tableau indexés par A
	 *  @param string $pst_requete à traiter
	 *  @return array tableau indexés par A
	 */
	function groupe_valeurs_par_clef($pst_requete)
	{
		$a_resultat = array();
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("groupe_valeurs_par_clef impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		while (list($i_idf_clef, $st_valeur) = $stmt->fetch(PDO::FETCH_NUM)) {
			if (array_key_exists($i_idf_clef, $a_resultat))
				array_push($a_resultat[$i_idf_clef], $st_valeur);
			else
				$a_resultat[$i_idf_clef] = array($st_valeur);
		}
		$this->a_params            = array();
		return $a_resultat;
	}

	/* ----- résultat = une ligne, une colonne ----- */
	public function sql_select1Utf8($pst_requete)
	{
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			print_r($this->db->errorInfo());
			throw new Exception("sql_select1 impossible: ");
		}
		$this->stmt = $stmt;
		$a_resultat = $stmt->fetch(PDO::FETCH_NUM);
		list($st_val) = $a_resultat;
		// ---- modif UTF8
		$st_val = mb_convert_encoding($st_val, 'UTF8', 'cp1252');
		// ---- fin modif UTF8
		//$this->a_params = array();
		return $st_val;
	}

	/* ----- résultat = une ligne, plusieurs colonnes ----- */
	public function sql_select_listeUtf8($pst_requete)
	{
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("sql_select_liste impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		$a_champs = $stmt->fetch(PDO::FETCH_NUM);
		$a_convert = array();
		// ---- modif UTF8
		foreach ($a_champs as $st_temp) {
			$st_temp = mb_convert_encoding($st_temp, 'UTF8', 'cp1252');
			array_push($a_convert, $st_temp);
		}
		// ---- fin modif UTF8
		$this->a_params = array();
		return	$a_convert;
	}

	/* ----- résultat = plusieurs lignes, une colonne, tableau de résultat non indexé ----- */
	public function sql_selectUtf8($pst_requete)
	{
		$a_resultat = array();
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("sql_select impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		while (list($st_champ) = $stmt->fetch(PDO::FETCH_NUM)) {
			// ---- modif UTF8
			$st_champ = mb_convert_encoding($st_champ, 'UTF8', 'cp1252');
			// ---- fin modif UTF8
			$a_resultat[] = $st_champ;
		}
		$this->a_params = array();
		return $a_resultat;
	}

	/* ----- résultat = plusieurs lignes, une colonne, tableau de résultat indexé par valeur ----- */
	public function liste_clef_par_valeurUtf8($pst_requete)
	{
		$a_resultat = array();
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("liste_clef_par_valeur impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		while (list($i_idf, $st_nom) = $stmt->fetch(PDO::FETCH_NUM)) {
			// ---- modif UTF8
			$i_idf = mb_convert_encoding($i_idf, 'UTF8', 'cp1252');
			$st_nom = mb_convert_encoding($st_nom, 'UTF8', 'cp1252');
			// ---- fin modif UTF8
			$a_resultat[strval($st_nom)] = $i_idf;
		}
		$this->a_params            = array();
		return $a_resultat;
	}

	/* ----- résultat = plusieurs lignes, une colonne, tableau de résultat indexé par clé ----- */
	public function liste_valeur_par_clefUtf8($pst_requete)
	{
		$a_resultat = array();
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("liste_valeur_par_clef impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		while (list($i_idf, $st_nom) = $stmt->fetch(PDO::FETCH_NUM)) {
			// ---- modif UTF8
			$i_idf = mb_convert_encoding($i_idf, 'UTF8', 'cp1252');
			$st_nom = mb_convert_encoding($st_nom, 'UTF8', 'cp1252');
			// ---- fin modif UTF8
			$a_resultat[$i_idf] = strval($st_nom);
		}
		$this->a_params            = array();
		return $a_resultat;
	}

	/* ----- résultat = plusieurs lignes, une colonne, tableau de résultat indexé par double clé ----- */
	public function liste_valeur_par_doubles_clefsUtf8($pst_requete)
	{
		$a_resultat = array();
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("liste_valeur_par_doubles_clefs impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		while ($a_champs = $stmt->fetch(PDO::FETCH_NUM)) {
			$st_clef1 = array_shift($a_champs);
			$st_clef2 = array_shift($a_champs);
			// ---- modif UTF8
			$st_clef1 = mb_convert_encoding($st_clef1, 'UTF8', 'cp1252');
			$st_clef2 = mb_convert_encoding($st_clef2, 'UTF8', 'cp1252');
			$a_champs = mb_convert_encoding($a_champs, 'UTF8', 'cp1252');
			// ---- fin modif UTF8
			$a_resultat[strval($st_clef1)][strval($st_clef2)] = $a_champs;
		}
		$this->a_params            = array();
		return $a_resultat;
	}

	/* ----- résultat = plusieurs lignes, plusieurs colonnes, tableau de résultat non indexé ----- */
	public function sql_select_multipleUtf8($pst_requete)
	{
		$a_resultat = array();

		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			print_r($this->db->errorInfo());
			throw new Exception("sql_select_multiple impossible: ");
		}
		$this->stmt = $stmt;
		while ($a_champs = $stmt->fetch(PDO::FETCH_NUM)) {
			$a_ligne = array();
			// ---- modif UTF8
			foreach ($a_champs as $st_temp) {
				$st_temp = mb_convert_encoding($st_temp, 'UTF8', 'cp1252');
				array_push($a_ligne, $st_temp);
			}
			// ---- fin modif UTF8
			array_push($a_resultat, $a_ligne);
			unset($a_ligne);
		}
		$this->a_params            = array();
		return $a_resultat;
	}

	/* ----- résultat = plusieurs lignes, plusieurs colonnes, tableau de résultat indexé par une clé ----- */
	public function sql_select_multiple_par_idfUtf8($pst_requete)
	{
		$a_resultat = array();
		$a_convert = array();

		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("sql_select_multiple_par_idf impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		while ($a_champs = $stmt->fetch(PDO::FETCH_NUM)) {
			$i_idf = array_shift($a_champs);
			// ---- modif UTF8
			$i_idf = mb_convert_encoding($i_idf, 'UTF8', 'cp1252');
			foreach ($a_champs as $a_temp) {
				$st_temp = mb_convert_encoding($a_temp, 'UTF8', 'cp1252');
				array_push($a_convert, $st_temp);
			}
			// ---- fin modif UTF8
			$a_resultat["$i_idf"] = $a_convert;
			unset($a_convert);
		}
		$this->a_params = array();
		return $a_resultat;
	}

	public function execute_requete($pst_requete)
	{
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			$st_msg = $this->db->errorInfo()[2];
			throw new Exception("execute_requete impossible: $st_msg");
		}
		$this->stmt = $stmt;
		$this->a_params            = array();
	}

	public function dernier_idf_insere()
	{
		return $this->db->lastInsertId();
	}

	/**
	 * Renvoie le nombre de lignes détruites ou modifiées par la dernière commande
	 * @return integer nombre de lignes affectées par la dernire commande, update, execute
	 */
	function nb_lignes_affectees()
	{
		return $this->stmt->rowCount();
	}

	/**
	 * Renvoie la ligne suivante du résultat d'une requête
	 * @return array ou FALSE si plus de r�sultat
	 */
	function ligne_suivante_resultat()
	{
		return $this->stmt->fetch(PDO::FETCH_NUM);
	}

	/**
	 ** Renvoie le dernier message d'erreur
	 */
	public function msg_erreur()
	{
		return $this->db->errorInfo()[2];
	}

	public function find($pst_requete)
	{
		$stmt = $this->db->prepare($pst_requete);
		if (!$stmt->execute($this->a_params)) {
			throw new Exception("sql_select_liste impossible: ");
			print_r($this->db->errorInfo());
		}
		$this->stmt = $stmt;
		$this->a_params = array();
		return	$stmt->fetch(PDO::FETCH_ASSOC);
	}
}
