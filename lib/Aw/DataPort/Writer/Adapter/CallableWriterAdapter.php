<?php

namespace Aw\DataPort;

use Aw\DataPort\WriterAdapter;

/**
 * Callable Writer Adapter
 * @author Jerry Sietsma
 */
class CallableWriterAdapter implements WriterAdapter
{
	protected $callable;
	
	public function __construct(callable $callable)
	{
		$this->callable = $callable;
	}
		
	public function writeRow(array $row)
	{
		$success = false;
		
		if ($this->callable instanceof \Closure)
		{
    		$success = $this->callable->__invoke($row);
		}
		else
		{
    		$success = call_user_func($this->callable);
		}
		
		return $success;
	}
}