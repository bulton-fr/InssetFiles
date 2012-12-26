<?php
/**
 * Modèle de la table dossier
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

class Table_Dossier extends Zend_Db_Table_Abstract
{
	protected $_name = 'dossier';
	
	/**
	 * id INTEGER PRIMARY AUTOINCREMENT DEFAULT NULL
	 * name VARCHAR NOT NULL
	 * user INTEGER NOT NULL
	 * nb_file INTEGER NOT NULL DEFAULT 0
	 * create DATETIME NOT NULL
	 * root INTEGER DEFAULT 0
	 * size_use INTEGER NOT NULL DEFAULT 0
	 */
	
	/**
	 * Liste les dossiers (ou sous-dossier) d'un user
	 * @param int $root : Id du dossier supérieur (0 si root)
	 * @param int $user : Id de l'user
	 * @param bool $triName [opt] : True si on tri par nom, false sinon
	 * @return array : Les dossiers et leurs infos
	 */
	public function lstDir($root, $user, $triName=false)
	{
		
		$tableFichier = new Table_Fichiers;
		$reqNbFile = $tableFichier->SousReqNbFile();
		
		$req = $this->select()->setIntegrityCheck(false)
					->from(array('d' => $this->_name), array(
						'id',
						'name',
						'size_use',
						'nbFile' => '('.new Zend_Db_Expr($reqNbFile).')'
					))
					->where('user=?', $user)
					->where('root=?', $root);
		if($triName == true) {$req->order('name ASC');}
		
		$res = $this->fetchAll($req);
		if($res != null) {return $res->toArray();}
		else {return array();}
	}
	
	/**
	 * Récupère les infos sur le dossier root d'un user
	 * @param int $user : L'id de l'user
	 * @return array : Les infos sur le dossier root
	 */
	public function idDirRoot($user)
	{
		$req = $this->select()
					->from($this->_name, 'id')
					->where('user=?', $user)
					->where('root=0');
		$res = $this->fetchRow($req)->toArray();
		return $res['id'];
	}
	
	/**
	 * Récupère les infos sur un dossier
	 * @param int $idDir : L'id du dossier
	 * @return array : Les infos sur le dossier
	 */
	public function infosDossier($idDir)
	{
		$req = $this->select()
					->from($this->_name)
					->where('id=?', $idDir);
		
		$res = $this->fetchRow($req);
		if($res == null) {return null;}
		else {return $res->toArray();}
	}
	
	/**
	 * Ajoute un dossier à un user
	 * @param int $idUser : L'id de l'user
	 * @param string $name : Le nom du dossier
	 * @param int $root : L'id du dossier au-dessus
	 */
	public function add($idUser, $name, $root)
	{
		if($root != 0)
		{
		 	$req = $this->select()
						->from($this->_name)
						->where('user=?', $idUser)
						->where('id=?', $root);
			$res = $this->fetchRow($req);
			if($res == NULL) {return 0;}
		}
		
		$dateCreate = new Zend_Date;
		$data = array(
			'id' => null,
			'name' => $name,
			'user' => $idUser,
			'create' => DateFormat_SQL($dateCreate),
			'root' => $root
		);
		
		$this->insert($data);
		return 1;
	}
	
	/**
	 * Supprime tous les dossiers d'un user
	 * @param int $idUser : L'id de l'utilisateur
	 */
	public function supprAll($idUser)
	{
		$where = $this->getAdapter()->quoteInto('user = ?', $idUser);
		$this->delete($where);
	}
	
	/**
	 * Mise à jour des tailles des dossiers
	 * @param int $idDir : L'id du dossier dans lequel se trouve le fichier
	 * @param int $size : La taille à ajouter aux dossiers
	 * @param bool $sens [opt] : Si 1 on ajoute, si 0 on soustrait
	 */
	public function majSize($idDir, $size, $sens=1)
	{
		$req = $this->select()->from($this->_name, 'size_use')->where('id=?', $idDir);
		$res = $this->fetchRow($req)->toArray();
		
		if($sens == 1) {$newSize = $res['size_use']+$size;}
		else {$newSize = $res['size_use']-$size;}
		
		$data = array('size_use' => $newSize);
        $where = $this->getAdapter()->quoteInto('id = ?', $idDir);
        $this->update($data, $where);
	}
	
	/**
	 * Supprime un dossier de l'user
	 * @param int $idDir : L'id du dossier
	 * @param int $idUser : L'id de l'user
	 */
	public function suppr($idDir, $idUser)
	{
		$req = $this->select()->from($this->_name, array('size_use', 'root'))->where('id=?', $idDir);
		$res = $this->fetchRow($req)->toArray();
		
		if($res['root'] != 0) //On vérifie que ce n'est pas le dossier racine de l'user
		{
			//Suppression du dossier en bdd
			$where = array();
			$where[] = $this->getAdapter()->quoteInto('id = ?', $idDir);
			$where[] = $this->getAdapter()->quoteInto('user = ?', $idUser);
			$this->delete($where);
			
			//Maj de la taille utilisé
			$this->majSize($res['root'], $res['size_use'], 0);
		}
	}
}
?>