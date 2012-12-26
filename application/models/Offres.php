<?php
/**
 * Modèle de la table offres
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

class Table_Offres extends Zend_Db_Table_Abstract
{
	protected $_name = 'offres';
	
	/**
	 * id INTEGER PRIMARY AUTOINCREMENT DEFAULT NULL
	 * name VARCHAR NOT NULL
	 * limit INTEGER NOT NULL
	 * limit_unite VARCHAR NOT NULL DEFAULT "Mo"
	 */
	
	/**
	 * Retourne les informations sur une offre
	 * @param int $idOffre : L'id de l'offre
	 * @return array : Les informations sur l'offre
	 */
	public function infos($idOffre)
	{
	 	$req = $this->select()
					->from($this->_name)
					->where('id=?', $idOffre);
		return $this->fetchRow($req)->toArray();
	}
	
	/**
	 * Retourne la taille max en octet
	 * @param int $idOffre : L'id de l'offre
	 * @return int : La taille max en octet
	 */
	public function MaxOctet($idOffre)
	{
	 	$req = $this->select()
					->from($this->_name)
					->where('id=?', $idOffre);
		$res = $this->fetchRow($req)->toArray();
		
		return Octet($res['limit'], $res['limit_unite']);
	}
	
	/**
	 * Si l'offre existe
	 * @param int $id : L'id de l'offre recherché
	 * @return bool : True s'il existe, false sinon
	 */
	public function ifExist($id)
	{
		$req = $this->select()->from($this->_name, 'id')->where('id=?', $id);
		$res = $this->fetchRow($req);
		
		if($res == null) {return false;}
		else {return true;}
	}
}
?>
