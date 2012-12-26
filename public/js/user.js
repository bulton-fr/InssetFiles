/**
 * Ajoute un dossier
 */
function addDir()
{
	name = $("article.addDossier dd#name-element input").val(); //La valeur du champ nom
	link = $("article.addDossier #link").val(); //Le lien à appeler
	
	$("article.addDossier dt#submit-label img#load").show(); //On affiche une image de chargement
	//Appel ajax vers la page voulu en transmettant le nom du dossier
	$.get(link, {name:name}, function(data)
	{
		id = parseInt($("#idDirRead").val()); //L'id du dossier dans lequel on est
		ajax = true;
		
		$("article.addDossier dt#submit-label img#load").hide(); //On cache l'image de chargement
		if(data == 1) //si le dossier a été ajouté
		{
			$(".overlay").hide(); //On cache l'overlay
			LoadDir(id);
		}
		else {alert("Une erreur s'est produite durant l'ajout du dossier.");} //S'il y a un souci, on affiche une erreur
	});
}

/**
 * Affiche un dossier
 * @param int/objet : Infos sur le bloc à afficher
 */
function LoadDir(obj, topDoc)
{
	loadOverlay(topDoc); //Affiche le bloc de chargement
	
	ajax = true;
	if(typeof(obj) == 'number') {id = obj;} //Si c'est un type int, le paramètre est directement l'id du dossier à afficher
	else
	{
		//On récupère l'id du dossier à afficher
		id = $(obj).attr('id');
		id = id.substr(1);
	}
	
	//On appelle la page affichant les dossiers
	$.get(baseUrl+'/user/lstdirfile', {dir:id, ajax:ajax}, function(data)
	{
		closeLoadOverlay(topDoc); //On ferme le bloc de chargement
		
		//On affiche la liste des dossiers et fichiers
		if(topDoc == undefined) {$("#lst_dirFile").html(data);}
		else {$("#lst_dirFile", top.document).html(data);}
	}); 
}

/**
 * Mise à jour de la taille totale utilisée
 */
function MajSizeTotal(topDoc)
{
	$.get(baseUrl+'/dossier/totaluse', {}, function(data)
	{
		if(topDoc == undefined) {$('.action span').html(data);}
		else {$('.action span', top.document).html(data);}
	});
}

/**
 * Lorsque la page est chargé
 */
