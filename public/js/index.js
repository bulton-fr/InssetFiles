/*
 * Vérification si un champ est valide ou non
 * @param string champ : L'id du champ à vérifier
 */
function verif(champ)
{
	val = $("#"+champ).val(); //On récupère le contenu du champ
	
	//Requête ajax vers une page de vérif
	//On lui passe en paramètre le nom du champ et sa valeur
	$.get(baseUrl+'/user/verifinfonew', {champ:champ, val:val}, function(data)
	{
		if(data == '1') //Si la valeur du champ est bon
		{
			img = 'tick'; alt="Ok"; //On affiche l'image 'valider'
			$("#erreur-"+champ).val("0"); //On met l'input hidden correspondant au champ vérifié à 0
		}
		else //Si la valeur du champ n'est pas bon
		{
			img = 'cross'; alt="Erreur"; //On affiche l'image 'erreur'
			$("#erreur-"+champ).val("1"); //On met l'input hidden correspondant au champ vérifié à 1
		}
		
		//On met à jour les images et on l'affiche
		$("#"+champ+"-element p img").attr("src", baseUrl+"/images/"+img+".png");
		$("#"+champ+"-element p img").attr("alt", alt);
		$("#"+champ+"-element p img").css("display", "block");
	});
}

/**
 * Lorsque la page est chargé
 */
$(document).ready(function()
{
	//Dès qu'un champ pers le focus, on le vérifie
	$("#mail").live('blur', function() {verif('mail');});
	$("#login").live('blur', function() {verif('login');});
	$("#pwd").live('blur', function() {verif('pwd');});
	
	//Lorsque le formulaire est validé
	$("form#new").live('submit', function()
	{
		//On récupère les valeurs des champs hidden de chaque champs
		errMail = $("#erreur-mail").val();
		errLogin = $("#erreur-login").val();
		errPwd = $("#erreur-pwd").val();
		
		//S'il n'y a pas d'erreurs
		if(errMail == 0 && errLogin == 0 && errPwd == 0)
		{
			//On vérifie le captcha
			
			cha = $("#recaptcha_challenge_field").val(); //La clé correspondant au captcha
			val = $("#recaptcha_response_field").val(); //La valeur rentré par l'user
			
			//On affiche un message d'attente car la validation du captcha peut prendre un peu de temps
			$("#send-label").html("<div style='text-align: center;'>Vérification du code en cours, veuillez patientez...</div>");
			
			//On appel la page de vérif du captcha
			//On passe en paramètre la clé du captcha et la valeur rentré par l'user
			$.get(baseUrl+'/user/verifcaptcha', {code:val, cha:cha}, function(data)
			{
				if(data == '1') //Si le captcha est ok
				{
					$('.overlay').hide(); //On cache l'overlay
					loadOverlay(); //On affiche le chargement
					$(".overlay_load").html("Inscription en cours"); //On change le texte de chargement
					
					//On récupère les valeurs de tous les champs utilisé dans l'inscription
					mail = $("#mail").val();
					login = $("#login").val();
					pwd = $("#pwd").val();
					offre = $("input:radio[name=offres]:checked").val();
					
					//Requête ajax pour ajouter l'user, on passe en paramètre les valeurs des champs
					$.get(baseUrl+'/user/newadd',
					{
						mail:mail,
						login:login,
						pwd:pwd,
						offre:offre,
						cha:cha,
						code:val
					},
					function(data)
					{
						console.log(data);
						closeLoadOverlay(); //On ferme le chargement
						if(data == '1') //Si l'ajout de l'user à réussi
						{
							//On affiche un overlay indiquant que l'inscription à réussi
							createOverlay("Vous avez bien été inscrit.<br/><br/>Vous allez être connecté d'ici quelques secondes.");
							
							//On appel par ajax la page index en envoyant en post le login et le mot de passe
							//On simule donc une connexion normal, et la session est créé
							$.post(baseUrl+'/index', {login:login, mdp:pwd}, function(data)
								//on attends 3 secondes et ont redirige vers la page index.
								{setTimeout(function() {window.location = baseUrl+'/index';}, 3000);});
						}
						else {createOverlay("Une erreur est survenu durant l'inscription.");}
					});
				}
				else //Si le captcha est faux
				{
					//On affiche une erreur
					$("#send-label").html("<div style='text-align: center;color:red;'>Le code recopié n'est pas bon.</div>");
					Recaptcha.reload(); //On reload le captcha
				}
			});
		}
		
		//On renvoi false pour pas que la page s'actualise
		return false;
	});
});