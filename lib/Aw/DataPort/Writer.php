<?php

namespace Aw\DataPort;

/**
 * Writer
 * @author Jerry Sietsma
 */
class Writer
{
	protected $adapter;
	
	protected $count;
	
	public function __construct(WriterAdapter $adapter)
	{
		$this->adapter = $adapter;
	}
	
	/**
	 * Get WriterAdapter
	 * @return  object  WriterAdapter instance
	 */
	public function getAdapter()
	{
    	return $this->adapter;
	}
	
	/**
	 * Write single row to WriterAdapter
	 * @param   array
	 * @return  boolean     True if success, false if not
	 */
	public function writeRow($row)
	{
		return $this->adapter->writeRow($row);
	}
	
	/**
	 * Get result
	 * @return   mixed   Depends on WriterAdapter
	 */
	public function getResult()
	{
		return $this->adapter->getResult();
	}
	
	/**
	 * Flush
	 * WriterAdapter classes can buffer data to write multiple rows at once.
	 * Always call the flush method after the last written row, to make sure
	 * everything will be written.
	 * @return  bool    True if success, false if not
	 */
	public function flush()
	{
		$this->adapter->flush();
	}
}