$(document).ready(function()
{
	$(".dir > img").live('click', function() {LoadDir(this);}); //On réactualise la liste des dossiers et fichiers
	$(".dir > p").live('click', function() {LoadDir(this);}); //On réactualise la liste des dossiers et fichiers
	$("li.link").live('click', function() {LoadDir(this);}); //On réactualise la liste des dossiers et fichiers
	
	//Click sur l'image pour ajouter un dossier
	$("#ajax_adddir").live('click', function()
	{
		loadOverlay(); //Affiche l'overlay
		
		link = $(this).attr('href'); //On récupère le lien
		idDir = $("#idDirRead").val(); //L'id du dossier lu
		
		//S'il n'y a pas d'erreurs (l'id appartient bien à l'user), on créer l'overlay avec le formulaire d'ajout de dossier
		$.get(link, {dir:idDir}, function(data)
		{
			if(data != 'NaN') {createOverlay(data);}
			else{alert("Une erreur s'est produite.\nVeuillez réessayer plus tard.");}
		});
		
		return false; //On retourne false
	});
	
	//Click sur l'image pour ajouter un fichier
	$("#ajax_addfile").click(function()
	{
		loadOverlay(); //Affiche l'overlay
		
		link = $(this).attr('href'); //On récupère le lien
		idDir = $("#idDirRead").val(); //L'id du dossier lu
		
		//On créer l'overlay avec le formulaire d'ajout de fichier
		createOverlay('<iframe src="'+link+'dir/'+idDir+'" id="FrameUp"></iframe>');
		return false; //On retourne false 
	});
	
	//Validation du formulaire d'ajout de dossier
	$("article.addDossier dt#submit-label img#valid").live('click', function() {addDir();});
	$("article.addDossier form").live('submit', function() {addDir(); return false;});
	
	//Si on double click sur un dossier : On l'ouvre
	$(".dossier").live('dblclick', function() {LoadDir(this);});
	
	var idFile; //L'id du dernier fichier dont on a lu les détails
	$(".file").live('click', function() //Si on click une seule fois sur le fichier
	{
		id = $(this).attr('id'); //L'id du fichier qu'on veux lire
		$(".infosDirFile").html($("#info_"+id).html()); //On met le contenu des détails dans le bloc prévu pour les afficher
		
		if(id != idFile) //Si l'id qu'on lit n'est pas le même que le dernier lu
		{
			if(idFile != undefined) {$("#"+idFile).removeClass("souligne");} //On enlève la classe souligne du dernier fichier lu
			$(this).addClass('souligne'); //On ajoute la classe souligne au fichier qu'on lit
			if($(".infosDirFile").css("display") == "none") {$(".infosDirFile").slideDown();} //On affiche les détails
			idFile = id; //On indique que le dernier fichier lu est celui qu'on lit
		}
		else //Si c'est le même id
		{
			$(this).removeClass('souligne'); //On enlève la classe souligne du nom du fichier
			$(".infosDirFile").slideUp(); //On enlève les détails
			idFile = '0'; //On remet à 0 l'id du dernier fichier lu
		}
	});
	
	var idDir; //L'id du dernier dossier dont on a lu les détails
	$(".dossier").live('click', function()
	{
		id = $(this).attr('id'); //L'id du fichier qu'on veux lire
		$(".infosDirFile").html($("#info_"+id).html()); //On met le contenu des détails dans le bloc prévu pour les afficher
		
		if(id != idDir) //Si l'id qu'on lit n'est pas le même que le dernier lu
		{
			$(this).addClass('souligne'); //On ajoute la classe souligne au fichier qu'on lit
			if($(".infosDirFile").css("display") == "none") {$(".infosDirFile").slideDown();} //On affiche les détails
			idDir = id; //On indique que le dernier fichier lu est celui qu'on lit
		}
		else //Si c'est le même id
		{
			$(this).removeClass('souligne'); //On enlève la classe souligne du nom du fichier
			$(".infosDirFile").slideUp(); //On enlève les détails
			idDir = '0'; //On remet à 0 l'id du dernier fichier lu
		}
	});
	
	//Click sur l'image pour supprimer
	$(".img_supprimer").live('click', function()
	{
		id = $(this).attr('id'); //L'id du fichier ou dossier concerné
		type = $(this).parent().children('span').html(); //Le type sur lequel agir (dossier ou fichier)
		
		//On demande confirmation
		choix = confirm("Êtes-vous sur de vouloir supprimer ce "+type+" ?");
		
		if(choix) //Si la personne confirme
		{
			//Requête ajax vers la page pour supprimer
			$.get(baseUrl+'/user/supprimer', {id:id}, function(data)
			{
				if(data == '1') //S'il n'y a pas eu d'erreur
				{
					id = parseInt($("#idDirRead").val()); //On récupère l'id du dossier qu'on lit
					LoadDir(id); //On réactualise la liste des dossiers et fichiers
					MajSizeTotal(); //On réactualise la taille utilisé par l'user
				}
				//Sinon on affiche une erreur
				else {alert('Une erreur s\'est produite durant la suppression.');}
			});
		}
	});
	
	//Submit du changement d'offre
	$("#changeOffre").live('submit', function()
	{
		offre = $("input:radio[name=offres]:checked").val();
		closeOverlay();
		loadOverlay();
		
		$.get(baseUrl+'/user/changeoffre', {offre:offre}, function(data)
		{
			closeLoadOverlay();
			if(data != 'NaN')
			{
				if(data == 'NoMail') {alert("Le mail de confirmation n\'a pu être envoyé suite à une erreur du serveur mail\nMerci de nous en excuser.\n\nPS @Harold: Vérifie le serveur mail, il a parfois des ratés...");}
				MajSizeTotal();
			}
			else {alert('Une erreur s\'est produite durant le changement d\'offre');}
		});
		
		return false;
	});
	
});