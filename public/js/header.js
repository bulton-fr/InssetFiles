/**
 * Permet d'adapter la longueur de la barre header suivant la taille du navigateur
 * @param int start : Indique si on est au lancement de la page où non
 */
function headSize(start)
{
	//On adapte la longueur de la barre
	BodyWidth = $("body").width() - 16;
	$("header").css("width", BodyWidth+"px");
	
	//On récupère la longueur des blocks
	//$("header .right").width() != [ $("header .left a").width() + $("header .left p").width() ]
	WidthLeftA = $("header .left a").width();
    WidthLeftP = $("header .left p").width();
    WidthLeft = WidthLeftA + WidthLeftP;
	WidthRight = $("header .right").width();

    if(start == 1) //Si on lance la page
	{
	    WidthHead = WidthLeft + WidthRight+150; //Taille calculé + marge (la marge est plus importante pour le start)
        if(BodyWidth < WidthHead) {$("header .left p").css("display", "none");} //Si trop petit, on efface le message
	}
	else
	{
	    //On fait disparaitre le texte à droite du logo si la barre passe sur 2 lignes
        offsetLeft = $("header .left").offset(); //Position du block à gauche
        offsetRight = $("header .right").offset(); //Position du block à droite
        display = $("header .left p").css("display"); //Etat de l'attribut display sur le texte
        
    	//Nota : L'enchaînement de if est volontaire (algo)
    	//Si les 2 block ne sont pas à la même hauteur ET si le texte est affiché : On le cache
    	if(offsetLeft.top != offsetRight.top) {if(display == "block") {$("header .left p").css("display", "none");}}
    	//Si les 2 blocks sont à la même hauteur ET que le texte est affiché ET que la barre fait plus de 540px : On l'affiche
    	else
        {
            WidthHead = WidthLeft + WidthRight+100; //Taille calculé + marge
            //Si le block est caché et qu'on a la place pour l'afficher, on l'affiche
            if(display == "none" && BodyWidth > WidthHead) {$("header .left p").css("display", "block");}
        }
   }
}

/**
 * Permet d'adapter la hauteur de la marge au-dessus du block principal suivant la taille du navigateur
 * @param int start : Indique si on est au lancement de la page où non
 */
function sizeBlock(start)
{
	//On récupère la hauteur de différents blocs
    HBody = $(window).height();
    HHead = $("header").height();
    HBlock = $("section.index").height();
    HCont = HHead + HBlock;
    HDiff = (HBody - HCont) / 3;
    
    //On récupère la largeur de différents blocs
    LBody = $(window).width();
    LBlock = $("section.index").width();
    LDiff = (LBody - LBlock) / 2;
    
    //Si la différence de hauteur est inférieur à 15px, on garde une marge de 15px
    if(HDiff < 15) {$("section.body").css("margin-top", "15px");}
    else {$("section.body").css("margin-top", HDiff+"px");} //Sinon on utilise la marge calculée
    
    //Si la différence de largeur à gauche du block est supérieur ou égale à 10px, on utilise la marge calculée
    if(LDiff >= 10) {$("section.body").css("margin-left", LDiff+"px");}
    else {$("section.body").css("margin-left", "5px");} //Sinon on oblige à 5px de marge à gauche
}

/**
 * Créer une infobulle
 * @param string idBulle : L'id qu'aura l'info bulle (permet d'en créer plusieurs)
 * @param string id : L'id du block par rapport au-quel se placer
 * @param string txt [opt] : Le texte à mettre dans l'info bulle
 */
