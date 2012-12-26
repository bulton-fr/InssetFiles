/*
 * Créer un overlay
 * @param string txt : Le texte à afficher dans l'overlay
 */
function createOverlay(txt, topDoc)
{
	//On cache le chargement
	if(topDoc == undefined)
	{
	    $('.overlay_load').hide();
	    $('#overlayLoadPacman').hide();
	}
	else
	{
		$('.overlay_load', top.document).hide();
	    $('#overlayLoadPacman', top.document).hide();
	}
    clearTimeout(timerPacman); //On désactive le timer utilisé par le pacman
    
    $(".overlay_fond").css('display', 'block'); //On affiche le fond noir de l'overlay
    $(".overlay").css('display', 'block'); //On affiche le bloc de l'overlay
    
    //$(".overlay_cont").html(txt); //Désactivé car les balises scripts sont désactivé via cette méthode (utile pour recaptcha)
    elem = document.getElementById("overlay_cont");
    if(elem == null) {elem = top.document.getElementById("overlay_cont");}
    elem.innerHTML = txt;
    
    //On repositionne le bloc de l'overlay
    overlayResize();
}

var TimerOverlayResize; //Le timer utilisé pour le redimenssionnement de l'overlay

/**
 * Redimensionnement de l'overlay
 * @param int verif : si on doit vérifier le contenu
 * @param string elem : L'id de l'élément (avec le #)
 */
function overlayResize(verif, elem)
{
	//On récupère les tailles de l'overlay
    L = ($('.overlay').width()) / 2;
    H = ($('.overlay').height()) / 2;
    
    //On repositionne les marges de la fenêtre
    $('.overlay').css('margin-top', "-"+H+"px");
    $('.overlay').css('margin-left', "-"+L+"px");
    
    if(verif == 1) //Si on doit vérifier
    {
    	cont = $(elem).html(); //On récupère le contenu de l'élément
    	//Tant que le contenu est vide, on ré-appelle cette fonction pour refaire la vérif
    	if(cont == "") {TimerOverlayResize = setTimeout(function() {overlayResize(verif, elem);}, 300);}
    	else {clearTimeout(TimerOverlayResize);} //Si le contenu n'est pas vide, on annulle le timer
    }
}

/**
 * Ferme la partie de chargement de l'overlay
 * @param ressouce : Utile si on est dans l'iframe, inutile sinon
 */
function closeLoadOverlay(topDoc)
{
	if(topDoc == undefined)
	{
	    $('.overlay_fond').hide(); //Cache le fond
	    $('.overlay_load').hide(); //Cache la partie de chargement
	    $('#overlayLoadPacman').hide(); //Cache le pacman
	}
	else
	{
	    $('.overlay_fond', top.document).hide(); //Cache le fond
	    $('.overlay_load', top.document).hide(); //Cache la partie de chargement
	    $('#overlayLoadPacman', top.document).hide(); //Cache le pacman
	}
    clearTimeout(timerPacman); //Arrête le timer du pacman
}

/**
 * Affiche le bloc de chargement
 * @param ressouce : Utile si on est dans l'iframe, inutile sinon
 */
function loadOverlay(topDoc)
{
	if(topDoc == undefined)
	{
	    $(".overlay_fond").css('display', 'block'); //Affiche le fond noir
	    $(".overlay_load").css('display', 'block'); //Affiche le bloc de chargement
	    $("#overlayLoadPacman").css('display', 'block'); //Affiche le pacman
	    $("#overlayLoadPacman").css('margin-left', '-90px'); //Déplace le pacman à sa position d'origine
	    
	    //On récupère les position min et max du pacman
	    LeftOriPacman = $("#overlayLoadPacman").css('margin-left');
	}
	else
	{
	    $(".overlay_fond", top.document).css('display', 'block'); //Affiche le fond noir
	    $(".overlay_load", top.document).css('display', 'block'); //Affiche le bloc de chargement
	    $("#overlayLoadPacman", top.document).css('display', 'block'); //Affiche le pacman
	    $("#overlayLoadPacman", top.document).css('margin-left', '-90px'); //Déplace le pacman à sa position d'origine
	    
	    //On récupère les position min et max du pacman
	    LeftOriPacman = $("#overlayLoadPacman", top.document).css('margin-left');
	}
	
    LeftOriPacman = parseInt(LeftOriPacman.substr(0, (LeftOriPacman.length -2)));
    MaxLeftPacman = LeftOriPacman+155;
    
    //On appelle la fonction de déplacement du pacman
    movePacman(LeftOriPacman, MaxLeftPacman, topDoc);
}

var timerPacman; //Le timer du pacman

/**
 * La fonction de déplacement du pacman
 * @param string LeftOriPacman : La position minimum à gauche du pacman
 * @param string MaxLeftPacman : La position maximum à droite du pacman
 * @param ressouce : Utile si on est dans l'iframe, inutile sinon
 */
function movePacman(LeftOriPacman, MaxLeftPacman, topDoc)
{
	if(topDoc == undefined)
	{
	    //On récupère la position du pacman
	    MarLeftPx = $("#overlayLoadPacman").css('margin-left');
	    MarLeft = parseInt(MarLeftPx.substr(0, (MarLeftPx.length -2)));
	    
	    //S'il s'agit de la position maximum, on le déplace à la position de base
	    if(MarLeft == MaxLeftPacman) {MarLeft = LeftOriPacman;}
	    else {MarLeft += 5;} //Sinon on le déplace de 5px
	    
	    //On met à jour la position du pacman
	    $("#overlayLoadPacman").css('margin-left', MarLeft+"px");
	}
	else
	{
	    //On récupère la position du pacman
	    MarLeftPx = $("#overlayLoadPacman", top.document).css('margin-left');
	    MarLeft = parseInt(MarLeftPx.substr(0, (MarLeftPx.length -2)));
	    
	    //S'il s'agit de la position maximum, on le déplace à la position de base
	    if(MarLeft == MaxLeftPacman) {MarLeft = LeftOriPacman;}
	    else {MarLeft += 5;} //Sinon on le déplace de 5px
	    
	    //On met à jour la position du pacman
	    $("#overlayLoadPacman", top.document).css('margin-left', MarLeft+"px");
	}
	
    //Et on rappel la fonction 500ms après
    timerPacman = setTimeout(function() {movePacman(LeftOriPacman, MaxLeftPacman, topDoc);}, 500);
}

/**
 * Ferme l'overlay
 */
function closeOverlay()
{
	$('.overlay').hide(); //Et on cache le bloc de l'overlay
	$('.overlay_fond').hide(); //Et on cache le fond de l'overlay
}

/**
 * Lorsque la page est chargé
 */
$(document).ready(function()
{
	$('.overlay_fond').click(function() //Click sur le fond autour de l'overlay
	{
		closeLoadOverlay(); //On appelle la fonction pour fermer l'overlay
		closeOverlay(); //On ferme l'overlay
	});
	
	$('.overlay_close').click(function() //Click sur l'image de la croix
	{
		closeLoadOverlay(); //On appelle la fonction pour fermer l'overlay
		closeOverlay(); //On ferme l'overlay
	});
});