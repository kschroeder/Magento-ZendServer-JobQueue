<?php

class Zendserver_Jobqueue_Autoloader
{
	
	public function injectAutoloader()
	{
		
		$al = array();
		foreach (spl_autoload_functions() as $f) {
			$al[] = $f;
			spl_autoload_unregister($f);
		}
		require_once 'Zend/Loader/Autoloader.php';
		require_once 'Zend/Loader.php'; // Silly circular references
		Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);
		spl_autoload_register('Zend_Loader_Autoloader::autoload');
		foreach ($al as $f) {
			spl_autoload_register($f);
		}
	}
	
}