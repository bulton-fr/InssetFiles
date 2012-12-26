/**
 * Lorsque la page est chargé
 */
$(document).ready(function()
{
	/* Le click sur l'image pour changer l'offre d'un user */
	$(".ajax_editOffre").click(function()
	{
		id = "menu_"+$(this).parent().attr('id'); //L'id
		display = $("#"+id).css("display"); //L'état d'affichage du menu (block/none)
		
		if(display == "none") //Si le menu n'est pas affiché
		{
			TR = $(".index_admin").offset().top; //La position top du bloc principal
			
			//On calcul la position top du menu par rapport à la position top de l'image du menu et la position du bloc principal
			T = ($(this).offset().top - TR) + 21;
			$("#"+id).css("top", T+"px"); //On met à jour la position top du menu
			
			$("#"+id).css("display", "block"); //On affiche le menu
		}
		else {$("#"+id).css("display", "none");} //Sinon on l'enlève
	});
	
	/* Le click sur une offre */
	$("ul.menuOffres li").click(function()
	{
		//On vérifie que l'élément cliqué est possible via la présence ou non de certaines classes css
		if(!($(this).hasClass("actuel")) || $(this).hasClass("tooSmall"))
		{
			idUser = $(this).parents(".user_adm").attr('id'); //On récupère l'id de l'user
			idOffre = $(this).attr('id'); //Et l'id de l'offre choisi
			
			//Puis on fait une requête ajax pour mettre à jour.
			$.get(baseUrl+'/admin/offre', {id:idUser, offre:idOffre}, function(data)
			{
				if(data == '1') {window.location.reload();} //Si la requête à réussi, on actualise la page.
				else {alert("Une erreur s'est produite durant la mise à jour de l'offre.");} //Sinon, on affiche une erreur
			});
		}
	});
	
	/* Click sur la suppression de l'user */
	$(".ajax_SupprUser").click(function()
	{
		id = $(this).parent().attr('id'); //On récupère l'id de l'user
		user = $(this).parent().children("p").html(); //On récupère le pseudo de l'user
		
		//On demande une confirmation
		choix = confirm("Êtes-vous sûr de vouloir supprimer l'utilisateur "+user+"\nAttention, tous ses fichiers seront également supprimé.");
		
		if(choix) //Si l'admin confirme
		{
			$.get(baseUrl+"/admin/suppr", {id:id}, function(data) //On fait une requête ajax
			{
				if(data == '1') //Si la suppression à réussi, on enlève la ligne correspondante
				{
					$("#"+id).fadeOut("slow");
					$("#end_"+id).fadeOut("slow");
				}
				else {alert("Une erreur est intervenue. Veuillez réessayer plus tard.");} //Sinon on affiche une erreur
			});
		}
		//Si l'admin à répondu "annuler", on ne fait rien.
	});
});