<?php

namespace com\zend\jobqueue;

class Response
{
	
	private $_jobNumber;
	private $_serverName;
	
	public function getJobNumber()
	{
		return $this->_jobNumber;
	}
	
	public function getServerName()
	{
		return $this->_serverName;
	}
	
	public function setJobNumber($num)
	{
		$this->_jobNumber = $num;
	}
	
	public function setServerName($name)
	{
		$this->_serverName = $name;
	}
	
}