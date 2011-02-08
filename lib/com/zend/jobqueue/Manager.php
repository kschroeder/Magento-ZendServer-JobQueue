<?php

namespace com\zend\jobqueue;

class Manager
{
	
	private static $defaultUrl;
	
	/**
	 * 
	 * @var com\zend\jobqueue\CodecInterface
	 */
	
	private static $defaultCodec;

	/**
	 * 
	 * @var com\zend\jobqueue\Codec
	 */
	private $codec;
	
	public static function setDefaultUrl($url)
	{
		self::$defaultUrl = $url;
	}
	
	
	/**
	 * 
	 * @return com\zend\jobqueue\CodecInterface
	 */
	
	public function getCodec()
	{
		 
		if (!$this->codec instanceof CodecInterface) {
			$this->codec = self::$defaultCodec instanceof CodecInterface?:new Codec();
		}
		return $this->codec;
	}
	
	public function setCodec(CodecInterface $codec)
	{
		$this->codec = $codec;
	}
	
	public static function setDefaultCodec(com\zend\jobqueue\CodecInterface $codec)
	{
		self::$defaultCodec = $codec;
	}
	
	public function invoke()
	{
		
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'Zend Server Job Queue') === 0) {
			$params = \ZendJobQueue::getCurrentJobParams();
			if (!isset($params['obj'])) {
				return new Exception('Job Queue execution request must be invoked only through the Job Queue');
			}
			echo $this->getCodec()->encode($this->executeJob($params['obj']));
		} else {
			$data = file_get_contents('php://input');		
			$obj = $this->getCodec()->decode($data);
			if ($obj instanceof RequestQueue) {
				$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				echo $this->getCodec()->encode($this->createJob($obj->getJob(), $url));
			} else {
				echo $this->getCodec()->encode(
					new Exception('Job Queue must be invoked with a Request object')
				);
			}
		}
	}
	
	/**
	 * 
	 * @param com\zend\jobqueue\JobAbstract $job
	 * @throws Exception
	 * @return Response
	 */
	
	public function sendJobQueueRequest(JobAbstract $job)
	{
		$url = $job->getJobQueueUrl()?:self::$defaultUrl;
		
		if (strpos($url, 'local://') === 0) {
			$response = $this->createJob($job, str_replace('local://', 'http://', $url));
		} else {
			$http = new \Zend_Http_Client($url);
			$http->setMethod('POST');
			$http->setRawData($this->getCodec()->encode(new RequestQueue($job)));
			
			$response = $this->getCodec()->decode(
				$http->request()->getBody()
			);
			
			if (!$response instanceof Response) {
				throw new Exception('Unable to get a properly formatted response from the server');
			}
			
		}
		return $response;
	}
	
	public function getCompletedJob(Response $res)
	{
		$jq = new \ZendJobQueue($res->getServerName());
		$job = $jq->getJobStatus($res->getJobNumber());
		$status = $job['status'];
		if ($status == \ZendJobQueue::STATUS_OK) {
			$output = \Zend_Http_Response::fromString($job['output']);
			
			$response = $this->getCodec()->decode(
				$output->getBody()
			);
			if ($response instanceof \Exception) {
				throw $response;
			}
			return $response;
		}
	}
	
	public function executeJob($obj)
	{
	
		$obj = $this->getCodec()->decode($obj);
		if ($obj instanceof RequestExecute) {
			try {
	        	$job = $obj->getJob();
	        	$job->run();
	        	
	            \ZendJobQueue::setCurrentJobStatus(\ZendJobQueue::OK);
	            return $job;
			} catch (\Exception $e) {
	        	zend_monitor_set_aggregation_hint(get_class($obj) . ': ' . $e->getMessage());
	            zend_monitor_custom_event('Failed Job', $e->getMessage());
	            \ZendJobQueue::setCurrentJobStatus(\ZendJobQueue::OK);
	            return $e;
	        }
		}
		$e = new Exception('Job must be passed via a RequestExecute object');
		\ZendJobQueue::setCurrentJobStatus(\ZendJobQueue::OK);
		return $e;
	}
	
	public function createJob(JobAbstract $obj, $queueUrl)
	{
		$q = new \ZendJobQueue();
		$qOptions = array(
			'name' => $obj->getJobName()?:get_class($obj)
		);
		if ($obj->getJobExecutionTime()) {
			$qOptions['schedule_time'] = $obj->getJobExecutionTime();
		} else if ($obj->getJobSchedule()) {
			$qOptions['schedule'] = $obj->getJobSchedule();
		}  
		$jobReq = new RequestExecute($obj);
		$num = $q->createHttpJob(
			$queueUrl,
			array(
				'obj' => $this->getCodec()->encode($jobReq)
			),
			$qOptions
		);
		
		$response = new Response();
		$response->setJobNumber($num);
		
		if (strpos(ini_get('zend_jobqueue.default_binding'), 'unix://') === 0) {
			$serverName = ini_get('zend_jobqueue.default_binding');
		} else {
			$serverName = php_uname('n');
		}
		$response->setServerName($serverName);
		
		return $response;
		
	}
}