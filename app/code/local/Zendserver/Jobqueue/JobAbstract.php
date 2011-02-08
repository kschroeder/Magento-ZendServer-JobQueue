<?php

use com\zend\jobqueue\JobAbstract;

abstract class Zendserver_Jobqueue_JobAbstract extends JobAbstract
{

	public function execute()
	{
		if (!$this->getJobQueueUrl()) {
			$this->setJobQueueUrl(Mage::getStoreConfig('jobqueue/settings/queue_url'));
		}
		return parent::execute();
	}
	
}