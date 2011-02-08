<?php

namespace com\zend\jobqueue;

class RequestQueue
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