function infosBulle(idBulle, id, txt)
{
	//On récupère les positions, largeur, hauteur et marge
    offset = $("#"+id).offset();
    width = ($("#"+id).width()) / 2;
    height = ($("#"+id).height()) + 3;
    PT = parseInt($("#"+id).css('padding-top'));
    
    //On calcul la position top et left
    DivTop = offset.top + height + PT;
    DivLeft = offset.left + width;
    
    //Si l'infobulle n'existe pas encore, on la créer
    if($("#"+idBulle).length == 0)
    {
        html = '<div class="infobulle" id="'+idBulle+'" style="top:'+DivTop+'px;"><div>'+txt+'</div></div>';
        $("body").append(html);
    }
    else //Sinon on l'affiche avec le nouveau contenu
    {
        $("#"+idBulle).css("display", "block");
        $("#"+idBulle+" div").html(txt);
    }
    
    //On calcul la position left de la bulle.
    //On ne peux le faire que maintenant car il faut d'abord connaître sa largeur
    DivWidth = ( $("#"+idBulle).width() ) / 2;
    DivLeft -= DivWidth;
    $("#"+idBulle).css("left", DivLeft);
}

/**
 * Vérifie la connexion
 */
var VerifLoginAttr = 0; //Permet de savoir si la vérification a déjà été fait ou non
function VerifLogin()
{
	//Si l'info bulle existe, on la cache
	if($("#infoBulleCo").length > 0) {$("#infoBulleCo").css("display", "none");}
        
    //On affiche l'image de chargement pendant la vérification
    if($("#LoadVerifCo").lenght > 0) {$("#LoadVerifCo").css("display", "block");}
    else
    {
        //$(this).prepend('<img src="/images/load.gif" alt="Chargement" id="LoadVerifCo" />');
        html = $(".formHiSubmit").html()+'<img src="'+baseUrl+'/images/load.gif" alt="Chargement" id="LoadVerifCo" />';
        $(".formHiSubmit").html(html);
    }
    
    //On récupère la valeur de login et mot de passe
    login = $("form#hi input#login").val();
    mdp = $("form#hi input#mdp").val();
    
    //S'ils ne sont pas vide ou ne contiennent pas les valeurs par défaults
    if(login != '' && login != 'Login' && mdp != 'Mot de passe' && mdp != '')
    {
    	//Requête ajax vers l'url /user/verifco avec le login et le mdp en paramètre
        $.get(baseUrl+'/user/verifco', {login:login, mdp:mdp}, function(data)
        {
        	$("#LoadVerifCo").css("display", "none"); //On cache l'image de chargement
           
           //Si php dit que c'est ok, on envoi le formulaire
           if(data != 0) {$("form#hi").submit();}
           //Sinon on affiche l'info bulle indiquant que le compte n'a pas été trouvé
           else {infosBulle('infoBulleCo', 'icon_user', 'Le compte n\'a pas été trouvé.');} 
        });
    }
    else
    {
    	$("#LoadVerifCo").css("display", "none"); //On cache l'image de chargement
    	
        if(login == '' || login == 'Login') {$("form#hi input#login").addClass("inputError");}
        else {if($("form#hi input#login").hasClass("inputError")) {$("form#hi input#login").removeClass("inputError");}}
        
        if(mdp == '' || mdp == 'Mot de passe') {$("form#hi input#mdp").addClass("inputError");}
        else {if($("form#hi input#mdp").hasClass("inputError")) {$("form#hi input#mdp").removeClass("inputError");}}
        
        //On affiche l'info bulle indiquant un problème
        infosBulle('infoBulleCo', 'icon_user', 'Mauvais login ou mot de passe.');
    }
}

/**
 * Appel à la fonction de resize du header quand on change la taille du nav
 */
$(window).resize(function()
{
    headSize(0);
    sizeBlock(0);
});

/**
 * Lorsque la page est chargé
 */
