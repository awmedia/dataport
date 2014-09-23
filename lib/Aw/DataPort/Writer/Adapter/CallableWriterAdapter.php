<?php

namespace Aw\DataPort\Writer\Adapter;

/**
 * Callable Writer Adapter
 * @author Jerry Sietsma
 */
class CallableWriterAdapter extends AbstractWriterAdapter
{
	protected $callable;
	
	public function __construct(callable $callable)
	{
		$this->callable = $callable;
	}
		
	protected function _writeRow(array $row)
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