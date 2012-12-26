<?php
/**
 * Les fonctions pour les mails
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

/**
 * Envoi un mail
 * @param array $dest : 'mail' => Le mail du destinataire du mail, 'nom' => le nom du destinataire
 * @param string $sujet : Le sujet du mail
 * @param string $contenu : Le contenu du mail
 * @return bool : True si le mail est envoyé, false s'il y a une erreur.
 */
function sendMail($dest, $sujet, $contenu)
{
	//$config = array('name' => 'maxime.vermeulen@etud.u-picardie.fr');
	$transport = new Zend_Mail_Transport_Smtp('mailx.u-picardie.fr');
	Zend_Mail::setDefaultTransport($transport);
	
	$mail = new Zend_Mail();
	$mail->setFrom('maxime.vermeulen@etud.u-picardie.fr', 'maxime vermeulen');
	$mail->addTo($dest['mail'], $dest['nom']);
	$mail->setSubject($sujet);
	$mail->setBodyHtml($contenu);
	
	try{$mail->send($transport); return true;}
	catch(exception $e) {return $e;}
}
?>