<?php

use com\zend\jobqueue\Manager;
class Zendserver_Jobqueue_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	ob_start(); // just to be sure
    	$mgr = new Manager();
    	$mgr->invoke();
    }
    /*    
    public function testAction()
    {
    	
    	
    	if (!Mage::getSingleton('core/session')->getJQResponse()) {
    		$job = new Zendserver_Jobqueue_Job_Nots();
    		$job->setValue(true);
    		$response = $job->execute();
    		Mage::getSingleton('core/session')->setJQResponse($response);
    		var_dump($job->execute());
    	} else {
    		$adapter = new Manager();
    		$job = $adapter->getCompletedJob(Mage::getSingleton('core/session')->getJQResponse());
    		if ($job !== null) {
    			Mage::getSingleton('core/session')->setJQResponse(null);
    			var_dump($job->getValue());
    		} else {
    			echo 'Not done';
    		}
    	}
    }
    */
}