$(document).ready(function()
{
	headSize(1); //Appel à la fonction de resize du header
	sizeBlock(1);
	
	var HeadRightOri = $("header .right p").html(); //Le contenu original de la partie droite du header
	$("#ajax_co").live('click', function() //Quand on clique sur "Connexion"
    {
    	//On remplace le contenu de la partie droite par une image de chargement
        $("header .right p").html('<img src="'+baseUrl+'/images/load.gif" alt="Chargement" class="HiLoading" />');
        
	    link = $(this).attr('href'); //On récupère le lien qui a été cliqué
	    //Requête ajax pour récupérer le formulaire de connexion et on l'affiche
	    $.get(link, {}, function(data)
	    {
	    	$("header .right p").html(data);
	    	$("#mdp").val("Mot de passe");
	    });
	    
	    return false; //Vu que c'est un lien on retourne false pour pas qu'il soit utilisé par le navigateur
	});
	
	//Quand un élément du formulaire de connexion à le focus
	$("form#hi dd input").live('focus', function()
	{
	   id = $(this).attr('id');
	   if(id == 'login') {val = 'Login';}
	   else {val = 'Mot de passe';}
	   
	   //Si c'est la valeur par défaut, on l'enlève
	   if($(this).val() == val) {$(this).val('');} 
	});
	
	//Quand un élément du formulaire de connexion perd le focus
	$("form#hi dd input").live('blur', function()
	{
		id = $(this).attr('id');
		if(id == 'login') {val = 'Login';}
		else {val = 'Mot de passe';}
		
		//Si le champs est vide, on remet la valeur par défaut
		if($(this).val() == '') {$(this).val(val);} 
    });
    
    //S'il y a un clique sur le bouton annuler du formulaire de connexion
    $(".formHiSubmit img#hi_cancel").live('click', function()
    {
    	//S'il y a une infobulle, on la cache
        if($("#infoBulleCo").length > 0) {$("#infoBulleCo").css("display", "none");}
        
        $("header .right p").html(HeadRightOri); //On remet le contenu d'origine
    });
    
    //Si on valide le formulaire de connexion, on appelle la fonction de login
    $(".formHiSubmit img#hi_valide").live('click', function() {VerifLogin();});
    
    //Si on valide le formulaire de connexion
    $("#hi").live('submit', function()
    {
    	if(VerifLoginAttr == 0) //Si on a pas déjà fait la verif
    	{
    		VerifLoginAttr = 1; //On indique qu'on fait la verif
    		VerifLogin(); //On appelle la fonction de login
    		return false; //Et on retourne false
    	}
		else //Si on a déjà fait la verif (la fonction de login réappelle l'event submit si c'est ok)
		{
			VerifLoginAttr = 0; //On indique qu'on peut refaire la verif au besoin
			return true; //Et on retourne true
		}
    });
    
    //Si on clique sur le lien d'inscription
    $(".ajax_new").live('click', function()
    {
       loadOverlay(); //On affiche le chargement
       link = $(this).attr('href'); //On récupère le lien
       
       //Si le lien n'existe pas, on va le chercher ailleurs
       if(link == undefined) {link = $(this).children("p").children("a").attr('href');}
       
       $.get(link, {}, function(data) //Requête ajax pour afficher le formulaire d'inscription
       {
           createOverlay(data); //On affiche le formulaire d'inscription dans l'overlay
           
           //On appelle une fonction permettant d'afficheer recaptcha
           showRecaptcha(); //Merci Zend de m'obliger à inclure moi-même le js car MONSIEUR gère pas si on appel en ajax ><
           
           //On attends 1 seconde et on appelle la fonction pour repositionné l'overlay
           //car le recaptcha n'était pas pris en compte lors du calcul de la position à la création de l'overlay
           setTimeout(function() {overlayResize(1, "#captcha_aff");}, 1000);
       });
       return false; 
    });
    
    $(".ajax_changeOffre").click(function()
    {
    	loadOverlay(); //On affiche le chargement
       link = $(this).attr('href'); //On récupère le lien
       
       //Requête ajax pour afficher le formulaire de changement d'offre
       $.get(link, {}, function(data)
       {
       		if(data != 'NaN') {createOverlay(data);} //On met le contenu retourné dans l'overlay
       		else {alert('Une erreur s\'est produite.');} //Si erreur, on l'indique
       });
       return false;
    });
});

//Merci Zend de m'obliger à inclure moi-même le js car MONSIEUR gère pas si on appel en ajax ><
/*
 * Créer le recaptcha
 */
function showRecaptcha()
{
    $("#captcha-element").append('<div id="captcha_aff"></div>');
    publicKey = "6LeJKdoSAAAAAJ376XFkW_TLGJv32jQblhDDjqVo";
    Recaptcha.create(publicKey, "captcha_aff", RecaptchaOptions);
}