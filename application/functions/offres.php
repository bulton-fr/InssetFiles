<?php
/**
 * Les fonctions en rapport avec les offres
 * 
 * @author Vermeulen Maxime
 * Projet : Insset Files
 */

/**
 * Renvoi un nombre lisible par rapport à un nombre entrée en octet
 * @param float $val : le nombre en octet
 * @param int $nb : Le nombre de chiffre après la virgule
 * @return string : Le nombre et son unité
 */
function offreAff($val, $nb=0)
{
	$strlen = strlen($val); //Compte le nombre de chiffre entrée
	
	//Mise en forme pour le round
	$cal1 = $strlen/3;
	if(is_int($cal1)) {$cal1 = ($cal1 != 0) ? $cal1-1 : 0;} //Pour éviter que 200 devienne 0,2 par exemple
	
	$cal2 = substr($cal1, 0, 1); //De façon à n'avoir que le premier chiffre du calcul
	$cal3 = $cal2+(2*$cal2); //Pour la puissance
	$cal4 = pow(10, $cal3); //10 puissance $cal3
	
	$div = $val/$cal4; //On divise de façon à passer de 10000 à 10,000
	$res = round($div, $nb); //Et on arrondit
	
	//On rajoute l'unité
		if($cal2 == 0) {$unit = 'o';}
	elseif($cal2 == 1) {$unit = 'ko';}
	elseif($cal2 == 2) {$unit = 'Mo';}
	elseif($cal2 == 3) {$unit = 'Go';}
	elseif($cal2 == 4) {$unit = 'To';}
	else {$unit = '';}
	
	//Et on retourne un chiffre lisible
	$ret = $res.$unit;
	return $ret;
}

/**
 * Renvoi le nombre en octet d'une taille
 * @param int $nb : Le nombre à transformer
 * @param string $uniter : L'uniter
 * @return int : Le nombre en octet
 */
function Octet($nb, $uniter)
{
		if($uniter == 'o')  {return $nb;}
	elseif($uniter == 'ko') {return $nb*1024;}
	elseif($uniter == 'Mo') {return $nb*1024*1024;}
	elseif($uniter == 'Go') {return $nb*1024*1024*1024;}
	elseif($uniter == 'To') {return $nb*1024*1024*1024*1024;}
}
