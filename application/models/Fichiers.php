<?php
/**
 * Modèle de la table fichiers
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

class Table_Fichiers extends Zend_Db_Table_Abstract
{
	protected $_name = 'fichiers';
	
	/**
	 * id INTEGER PRIMARY AUTOINCREMENT DEFAULT NULL
	 * name VARCHAR NOT NULL
	 * user INTEGER NOT NULL
	 * dossier INTEGER NOT NULL
	 * size INTEGER NOT NULL DEFAULT 0
	 * create DATETIME NOT NULL
	 * nom_stock VARCHAR NOT NULL
	 */
	 
	 /**
	  * Liste les fichiers d'un dossier
	  * @param int $dir : Id du dossier
	  * @param int $user : Id de l'user
	  * @param bool $triName [opt] : True si on tri par nom, false sinon
	  * @return array : Les dossiers et leurs infos
	  */
	 public function lstFiles($dir, $user, $triName=false)
	 {
	 	$req = $this->select()
					->from($this->_name, array('id', 'name', 'create', 'size'))
					->where('dossier=?', $dir)
					->where('user=?', $user);
		if($triName == true) {$req->order('name ASC');}
		
		return $this->fetchAll($req)->toArray();
	 }
	
	/**
	 * Supprime tous les fichiers de l'utilisateur
	 * @param int $idUser : L'id de l'utilisateur
	 */
	public function supprAll($idUser)
	{
		$req = $this->select()->from($this->_name, 'nom_stock')->where('user=?', $idUser);
		$res = $this->fetchAll()->toArray();
		
		//Suppression du fichier (bdd & serveur)
		$where = $this->getAdapter()->quoteInto('user = ?', $idUser);
		$this->delete($where);
		
		foreach($res as $val) {unlink(APPLICATION_PATH.'/../public/upload'.$val['nom_stock']);}
	}
	
	/**
	 * Sous requête permettant de connaître le nombre de fichier que contient un dossier
	 * @return Zend_Db_Table_Select : L'instance de la sous-requête
	 */
	public function SousReqNbFile()
	{
		$reqNbFile = $this->select()->setIntegrityCheck(false)
						  ->from('fichiers', 'COUNT(id)')
						  ->where('dossier=d.id');
		return $reqNbFile;
	}
	
	/**
	 * Ajoute un fichier
	 * @param string $name : Le nom du fichier
	 * @param int $idUser : L'id de l'user
	 * @param int $idDir : L'id du dossier
	 * @param int $size : La taille du fichier
	 * @param int $nom_stock : Le nom du fichier sur le serveur
	 */
	public function add($name, $idUser, $idDir, $size, $nom_stock)
	{
		$dateCreate = new Zend_Date;
		$data = array(
			'id' => null,
			'name' => $name,
			'user' => $idUser,
			'dossier' => $idDir,
			'size' => $size,
			'create' => DateFormat_SQL($dateCreate),
			'nom_stock' => $nom_stock
		);
		
		$this->insert($data);
		return 1;
	}
	
	/**
	 * Supprime un fichier
	 * @param int $idFile : L'id du fichier à supprimer
	 */
	public function suppr($idFile, $idUser)
	{
		//Récup des infos sur le fichier
		$req = $this->select()->from($this->_name, array('dossier', 'size', 'nom_stock'))->where('id=?', $idFile);
		$res = $this->fetchRow($req)->toArray();
		$size = $res['size'];
		$idDir = $res['dossier'];
		$nom = $res['nom_stock'];
		
		//Maj de la taille prise par le dossier
		$tableDir = new Table_Dossier;
		$tableDir->majSize($idDir, $size, 0);
		
		//Suppression du fichier (bdd & serveur)
		$where = array();
		$where[] = $this->getAdapter()->quoteInto('id = ?', $idFile);
		$where[] = $this->getAdapter()->quoteInto('user = ?', $idUser);
		$this->delete($where);
		unlink(APPLICATION_PATH.'/../public_html/upload/'.$nom);
	}
	
	/**
	 * Supprime tous les fichiers d'un dossier
	 * @param int $idDir : L'id du dossier où il faut supprimer
	 * @param int $idUser : L'id de l'user
	 */
	public function supprAllInDir($idDir, $idUser)
	{
		$req = $this->select()->from($this->_name)->where('dossier=?', $idDir)->where('user=?', $idUser);
		$res = $this->fetchAll($req);
		
		if($res != null)
		{
			$res = $res->toArray();
			if(count($res) > 0) {foreach($res as $val) {$this->suppr($val['id'], $idUser);}}
		}
	}
	
	/**
	 * Informations sur un fichier
	 * @param int $idFile : L'id du fichier
	 */
	public function infos($idFile)
	{
		$req = $this->select()->from($this->_name)->where('id=?', $idFile);
		$res = $this->fetchRow($req);
		
		if($res != null) {return $res->toArray();}
		else {return array();}
	}
}
?>
