<?php
/**
 * Modèle de la table users
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

class Table_Users extends Zend_Db_Table_Abstract
{
	protected $_name = 'users'; //Le nom de la table
	
	/**
	 * id INTEGER PRIMARY AUTOINCREMENT DEFAULT NULL
	 * login VARCHAR NOT NULL
	 * pwd VARCHAR NOT NULL
	 * mail VARCHAR NOT NULL
	 * acces INTEGER NOT NULL DEFAULT 1
	 * offre INTEGER NOT NULL DEFAULT 1
	 * demande_change BOOL NOT NULL DEFAULT 0
	 */
	
	/**
	 * Vérifie si un utilisateur existe pour un login et un mot de passe
	 * @param string $login : Le login de l'user
	 * @param string $pwd : Le mot de passe de l'user
	 * @return int : Le nombre d'user trouvé
	 */
	public function verif($login, $pwd)
	{
		$req = $this->select()
					->from($this->_name, 'COUNT(id) AS nb')
					->where('login=?', $login)
					->where('pwd=?', $pwd);
		
		$res = $this->fetchRow($req)->toArray();
		return $res['nb'];
	}
	
	/**
	 * Vérifie si un utilisateur existe par rapport à un champ précis
	 * @param string $champ : Le champ à vérifier
	 * @param string $val : La valeur à vérifier
	 * @return bool : True s'il existe, false sinon
	 */
	public function ifExist($champ, $val)
	{
		$req = $this->select()
					->from($this->_name, 'id')
					->where($champ.'=?', $val);
		$res = $this->fetchRow($req);
		
		if($res == null) {return 0;}
		else {return 1;}
	}
	 
	 /**
	  * Ajoute un user
	  * @param string $mail : L'adresse email
	  * @param string $login : Le nom d'utilisateur
	  * @param string $pwd : Le mot de passe
	  * @param int $offre : L'offre choisie
	  */
	 public function add($mail, $login, $pwd, $offre)
	 {
	 	//On récupère l'id des accès visiteur
	 	$tableAcc = new Table_Acces;
		$acces = $tableAcc->idVisiteur();
	 	
		//Les informations à ajouter
	 	$data = array(
			'id' => null,
			'login' => $login,
			'pwd' => $pwd,
			'mail' => $mail,
			'acces' => $acces,
			'offre' => $offre,
			'demande_change' => 0
		);
		
		//L'insertion
		$idUser = $this->insert($data);
		
		//Ajout du dossier racine de l'user
		$tableDir = new Table_Dossier;
		$tableDir->add($idUser, 'root', 0);
	}
	
	/**
	 * Liste tous les utilisateurs par ordre de login
	 * @return Zend_Db_Table_Select : La requête sql
	 */
	public function liste() {return $this->select()->from($this->_name)->order('login');}
	
	/**
	 * Modifie une offre
	 * @param int $idUser : L'id de l'utilisateur
	 * @param int $newOffre : L'id de la nouvelle offre 
	 */
	public function modifieOffre($idUser, $newOffre)
	{
		$data = array('offre' => $newOffre, 'demande_change' => 0);
        $where = $this->getAdapter()->quoteInto('id = ?', $idUser);
        $this->update($data, $where);
	}

	/**
	 * Suppression d'un utilisateur
	 * @param int $idUser : L'id de l'utilisateur
	 */
	public function suppr($idUser)
	{
		$where = $this->getAdapter()->quoteInto('id = ?', $idUser);
        $this->delete($where);
	}
	
	/**
	 * Retourne les users admin
	 * @return array
	 */
	public function LstAllAdmin()
	{
		$tableAcces = new Table_Acces;
		$idAccAdmin = $tableAcces->idAdmin();
		
		$req = $this->select()->from($this->_name)->where('acces=?', $idAccAdmin);
		$res = $this->fetchAll($req);
		
		if($res != null) {return $res->toArray();}
		else {return array();}
	}
	
	/**
	 * Modifie la valeur de la demande de changement d'offre
	 * @param int $idUser : L'id de l'utilisateur
	 * @param int $newOffre : L'id de la nouvelle offre 
	 */
	public function askOffre($idUser, $newOffre)
	{
		$data = array('demande_change' => $newOffre);
        $where = $this->getAdapter()->quoteInto('id = ?', $idUser);
        $this->update($data, $where);
	}
}
?>
