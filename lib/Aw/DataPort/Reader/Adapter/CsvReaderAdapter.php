<?php

namespace Aw\DataPort\Reader\Adapter;

use Aw\DataPort\Exception, 
	Aw\DataPort\ReaderAdapter,
	Aw\Common\Spl\Iterators\CsvIterator;

/**
 * CsvReaderAdapter
 * @author Jerry Sietsma
 */
class CsvReaderAdapter implements ReaderAdapter
{
	protected $file;
	
	protected $delimiter;
	
	protected $enclosure;
	
	protected $escape;
	
	protected $ignoreHeaderWithColumns = false;
	
	protected $hasHeaderWithColumns = false;
	
	protected $useHeaderColumnsAsIndex = true;
	
	protected $iterator;
	
	public function __construct($file, $delimiter = ',', $enclosure = '"', $escape = '\\')
	{
		$this->isFileHandle = get_resource_type($file) !== false;
		$this->file = $file;
		$this->delimiter = $delimiter;
		$this->enclosure = $enclosure;
		$this->escape = $escape;
	}
	
	public function setHasHeaderWithColumns($hasHeaderWithColumns)
	{
		if ($this->iterator)
		{
			throw new Exception('Cannot change ' . __FUNCTION__ . ' when getRows is invoked (and iterator is init).');
		}
		
		$this->hasHeaderWithColumns = (bool) $hasHeaderWithColumns;
		return $this;
	}
	
	public function setIgnoreHeaderWithColumns($ignore)
	{
		if ($this->iterator)
		{
			throw new Exception('Cannot change ' . __FUNCTION__ . ' when getRows is invoked.');
		}
		
		$this->ignoreHeaderWithColumns = (bool) $ignore;
		return $this;
	}
	
	public function setUseHeaderColumnsAsIndex($useHeaderColumnsAsIndex)
	{
		if ($this->iterator)
		{
			throw new Exception('Cannot change ' . __FUNCTION__ . ' when getRows is invoked.');
		}
		
		$this->useHeaderColumnsAsIndex = (bool) $useHeaderColumnsAsIndex;
		return $this;
	}
		
	public function getRows()
	{
		if (!$this->iterator)
		{
			$this->iterator = new CsvIterator($this->file, $this->delimiter, $this->enclosure, $this->escape);
			$this->iterator->setHasHeaderWithColumns($this->hasHeaderWithColumns);
			$this->iterator->setIgnoreHeaderWithColumns($this->ignoreHeaderWithColumns);
			$this->iterator->setUseHeaderColumnsAsIndex($this->useHeaderColumnsAsIndex);
		}
		
		return $this->iterator;
	}
	
	public function getRowCount()
	{
		return iterator_count($this->getRows());
	}
	
	public function getColumns()
	{
		return $this->getRows()->columns();
	}
}