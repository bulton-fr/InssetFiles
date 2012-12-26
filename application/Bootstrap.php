<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	/**
	 * Charge les modèles du projet automatiquement.
	 */
	protected function _initModel()
	{
		//On ajoute une ressource à l'autoloader pointant vers le dossier Application
		//On lui met pas de namespace (pour n'avoir qu'un seul préfixe)
		//Si on indique un namespace AA, il faudra faire AA_Table_xxx alors qu'on cherche à faire que Table_xxx
		$resourceLoader = new Zend_Loader_Autoloader_Resource(array(
			'basePath'  => APPLICATION_PATH,
			'namespace' => ''
		));
		
		//On ajoute le préfixe "Table_" à l'autoloader, 
		//et on lui dit que c'est une ressource de type model se trouvant dans le dossier "models/"
		$resourceLoader->addResourceTypes(array(
			'model' => array(
				'path'  => 'models/',
				'namespace' => 'Table'
			)
		));
	}
}

