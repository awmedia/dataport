<?php

namespace Aw\DataPort;

/**
 * AbstractDatabaseWriterAdapter
 * Basic database writer adapter implementation
 * @author Jerry Sietsma
 */
abstract class AbstractDatabaseWriterAdapter extends AbstractWriterAdapter
{
	protected $table;
	
	protected $database;
		
	public function __construct($table)
	{
		$table = str_replace('`', '', $table);
		$tableElements = explode('.', $table);
		$hasDatabase = count($tableElements) > 1;
		
		$this->table = $tableElements[$hasDatabase ? 1 : 0];
		$this->database = $hasDatabase ? $tableElements[0] : null;
		
		$this->lastItemId = null;
	}
	
	protected function getTable($quote = false)
	{
    	return $quote ? '`' . $this->table . '`' : $this->table;
	}
	
	protected function getDatabase($quote = false, $suffix = '')
	{
    	$name = $quote ? (isset($this->database) ? '`' . $this->database . '`' : '') : $this->database;
    	
    	if ($name !== null && $name !== '')
    	{
        	$name .= $suffix;
    	}
    	
    	return $name;
	}
	
	public function getLastItemId()
	{
		return $this->lastItemId;
	}
	
	/**
	 * Private/protected methods
	 */
	 
	 /**
	  * Method to execute query
	  * Template methods
	  */
	 abstract protected function executeQuery($query);
}