<?php
/**
 * Controleur : User
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

class UserController extends Zend_Controller_Action
{
	/**
	 * à l'initialisation de la classe, déclaration des js et css
	 */
	public function init()
	{
		$this->headStyleScript = array(
			'css' => 'user',
			'js' => 'user'
		);
		
		//Recaptcha keys
		$this->publicKey = '6LeJKdoSAAAAAJ376XFkW_TLGJv32jQblhDDjqVo';
		$this->privateKey = '6LeJKdoSAAAAANe_ggpOx9XzS3osKqjqAgZsOvlK';
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
			$idUser = $infosUser->id; //On récupère l'id de l'user
			
			//Appel de l'action header
			$this->_helper->actionStack('header', 'index', 'default', array('arr' => $this->headStyleScript));
			
			//Modèles
			$tableDir = new Table_Dossier;
			$tableFile = new Table_Fichiers;
			$tableOffres = new Table_Offres;
			
			$idDirRoot = $tableDir->idDirRoot($idUser); //L'id du dossier root
			$this->view->infosDirRoot = $infosDir = $tableDir->infosDossier($idDirRoot); //Les infos sur le dossier root
			
			//Les infos sur l'offre de l'user
			$infosOffres = $tableOffres->infos($infosUser->offre);
			
			//Envoi à la vue des infos sur l'offre et l'espace utilisé
			$this->view->limit = $infosOffres['limit'];
			$this->view->limit_unit = $infosOffres['limit_unite'];
			$this->view->useAff = offreAff($infosDir['size_use']);
			
			//On récupère le maximum possible en octet
			$maxOctet = $tableOffres->MaxOctet($infosUser->offre);
			$use = (int) $infosDir['size_use'];
			
			//Puis on calcul l'espace libre disponible en %
			$this->view->usePrct = $usePrct = floor(($use * 100) / $maxOctet);
		}
		else {$this->_helper->redirector('index', 'index');}
	}

	/**
	 * Liste les dossiers et fichiers pour un répertoire donné
	 */
	public function lstdirfileAction()
	{
		//Si on appel l'action par ajax
		$ajax = $this->_getParam('ajax', null);
		if($ajax != null)
		{
			//On change de layout
			$layout = Zend_Layout::getMvcInstance();
			$layout->setLayout('vierge');
		}
		
		$idDir = $this->_getParam('dir'); //On récupère l'id du dossier
		$this->view->idDir = $idDir; //Et on l'envoi à la vue
		
		$infosUser = Zend_Auth::getInstance()->getStorage()->read();
		$idUser = $infosUser->id;
		
		//Modèles
		$tableDir = new Table_Dossier;
		$tableFile = new Table_Fichiers;
		$idDirRoot = $tableDir->idDirRoot($idUser); //L'id du dossier root de l'user
		
		$lstFile = $tableFile->lstFiles($idDir, $idUser, 1); //Récupère les fichiers pour l'id du dossier et l'user courant
		$lstDir = $tableDir->lstDir($idDir, $idUser, 1); //Récupère les dossiers pour l'id du dossier et l'user courant
		
		//Liste des fichiers
		foreach($lstFile as $key => $val)
		{
			if($val['create']) //Traitement pour la date d'upload
			{
				$date = new Zend_Date($val['create']);
				$lstFile[$key]['create'] = DateFormat_View($date);
			}
			else {$lstFile[$key]['create'] = 'Inconnue';}
			$lstFile[$key]['size'] = offreAff($val['size']); //Traitement pour la taille que fait le fichier
		}
		
		//Liste des dossiers, traitement pour la taille que fait le dossier
		foreach($lstDir as $key => $val) {$lstDir[$key]['size'] = offreAff($val['size_use']);}
		
		//Les infos à envoyer à la vue
		$lst = array();
		$lst['infos'] = $tableDir->infosDossier($idDir);
		$lst['file'] = $lstFile;
		$lst['dir'] = $lstDir;
		$lst['nbdirfile'] = count($lstDir) + count($lstFile);
		$this->view->lst = $lst;
		
		//L'arborescence
		$cheminArr = array();
		if($lst['infos']['root'] != 0) //Si on n'est pas dans le dossier racine de l'user
		{
			$idD = $idDir;
			
			//On récupère l'id et le nom pour chaque dossier de l'arborescence
			do
			{
				$infos = $tableDir->infosDossier($idD);
				$idD = $infos['root'];
				
				if($idD != 0) {$cheminArr[] = array('id' => $infos['id'], 'name' => $infos['name']);}
			}
			while($idD != 0);
			krsort($cheminArr); //Tri pour les remettre dans le bon ordre
		}
		$this->view->chemin = $cheminArr; //Envoi de l'arborescence à la vue
	}
	
	/**
	 * Formulaire d'ajour de nouvel utilisateur
	 * Appel via AJAX
	 */
	public function newAction()
	{
		//On change de layout
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('vierge');
		
		//Le formulaire d'inscription
		$form = new Zend_Form();
		$form->setMethod('post');
		$form->setAttrib('id', 'new'); //L'id du formulaire
		
		$tableOffres = new Table_Offres; //Le modèle de la table Offres
		$lstOffres = $tableOffres->fetchAll()->toArray(); //Liste toutes les offres
		
		//Traitement du tableau des offres
		$offres = array();
		foreach($lstOffres as $val) {$offres[$val['id']] = $val['name'].' : '.$val['limit'].$val['limit_unite'];}
		
		//Elément offre du formulaire
		$radio = new Zend_Form_Element_Radio('offres');
		$radio->setLabel('Choisissez votre offres : ');
		$radio->addMultiOptions($offres);
		
		//L'offre à sélectionner par défaut
		$idSelect = $this->_getParam('offre', $lstOffres[0]['id']);
		$radio->setValue($idSelect);
		$form->addElement($radio);
		
		$decorator = array(array('Description', array('tag' => 'p', 'escape' => false)));
		
		//L'élément mail
		$mail = new Zend_Form_Element_Text('mail');
		$mail->setLabel('Adresse e-mail :');
		$mail->setDescription('<img src="" alt="" /><div class="end_float"></div>');
		$mail->addDecorators($decorator);
		$form->addElement($mail);
		
		//L'élément login
		$login = new Zend_Form_Element_Text('login');
		$login->setLabel('Nom d\'utilisateur :');
		$login->addDecorators($decorator);
		$login->setDescription('<img src="" alt="" /><div class="end_float"></div>');
		$form->addElement($login);
		
		//L'élément mot de passe
		$pwd = new Zend_Form_Element_Password('pwd');
		$pwd->setLabel('Mot de passe :');
		$pwd->addDecorators($decorator);
		$pwd->setDescription('<img src="" alt="" /><div class="end_float"></div>');
		$form->addElement($pwd);
		
		//L'élément captcha
		$recaptcha = new Zend_Service_ReCaptcha($this->publicKey, $this->privateKey);
		$recaptcha->setOption('lang', 'fr');
		
		$captcha = new Zend_Form_Element_Captcha('captcha', array(
              'captcha' => 'ReCaptcha',
              'captchaOptions' => array('captcha' => 'ReCaptcha', 'service' => $recaptcha)
		));
		$form->addElement($captcha);
		
		//Le bouton de validation
		$form->addElement('submit', 'send', array('label' => 'Je m\'inscris'));
		
		//Envoi du formulaire à la vue.
		$this->view->form = $form;
	}
	
	/**
	 * Formulaire de connexion
	 * Appel via AJAX
	 */
	public function hiAction()
	{
		//On change de layout
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('vierge');
		
		$form = new Zend_Form;
		$form->setMethod('post');
		$form->setAttrib('id', 'hi');
		
		$form->addElement('text', 'login', array('value' => 'Login'));
		$form->addElement('password', 'mdp', array('value' => 'Mot de passe'));
		$form->addElement('submit', 'HiSend', array('label' => ''));
		
		$this->view->form = $form;
	}
	
	/**
	 * Déconnexion
	 */
	public function byeAction()
	{
		Zend_Auth::getInstance()->clearIdentity(); //Déconnexion pour Zend_auth
		$this->_helper->redirector('index', 'index'); //Redirection vers la méthode index du controleur index
	}
	
	/**
	 * Lorsqu'on reçoit une demande de connexion
	 * Appel via AJAX
	 */
	public function verifcoAction()
	{
		//On change de layout
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('vierge');
		
		//Récupération des valeurs
		$login = $this->_getParam('login', null);
		$mdp = $this->_getParam('mdp', null);
		$mdp = hashPwd($mdp);
		
		//Vérification en base de données
		$tableUser = new Table_Users;
		$verif = $tableUser->verif($login, $mdp);
		
		echo $verif;
		exit;
	}
	
	/**
	 * Vérifie si l'info envoyer correspond à ce qui est attendu à l'inscription
	 * @param bool $ajax [opt] : Indique si on est dans un appel ajax ou non
	 * @param string $champ [opt] : Le nom du champ à vérifier
	 * @param string $val [opt] : La valeur du champ à vérifier
	 * @return string [opt] : Si on ne passe par ajax, le contenu à afficher est retourné
	 */
	public function verifinfonewAction($ajax=true, $champ=null, $val=null)
	{
		$retour = ''; //Ce qui sera retourné
		
		if($ajax==true) //Si on passe par ajax, on récupère le champ et sa valeur
		{
			$champ = $this->_getParam('champ', null);
			$val = trim($this->_getParam('val', null));
		}
		
		//Si le champ et sa valeur n'est pas null
		if($champ != null && $val != null && strlen($val) > 0)
		{
			$val = trim($val);
			
			if($champ == 'mail') //S'il s'agit du champ mail, on vérifie sa validé et s'il existe déjà en base ou non
			{
				$val = valid_mail($val);
				if($val)
				{
					$tableUser = new Table_Users;
					$retour = !$tableUser->ifExist('mail', $val);
				}
				else {$retour = '0';}
			}
			elseif($champ == 'login') //S'il s'agit du login on ne fait que vérifier s'il existe déjà en base ou non
			{
				$tableUser = new Table_Users;
				$retour = !$tableUser->ifExist('login', $val);
			}
			elseif($champ == 'pwd') {$retour = '1';} //Pas d'action précise pour le mot de passe
			else {$retour = '0';}
		}
		else {$retour = '0';}
		
		//Retour des infos
		if($ajax == true) {echo $retour;exit;}
		else {return $retour;}
	}
	
	/**
	 * Vérifie si le captcha est bon
	 * @param bool $ajax [opt] : S'il s'agit d'un appel ajax ou non
	 * @return bool [opt] : Uniquement si ça ne passe pas par ajax, True si le captcha est bon, false sinon
	 */
	public function verifcaptchaAction($ajax=true)
	{
		if($ajax == true) //S'il s'agit d'un appel ajax
		{
			//On récupère les informations
			$retour = '';
			$cha = trim($this->_getParam('cha')); //La clé du captcha
			$code = trim($this->_getParam('code')); //Le code rentré par l'user
			
			//Si aucun des 2 n'est vide
			if(!empty($cha) && !empty($code))
			{
				//On appel le service recaptcha
				$recaptcha = new Zend_Service_ReCaptcha($this->publicKey, $this->privateKey);
				
				try
				{
					$result = $recaptcha->verify($cha, $code); //On vérifie que le code soit bon
					if($result->isValid()) {$retour = '1';} //S'il est valide on retourne '1'
					else {$retour = '0';} //Si erreur, on retourne '0'
				}
				//S'il y a une erreur à la vérification, on retourne '0'
				catch(exception $e) {$retour = '0';}
			}
			else {$retour = '0';} //Si les champs n'ont pas été donné, on retourne '0'
			
			//On stocke en session le retour de la vérification car une image recaptcha ne peut être vérifier qu'une seule et unique fois.
			$sess = new Zend_Session_Namespace('captcha');
			$sess->retour = $retour;
			
			//Et on affiche le retour
			echo $retour; exit;
		}
		else //Si on appelle l'action via une autre action et non par ajax
		{
			//On considère la vérification déjà fait via ajax
			$sess = new Zend_Session_Namespace('captcha'); //On récupère le résultat de la vérification
			if(isset($sess->retour)) {$retour = $sess->retour;}
			else {$retour = '0';} //Si la vérification n'existe pas, on retourne '0'
			
			//On retourne le résultat de la vérification
			return $retour;
		}
	}
	
	/**
	 * Ajoute un utilisateur
	 */
	public function newaddAction()
	{
		//On récupère les valeurs
		$mail = trim($this->_getParam('mail', null));
		$login = trim($this->_getParam('login', null));
		$pwd = trim($this->_getParam('pwd', null));
		$offre = trim($this->getParam('offre', null));
		
		//On les vérifie toutes
		$VMail = $this->verifinfonewAction(false, 'mail', $mail);
		$VLogin = $this->verifinfonewAction(false, 'login', $login);
		$VPwd = $this->verifinfonewAction(false, 'pwd', $pwd);
		
		//Si la vérification est bonne
		if($VMail && $VLogin && $VPwd)
		{
			//On vérifie le captcha
			$capRet = $this->verifcaptchaAction(false);
			if($capRet == '1') //Si le captcha est bon
			{
				$tableOffre = new Table_Offres; //Modèle de la table offre
				if($tableOffre->ifExist($offre)) //On vérifie que l'offre existe
				{
					$pwd = hashPwd($pwd); //On hash le mot de passe de l'user
					$tableUser = new Table_Users; //Le modèle de la table Users
					
					//On ajoute l'user et on affiche '1'
					try {$tableUser->add($mail, $login, $pwd, $offre); echo '1';}
					catch(exception $e) {echo '0'; echo $e;} //Si une erreur survient, on affiche '0'
				}
				else {echo '0';} //Si erreur, on affiche '0'
			}
			else {echo '0';} //Si erreur, on affiche '0'
		}
		else {echo '0';} //Si erreur, on affiche '0'
		
		exit;
	}
	
	/**
	 * changer d'offre
	 */
	public function offreAction()
	{
		//On change de layout
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('vierge');
		
		//Si on est connecté
		if(Zend_Auth::getInstance()->hasIdentity())
		{
			$infosUser = Zend_Auth::getInstance()->getStorage()->read();
			$idUser = $infosUser->id; //On récupère l'id de l'user
			
			//Les modèles de tables
			$tableDir = new Table_Dossier;
			$tableOffres = new Table_Offres;
			
			//Récupération de l'espace utilisé par l'user
			$idDirRoot = $tableDir->idDirRoot($idUser);
			$infoDir = $tableDir->infosDossier($idDirRoot);
			$use = $infoDir['size_use'];
			
			//Le maximum possible pour l'user
			$infosOffre = $tableOffres->infos($infosUser->offre);
			
			//Le formulaire d'inscription
			$form = new Zend_Form();
			$form->setMethod('post');
			$form->setAttrib('id', 'changeOffre'); //L'id du formulaire
			
			$lstOffres = $tableOffres->fetchAll()->toArray(); //Liste toutes les offres
			//Traitement du tableau des offres
			$offres = array();
			foreach($lstOffres as $val)
			{
				$offres[$val['id']] = $val['name'].' : '.$val['limit'].$val['limit_unite'];
				$octet = Octet($val['limit'], $val['limit_unite']);
				
				if($octet < $use) {$offres[$val['id']] .= ' (Non disponible : trop d\'espace disque utilisé)';}
			}
			
			//Elément offre du formulaire
			$radio = new Zend_Form_Element_Radio('offres');
			$radio->setLabel('Choisissez votre offres : ');
			$radio->addMultiOptions($offres);
			
			//L'offre à sélectionner par défaut
			$idSelect = $this->_getParam('offre', $infosUser->offre);
			$radio->setValue($idSelect);
			$form->addElement($radio);
			
			$form->addElement('submit', 'send', array('Label' => 'Changer mon offre'));
			
			$this->view->actuel = $infosOffre['limit'].$infosOffre['limit_unite'];
			$this->view->use = offreAff($use);
			$this->view->form = $form;
		}
		else {echo 'NaN'; exit;}
	}

	/**
	 * Le submit du changement d'offre
	 */
	public function changeoffreAction()
	{
		//On change de layout
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('vierge');
		
		//Si on est connecté
		if(Zend_Auth::getInstance()->hasIdentity())
		{
			$idOffre = $this->_getParam('offre', null);
			if($idOffre != null)
			{
				$infosUser = Zend_Auth::getInstance()->getStorage()->read();
				$idUser = $infosUser->id; //On récupère l'id de l'user
				
				//Les modèles de tables
				$tableUser = new Table_Users;
				$tableDir = new Table_Dossier;
				$tableOffres = new Table_Offres;
				
				//Récupération de l'espace utilisé par l'user
				$idDirRoot = $tableDir->idDirRoot($idUser);
				$infoDir = $tableDir->infosDossier($idDirRoot);
				$use = $infoDir['size_use'];
				
				//Le maximum possible avec la nouvelle offre
				$infosOffre = $tableOffres->infos($idOffre);
				$maxOctet = Octet($infosOffre['limit'], $infosOffre['limit_unite']);
				
				if($maxOctet >= $use) //S'il est possible de passer sur l'offre choisie
				{
					$tableUser->askOffre($idUser, $idOffre); //Modif bdd
					
					//Envoi du mail à l'user
					$dest['mail'] = $infosUser->mail;
					$dest['nom'] = $infosUser->login;
					$sujet = 'Insset Files : Modification de votre offre';
					$cont  = 'Bonjour '.$infosUser->login."<br/><br/>";
					$cont .= 'Votre demande de modification d\'offre a bien &eacute;t&eacute; prise en compte.<br/>';
					$cont .= 'D&egrave;s qu\'un administrateur l\'aura approuv&eacute;, vous passerez &agrave; '.$infosOffre['limit'].$infosOffre['limit_unite'].' de stockage maximum.';
					$sendUser = sendMail($dest, $sujet, $cont);
					
					if($sendUser != true)
					{
						$sendUserEtat = false;
						var_dump($sendUser);
					}
					else {$sendUserEtat = true;}
					
					//Envoi du mail aux admins
					$sujet = 'Insset Files : Un utilisateur veux changer d\'offre';
					$cont = 'L\'utilisateur '.$infosUser->login.' vient de faire une demande pour passer &agrave; une offre de '.$infosOffre['limit'].$infosOffre['limit_unite'].' de stockage maximum.';
					
					$sendAdmin = true;
					$lstAdmin = $tableUser->LstAllAdmin();
					foreach($lstAdmin as $val)
					{
						$dest['mail'] = $val['mail'];
						$dest['nom'] = $val['login'];
						$send = sendMail($dest, $sujet, $cont);
						
						if($send != true)
						{
							$sendAdmin = false;
							var_dump($send);
						}
					}
					
					if($sendUser == false || $sendAdmin == false) {echo 'NoMail';}
					else {echo '1';}
					exit;
				}
				else {echo 'NaN'; exit;}
			}
			else {echo 'NaN'; exit;}
		}
		else {echo 'NaN'; exit;}
	}
	
	/**
	 * Suppression d'un dossier ou fichier
	 */
	public function supprimerAction()
	{
		//On change de layout
		$layout = Zend_Layout::getMvcInstance();
		$layout->setLayout('vierge');
		
		//Si on est connecté
		if(Zend_Auth::getInstance()->hasIdentity())
		{
			$infosUser = Zend_Auth::getInstance()->getStorage()->read();
			$idUser = $infosUser->id; //On récupère l'id de l'user
			
			$id = $this->_getParam('id', null);
			if($id != null)
			{
				//id = su_f00 ou su_d00
				$id = substr($id, 3);
				$type = $id{0};
				$id = substr($id, 1);
				
				//Les modèles de la table
				$tableFichier = new Table_Fichiers;
				$tableDossier = new Table_Dossier;
				
				if($type == 'f')
				{
					$infoFile = $tableFichier->infos($id);
					$nom = $infoFile['nom_stock'];
					$tableFichier->suppr($id, $idUser);
					echo '1';
				}
				elseif($type == 'd')
				{
					$tableFichier->supprAllInDir($id, $idUser);
					$tableDossier->suppr($id, $idUser);
					echo '1';
				}
				else {echo '0';}
			}
			else {echo '0';}
		}
		else {echo '0';}
	}
}