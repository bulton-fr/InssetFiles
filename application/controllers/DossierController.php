<?php
/**
 * Controleur : Dossier
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

class DossierController extends Zend_Controller_Action
{
	/**
	 * Ajouter un sous-dossier
	 */
	public function addAction()
	{
		//On change de layout
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('vierge');
		
		$idDir = $this->_getParam('dir', 0); //L'id du dossier dans lequel ajouter un dossier
		$tableDossier = new Table_Dossier; //Modèle de la table des dossiers
		$infosDir = $tableDossier->infosDossier($idDir); //Les infos sur le dossier
		
		//Si l'user à bien accès au dossier mit en paramètre (vérifie aussi si l'user est connecté)
		if(accesUserDir($idDir, $infosDir))
		{
			$add = $this->_getParam('ajout', null); //Permet de savoir si on valide le formulaire ou l'affiche
			
			if($add == '1') //Si on valide le formulaire
			{
				//On récupère l'id de l'user
				$infosUser = Zend_Auth::getInstance()->getStorage()->read();
				$idUser = $infosUser->id;
				$name = trim($this->_getParam('name', '')); //On récupère le nom du nouveau dossier
				
				if(!empty($name)) //Si le nom n'est pas vide
				{
					$ret = $tableDossier->add($idUser, $name, $idDir); //Ajout du dossier
					if($ret == 0) {$ret = 'Erreur durant l\'ajout du dossier.';} //Si erreur durant l'ajout
					
					echo $ret;
				}
				else {echo 'Erreur durant l\'ajout du dossier.';} //Sinon on affiche un erreur
				
				exit;
			}
			else
			{
				//Le formulaire d'ajout de dossier
				$form = new Zend_Form;
				$form->addElement('text', 'name', array('label' => 'Nom du dossier'));
				
				$label  = '<img src="'.$this->view->baseUrl('/images/load.gif').'" alt="Chargement" id="load" />';
				$label .= '<img src="'.$this->view->baseUrl('/images/badge_save.png').'" alt="save" id="valid" />';
				
				$submit = new Zend_Form_Element_Hidden('submit');
				$submit->setLabel($label);
				$submit->addDecorators(array(array('Label', array('tag' => 'dt', 'escape' => false))));
				$form->addElement($submit);
				
				//Envoi du formulaire et de l'id du dossier à la vue
				$this->view->form = $form;
				$this->view->idDir = $idDir;
			}
		}
		else {echo 'NaN'; exit;} //Sinon on retour "NaN" pour indiquer une erreur
	}

	public function totaluseAction()
	{
		//On change de layout
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('vierge');
		
		//Si on est connecté
		if(Zend_Auth::getInstance()->hasIdentity())
		{
			$infosUser = Zend_Auth::getInstance()->getStorage()->read();
			$idUser = $infosUser->id; //On récupère l'id de l'user
			
			$tableDir = new Table_Dossier;
			$tableOffres = new Table_Offres;
			
			$idDirRoot = $tableDir->idDirRoot($idUser); //L'id du dossier root
			$infosDir = $tableDir->infosDossier($idDirRoot); //Les infos sur le dossier root
			
			//Les infos sur l'offre de l'user
			$infosOffres = $tableOffres->infos($infosUser->offre);
			
			//Envoi à la vue des infos sur l'offre et l'espace utilisé
			$limit = $infosOffres['limit'];
			$limit_unit = $infosOffres['limit_unite'];
			$useAff = offreAff($infosDir['size_use']);
			
			echo '<span>Espace utilisé : '.$useAff.' / '.$limit.$limit_unit.' disponible.</span>';
		}
	}
}
?>