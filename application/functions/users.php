<?php
/**
 * Les fonctions en rapport avec les utilisateurs
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

/*
 * Hash un mot de passe
 * @param string $string : La chaîne à haser
 * @return string : La chaîne hashé
 */
function hashPwd($string) {return substr(hash('sha256', md5($string)),0,32);}

/**
 * Vérifie si l'user à accès à un dossier
 * @param int $idDir : L'id du dossier
 * @param array $infosDir [opt] : Les informations sur le dossier
 * @return bool : True si l'user à accès, false sinon
 */
function accesUserDir($idDir, $infoDir = null)
{
	if(is_null($infoDir)) //Si on a pas les infos sur le dossier, on les récupères
	{
		$tableDossier = new Table_Dossier;
		$infoDir = $tableDossier->infosDossier($idDir);
	}
	
	$logged = Zend_Auth::getInstance()->hasIdentity(); //On obtient l'info de si l'user est co ou pas
	if($logged) //Si l'user est loggé
	{
		$infosUser = Zend_Auth::getInstance()->getStorage()->read(); //On récupère ses infos
		
		if($infosUser->id == $infoDir['user']) {return true;} //Si l'user est bien le propriétaire du dossier
		else {echo 'noegalid';return false;} //Sinon on retourne false
	}
	else {echo 'nologged';return false;} //Si l'user n'est pas loggé on retourne false
}

/**
 * Permet de savoir si le mail passé en paramètre est un e-mail valide ou non
 * @param string : L'adresse e-mail à vérifier
 * @return bool : 
 */
function valid_mail($mail) {return preg_match('#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#i', $mail);}
?>