<?php

namespace Aw\DataPort;

use Aw\DataPort\WriterAdapter;

/**
 * Array Writer Adapter
 * @author Jerry Sietsma
 */
class ArrayWriterAdapter implements WriterAdapter
{
	protected $items;
	
	public function __construct()
	{
		$this->items = array();
	}
		
	public function writeRow(array $row)
	{
		$this->items[] = $row;
		return true;
	}
	
	public function getResult()
	{
		return $this->items;
	}
	
	public function flush()
	{}
}