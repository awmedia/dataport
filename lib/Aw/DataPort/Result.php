<?php

namespace Aw\DataPort\DataPort;

use \Exception;

/**
 * Result
 * @author Jerry Sietsma
 */
class Result
{
	protected $success;
	
	protected $writerResult;
	
	protected $rowCount;
	
	protected $exception;
	
	public function __construct($success, $writerResult, $rowCount, Exception $exception = null)
	{
		$this->success = (bool) $success;
		$this->writerResult = $writerResult;
		$this->rowCount = $rowCount;
		$this->exception = $exception;
	}
	
	public function getSuccess()
	{
		return $this->success;
	}
	
	public function getWriterResult()
	{
		return $this->writerResult;
	}
	
	public function getRowCount()
	{
		return $this->rowCount;
	}
	
	public function getException()
	{
		return $this->exception;
	}
}