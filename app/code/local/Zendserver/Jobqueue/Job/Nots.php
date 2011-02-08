<?php

class Zendserver_Jobqueue_Job_Nots extends Zendserver_Jobqueue_JobAbstract 
{
	private $value;
	
	public function setValue($value)
	{
		$this->value = (bool)$value;
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function job()
	{
		$this->value = !$this->value;
	}


}