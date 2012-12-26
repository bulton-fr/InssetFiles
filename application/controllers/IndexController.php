<?php
/**
 * Controleur : Index
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

class IndexController extends Zend_Controller_Action
{
	/**
	 * à l'initialisation de la classe, déclaration des js et css
	 */
	public function init()
	{
		$this->headStyleScript = array(
			'css' => 'index',
			'js' => 'index'
		);
	}
	
    /**
	 * Page d'accueil
	 */
	public function indexAction()
	{
		$this->_helper->actionStack('header', 'index', 'default', array('arr' => $this->headStyleScript));
		
		//Vérification de si l'user se connecte
		$login = $this->_getParam('login', null);
		$pwd = $this->_getParam('mdp', null);
		
		if($login != null && $pwd != null)
		{
			$pwd = hashPwd($pwd); //On hash le mot de passe entré
			$auth = Zend_Auth::getInstance();
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			
			$dbAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'login', 'pwd');
			$dbAdapter->setCredential($pwd)
					  ->setIdentity($login);
			$result = $auth->authenticate($dbAdapter);
			
			if($result->isValid()) //Si l'user est trouvé
			{
				//On retourne toutes les infos de la table user sauf le mdp pour l'user qui se connecte
				$data = $dbAdapter->getResultRowObject(null, 'pwd');
				
				$tableUser = new Table_Acces(); //Modèle de la table Acces
				//Récupère l'info de si l'user est admin et l'ajoute aux infos stocké par zend_auth
				$data->isAdmin = $tableUser->get_admin($data->acces);
				
				$auth->getStorage()->write($data); //Ecris les infos dans zend_auth
			}
		}
		
		//Si on est connecté
		if(Zend_Auth::getInstance()->hasIdentity())
		{
			$infosUser = Zend_Auth::getInstance()->getStorage()->read();
			$isAdmin = $infosUser->isAdmin;
			
			if($isAdmin == false) {$this->_helper->redirector('index', 'user');} //Si c'est un user, on redirige vers l'index de l'user
			else {$this->_helper->redirector('index', 'admin');} //Si c'est un admin, on redirige vers l'index de l'admin
			
			exit;
		}
		
		//On récupère la liste des offres
		$tableOffres = new Table_Offres();
		$offres = $tableOffres->fetchAll()->toArray();
		
		$offres_ligne = array();
		$i_ligne = 0;
		$i = 1;
		
		//Traitement sur les offres
		//L'objectif étant de n'avoir que 3 par lignes.
		foreach($offres as $key => $val)
		{
			$offres[$key]['suite'] = true; //On dit que par défaut on est pas en fin de ligne et qu'il y a une autre offre après
			
			if($i > 3) //Si on dépasse les 3 éléments
			{
				$i_ligne++; //On augmente le numéro de ligne
				$i = 1; //On indique repasser au 1er élément
			}
			elseif($i == 3) {$offres[$key]['suite'] = false;} //Si on est en fin de ligne, on l'indique
			
			$offres_ligne[$i_ligne][] = $offres[$key]; //Et on stock l'info dans le nouvelle array
			$i++; //On augmente le numéro de l'élément
		}
		
		$this->view->offres = $offres_ligne; //On envoi le tableau des offres à la vue
	}
	
	/**
	 * Le header
	 */
	public function headerAction()
	{
		//On obtient l'info de si l'user est connecté ou pas
		$this->view->logged = $logged = Zend_Auth::getInstance()->hasIdentity();
		if($logged) //Si l'user est connecté, on récupère l'info de s'il est admin ou pas
		{
			$infosUser = Zend_Auth::getInstance()->getStorage()->read();
			$this->view->isAdmin = $infosUser->isAdmin; //On envoi à la vue l'info de s'il est admin ou pas
		}
		
		$arr = $this->_getParam('arr', null); //On récupère l'info sur les css et js à mettre
		if(is_array($arr)) //Si c'est bien un array
		{
			if(isset($arr['css'])) //Si des css on été indiqué
			{
				if(!is_array($arr['css'])) {$arr['css'] = array($arr['css']);} //Si c'est pas un array, on le passe en array
				$css = $arr['css'];
			}
			else {$css = null;}
			
			if(isset($arr['js'])) //Si des js on été indiqué
			{
				if(!is_array($arr['js'])) {$arr['js'] = array($arr['js']);} //Si c'est un array, on le passe en array
				$js = $arr['js'];
			}
			else {$js = null;}
		}
		else {$css = $js = null;}
		
		//On envoi les tableau de css et de js au layout
		$layout = Zend_Layout::getMvcInstance();
		$view = $layout->getView();
		$view->css = $css;
		$view->js = $js;
		
		//On indique que la vue header sera à mettre dans le segment header dans le layout
		$this->_helper->viewRenderer->setResponseSegment('header');
		
		//Appel de l'action footer
		$this->_helper->actionStack('footer','index','default',array());
	}
    
	/**
	 * Le footer
	 * Toute la vue est mise dans le segment footer dans le layout
	 */
	public function footerAction() {$this->_helper->viewRenderer->setResponseSegment('footer');}
}