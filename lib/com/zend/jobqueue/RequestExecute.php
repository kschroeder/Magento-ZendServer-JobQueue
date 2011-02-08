<?php

namespace com\zend\jobqueue;

class RequestExecute
{
	
	private $job;
	
	public function __construct(JobAbstract $job)
	{
		$this->job = $job;
	}
	
	/**
	 * 
	 * @return JobAbstract
	 */
	
	public function getJob()
	{
		return $this->job;
	}
	
}