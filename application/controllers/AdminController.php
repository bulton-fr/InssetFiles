<?php
/**
 * Controleur : Admin
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

class AdminController extends Zend_Controller_Action
{
	/**
	 * à l'initialisation de la classe, déclaration des js et css
	 */
	public function init()
	{
		$this->headStyleScript = array(
			'css' => array('admin','user'),
			'js' => array('admin', 'user')
		);
	}
	
    /**
	 * Page d'accueil
	 */
	public function indexAction()
	{
		//Si on est connecté
		if(Zend_Auth::getInstance()->hasIdentity())
		{
			$infosUser = Zend_Auth::getInstance()->getStorage()->read();
			$isAdmin = $infosUser->isAdmin;
			
			if($isAdmin == true) //Si on est admin
			{
				//Appel de l'action header
				$this->_helper->actionStack('header', 'index', 'default', array('arr' => $this->headStyleScript));
				
				//Appel des modèles
				$tableDir = new Table_Dossier;
				$tableUsers = new Table_Users;
				$tableAcces = new Table_Acces;
				$tableOffres = new Table_Offres;
				
				$select = $tableUsers->liste(); //On récupère la liste des users
				
				//On créer la pagination
				$paginator = Zend_Paginator::factory($select);
				$paginator->setItemCountPerPage(9);
				$paginator->setCurrentPageNumber($this->_getParam('page', 1));
				Zend_Paginator::setDefaultScrollingStyle('Elastic');
				Zend_View_Helper_PaginationControl::setDefaultViewPartial('controls.phtml');
				
				$infosUsers = array(); //Tableau contenant toutes les infos à retourner
				foreach($paginator as $item)
				{
					$idUser = $item->id; //L'id de l'user
					$isAdmin = $tableAcces->get_admin($item->acces); //Si l'user est admin
					$idDirRoot = $tableDir->idDirRoot($idUser); //L'id du dossier root de l'user
					$infosDir = $tableDir->infosDossier($idDirRoot); //Les infos sur le dossier root
					$infosOffres = $tableOffres->infos($item->offre); //Les infos sur l'offre qu'à l'user
					
					$maxOctet = $tableOffres->MaxOctet($item->offre); //Le maximum que peux stocker l'user en octet
					$use = (int) $infosDir['size_use']; //La taille en octet utilisé par l'user
					$usePrct = floor(($use * 100) / $maxOctet); //La taille utilisé par l'user en %
					
					//On stock les infos dans l'array
					$infosUsers[$idUser]['infos'] = $item;
					$infosUsers[$idUser]['isAdmin'] = $isAdmin;
					$infosUsers[$idUser]['useAff'] = offreAff($infosDir['size_use']);
					$infosUsers[$idUser]['limit'] = $infosOffres['limit'];
					$infosUsers[$idUser]['limit_unit'] = $infosOffres['limit_unite'];
					$infosUsers[$idUser]['usePrct'] = $usePrct;
					$infosUsers[$idUser]['useOctet'] = $infosDir['size_use'];
					$infosUsers[$idUser]['maxOctet'] = $maxOctet;
				}
				
				$this->view->infosUsers = $infosUsers; //En envoi le tableau à la vue
				
				//On récupère la liste des offres disponible et on l'envoi à la vue
				$lstOffres = $tableOffres->fetchAll()->toArray();
				$this->view->offres = $lstOffres;
				$this->view->paginator = $paginator;
			}
			else {$this->_helper->redirector('index', 'index');} //Si l'user n'est pas admin, on redirige vers l'index
		}
		else {$this->_helper->redirector('index', 'index');} //Si l'user n'est pas admin, on redirige vers l'index
	}
	
	/**
	 * Suppression d'un user, de ces dossiers et ces fichiers
	 */
	public function supprAction()
	{
		//Si on est connecté
		if(Zend_Auth::getInstance()->hasIdentity())
		{
			$infosUser = Zend_Auth::getInstance()->getStorage()->read();
			$isAdmin = $infosUser->isAdmin;
			
			if($isAdmin == true) //Si on est admin
			{
				//On change de layout
				$layout = Zend_Layout::getMvcInstance();
				$layout->setLayout('vierge');
				
				$id = $this->_getParam('id', null); //On récupère l'id de l'user
				if($id != null) //Si un id a été passé
				{
					//On supprime l'user
					$tableUser = new Table_Users;
					$tableUser->suppr($id);
					
					//On supprime le dossier
					$tableDossier = new Table_Dossier;
					$tableDossier->supprAll($idUser);
					
					//On supprime le fichier
					$tableFichier = new Table_Fichiers;
					$tableFichier->supprAll($idUser);
					
					echo '1';
				}
				else {echo '0';} //S'il n'y a pas d'id, on affiche 0 pour indiquer une erreur
			}
			else {echo '0';} //Si l'user n'est pas admin, on affiche 0 pour indiquer une erreur
		}
		else {echo '0';} //Si l'user n'est pas admin, on affiche 0 pour indiquer une erreur
		
		exit;
	}
	
	/**
	 * Change l'offre d'un user
	 */
	public function offreAction()
	{
		//Si on est connecté
		if(Zend_Auth::getInstance()->hasIdentity())
		{
			$infosUser = Zend_Auth::getInstance()->getStorage()->read();
			$isAdmin = $infosUser->isAdmin;
			
			if($isAdmin == true) //Si on est admin
			{
				//On change de layout
				$layout = Zend_Layout::getMvcInstance();
				$layout->setLayout('vierge');
				
				$idUser = $this->_getParam('id', null); //On récupère l'id de l'user
				$idOffre = $this->_getParam('offre', null); //On récupère l'id de la nouvelle offre voulue
				
				if($idUser != null && $idOffre != null) //Si les 2 paramètres on bien été passés
				{
					$idOffre = substr($idOffre, 4); //On enlève le surplus sur l'id
					
					//On déclare le modèle
					$tableUser = new Table_Users;
					$tableOffre = new Table_Offres;
					$tableDossier = new Table_Dossier;
					
					//Si l'user et l'offre existe
					if($tableUser->ifExist('id', $idUser) && $tableOffre->ifExist($idOffre))
					{
						$infosOffre = $tableOffre->infos($idOffre); //On récupère les infos sur l'offre voulu
						$idDirRoot = $tableDossier->idDirRoot($idUser); //On récupère l'id du dossier root de l'user
						$infosDirRoot = $tableDossier->infosDossier($idDirRoot); //On récupère les infos sur le dossier root
						
						//Si c'est possible de passer sur l'offre: L'user n'utilise pas plus de place que ce qui est dispo sur la nouvelle offre
						if($infosDirRoot['size_use'] <= Octet($infosOffre['limit'], $infosOffre['limit_unite']))
						{
							//On change d'offre
							$tableUser->modifieOffre($idUser, $idOffre);
							echo '1';
						}
						else {echo '01';} //On affiche 0 pour indiquer une erreur
					}
					else {echo '02';} //On affiche 0 pour indiquer une erreur
				}
				else {echo '03';} //On affiche 0 pour indiquer une erreur
			}
			else {echo '04';} //Si l'user n'est pas admin, on affiche 0 pour indiquer une erreur
		}
		else {echo '05';} //Si l'user n'est pas admin, on affiche 0 pour indiquer une erreur
		
		exit;
	}
}