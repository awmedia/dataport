<?php

namespace Aw\DataPort\Writer\Adapter;

/**
 * Array Writer Adapter
 * @author Jerry Sietsma
 */
class ArrayWriterAdapter extends AbstractWriterAdapter
{
	protected $items;
	
	public function __construct()
	{
		$this->items = array();
	}
		
	protected function _writeRow(array $row)
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