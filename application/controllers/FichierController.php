<?php
/**
 * Controleur : Fichier
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

class FichierController extends Zend_Controller_Action
{
	/**
	 * Ajouter un fichier à un dossier
	 */
	public function addAction()
	{
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('iframe');
		$view = $layout->getView();
		$view->css = array('user');
		$view->js = array('user');
		
		$idDir = $this->_getParam('dir', 0); //On récupère l'id du dossier dans lequel ajouter le fichier
		
		$tableDossier = new Table_Dossier; //Le modèle de la table dossier
		$infoDir = $tableDossier->infosDossier($idDir); //les infos sur le dossier
		
		//Si l'user à bien accès au dossier mit en paramètre (vérifie aussi si l'user est connecté)
		if(accesUserDir($idDir, $infoDir))
		{
			//récupération des infos sur le dossier root
			$infosUser = Zend_Auth::getInstance()->getStorage()->read();
			$idUser = $infosUser->id; //On récupère l'id de l'user
			$idDirRoot = $tableDossier->idDirRoot($idUser); //L'id du dossier root
			$infoDirRoot = $tableDossier->infosDossier($idDirRoot); //Les infos sur le dossier root
			
			//Calcul de la taille maximum d'un fichier
			$tableOffres = new Table_Offres; //le modèle de la table offre
			$maxOctet = $tableOffres->MaxOctet($infosUser->offre);
			$use = (int) $infoDirRoot['size_use'];
			$max_upload = $maxOctet - $use;
			
			//Taille maxi qu'il est possible d'uploader
			$max_GetIni = ini_get('upload_max_filesize');
			$max_iniVal = substr($max_GetIni, 0, -1);
			$max_iniUnit = substr($max_GetIni, -1).'o';
			$max_ini = Octet($max_iniVal, $max_iniUnit);
			
			if($max_ini <= $max_upload) {$maxOctet = $max_ini;}
			else {$maxOctet = $max_upload;}
			
			$form = new Zend_Form;
			$form->setAction($this->view->baseUrl('/fichier/up/dir/'.$idDir));
			$form->setAttrib('id', 'newFile');
			$form->setAttrib('enctype', 'multipart/form-data');
			
			$file = new Zend_Form_Element_File('file');
			$file->setLabel('');
			$file->setDestination(APPLICATION_PATH.'/../public_html/upload'); //Le répertoire de destination
			$file->addValidator('Count', false, 1); //1 seul fichier à la fois
			$file->addValidator('Size', false, $maxOctet); //La limite max
			$form->addElement($file);
			
			$submit = new Zend_Form_Element_Submit('submit');
			$submit->setLabel('');
			$form->addElement($submit);
			
			$form->addElement('hidden', 'limit', array('label' => 'Taille maximum du fichier : '.offreAff($maxOctet)));
			$form->addElement('hidden', 'dir', array('value' => $idDir));
			$form->addElement('hidden', 'max', array('value' => $maxOctet));
			
			$this->view->form = $form;
		}
		else {echo 'NaN';exit;} //Sinon on retour "NaN" pour indiquer une erreur
	}
	
	public function upAction()
	{
		$auth = Zend_Auth::getInstance()->getStorage(); //Pour démarrer la session avant le début de l'affichage.
		
		//On change de layout
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('iframe');
		$view = $layout->getView();
		$view->css = array('user');
		$view->js = array('user');//On change de layout
		
		$max = $this->_getParam('max');
		
		//On réécrie sommairement le formulaire pour sa validation.
		$form = new Zend_Form;
		$form->setAttrib('id', 'newFile');
		
		$file = new Zend_Form_Element_File('file');
		$file->setRequired(true);
		$file->setDestination(APPLICATION_PATH.'/../public_html/upload'); //Le répertoire de destination
		$file->addValidator('Count', false, 1); //1 seul fichier à la fois
		$file->addValidator('Size', false, $max); //La limite max
		
		$form->addElement($file);
		$form->addElement('hidden', 'max');
		$form->addElement('hidden', 'dir');
		$form->addElement('submit', 'send');
		
		//On indique à l'user qu'on est en train d'up
		echo 'Upload en cours ...<br/><br/>';
		ob_flush(); //On affiche le texte, on n'attends pas la fin du script (donc affiché avant l'up).
		
		if($this->_request->isPost())
		{
			$formData = $this->_request->getPost();
			if($form->isValid($formData))
			{	
				//Upload réussi, on récupère des infos
				$uploadedData = $form->getValues();
				$fullFilePath = $form->file->getFileName();
				
				//On récupère les infos sur le dossier et l'user
				$idDir = $this->_getParam('dir');
				$infosUser = $auth->read();
				$idUser = $infosUser->id; //On récupère l'id de l'user
				
				//On récupère le nom originel du fichier et son path sur le serveur
				$ex = explode('/', $fullFilePath);
				$cnt = count($ex) -1;
				$nom_ori = $ex[$cnt];
				unset($ex[$cnt]);
				$path = implode('/', $ex).'/'; //Recréation du path sans le nom du fichier à la fin
				
				//On génère un nom aléatoire mais on vérifie qu'il n'existe pas déjà pour éviter d'écraser un fichier déjà existant.
				//(L'aléatoire à ces limites)
				$exist = true;
				do
				{
					if($exist == true) {$newName = uniqid($idUser.'_');}
					$exist = file_exists($path.$newName);
				}
				while($exist == true);
				
				rename($fullFilePath, $path.$newName); //On renomme le fichier sur le serveur
				$size = filesize($path.$newName); //On récupère sa taille
				
				//Les modèles de tables
				$tableFichier = new Table_Fichiers;
				$tableDossier = new Table_Dossier;
				
				//On ajout le fichier en bdd
				$tableFichier->add($nom_ori, $idUser, $idDir, $size, $newName);
				
				//On met à jour les tailles utilisé dans le dossier où est le fichier et ces dossiers supérieurs
				$infos = $tableDossier->infosDossier($idDir);
				if($infos['root'] != 0) //Si on n'est pas dans le dossier racine de l'user
				{
					$idD = $idDir;
					
					//On récupère l'id et le nom pour chaque dossier de l'arborescence
					do
					{
						$infos = $tableDossier->infosDossier($idD);
						$idD = $infos['root'];
						
						if($idD != 0) {$tableDossier->majSize($idDir, $size);}
					}
					while($idD != 0);
				}
				else {$tableDossier->majSize($idDir, $size);}
				
				//Affichage des infos à l'user
				echo 'Upload terminé.';
				echo '<script type="text/javascript">';
					echo 'LoadDir('.$idDir.', 1);';
					echo 'MajSizeTotal(1); //On réactualise la taille utilisé par l\'user'."\n";
					echo '$(\'.overlay\', top.document).hide(); //Et on cache le bloc de l\'overlay'."\n";
				echo '</script>';
			}
			else
			{
				$getMsg = $form->getMessages();
				if(isset($getMsg['file']['fileSizeTooBig']))
				{
					echo 'Votre fichier est trop gros.<br/>';
					echo 'Vous pouvez cependant modifier votre offre pour stocker plus de fichier.';
				}
				else {echo '[] Une erreur s\'est produite durant l\'upload';}
			}
		}
		else {echo 'Une erreur s\'est produite durant l\'upload';}
	}

	public function telechargerAction()
	{
		//On change de layout
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('vierge');
		
		//Si on est connecté
		if(Zend_Auth::getInstance()->hasIdentity())
		{
			$infosUser = Zend_Auth::getInstance()->getStorage()->read();
			$idUser = $infosUser->id; //On récupère l'id de l'user
			
			$idFile = $this->_getParam('idFile', null);
			if($idFile != null)
			{
				$tableFile = new Table_Fichiers;
				$infos = $tableFile->infos($idFile);
				
				if($infos['user'] == $idUser)
				{
					header('Content-disposition: attachment; filename='.$infos['name']); 
					header('Content-Type: application/force-download'); 
					header('Content-Transfer-Encoding: binary');
			        header('Pragma: no-cache'); 
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0, public'); 
					header('Expires: 0'); 
					
					readfile(APPLICATION_PATH.'/../public_html/upload/'.$infos['nom_stock']);
					exit; 
				}
				else {echo 'Erreur : Le fichier ne vous appartient pas.';}
			}
			else {echo 'Erreur : Le fichier n\'est pas indiqué.';}
		}
		else {echo 'erreur: Vous devez être connecté';}
	}
	
}
?>