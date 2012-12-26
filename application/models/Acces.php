<?php
/**
 * Modèle de la table acces
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

class Table_Acces extends Zend_Db_Table_Abstract
{
	protected $_name = 'acces';
	
	/**
	 * id INTEGER PRIMARY AUTOINCREMENT DEFAULT NULL
	 * name VARCHAR NOT NULL
	 * admin BOOL NOT NULL DEFAUlT 0
	 */
	 
	 /**
	  * Permet de savoir si l'user est admin ou non
	  * @param int $id : L'id de l'user
	  * @return bool : True si l'user est admin, false sinon
	  */
	 public function get_admin($id)
	 {
	 	$req = $this->select()->from($this->_name, 'admin')->where('id=?', $id);
		$res = $this->fetchRow($req)->toArray();
		return $res['admin'];
	 }
	 
	 /**
	  * Récupère l'id pour un accès visiteur
	  * @return int : L'id
	  */
	 public function idVisiteur()
	 {
	 	$req = $this->select()->from($this->_name, 'id')->where('admin=0');
		$res = $this->fetchRow($req)->toArray();
		return $res['id'];
	 }
	 
	 /**
	  * Récupère l'id pour un accès admin
	  * @return int : L'id
	  */
	 public function idAdmin()
	 {
	 	$req = $this->select()->from($this->_name, 'id')->where('admin=1');
		$res = $this->fetchRow($req)->toArray();
		return $res['id'];
	 }
}
?>
