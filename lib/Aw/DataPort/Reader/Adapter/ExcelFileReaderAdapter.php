<?php

namespace Aw\DataPort\Reader\Adapter;

use Aw\DataPort\ReaderAdapter,
	ArrayIterator;

/**
 * Excel File ReaderAdapter
 * @author Jerry Sietsma
 */
class ExcelFileReaderAdapter implements ReaderAdapter
{
	protected $filename;
	
	protected $hasHeaderWithColumns = false;
	
	// when source has header with columns, true to ignore, false otherwise
	protected $ignoreHeaderWithColumns = false;
	
	public function __construct($filename)
	{
		$this->filename = $filename;
		
		// Load Excel lib
		//$libPath = PATH_SYS . 'vendor/phpexcel/Classes/';
		//require_once($libPath . 'PHPExcel/IOFactory.php');
				
		$this->excelReader = null;
		$this->rowIterator = null;
	}
	
	public function setHasHeaderWithColumns($hasHeaderWithColumns)
	{
		$this->hasHeaderWithColumns = (bool) $hasHeaderWithColumns;
		return $this;
	}
	
	public function setIgnoreHeaderWithColumns($ignore)
	{
		$this->ignoreHeaderWithColumns = (bool) $ignore;
		return $this;
	}
	
	public function getRows()
	{
		if (!$this->rowIterator)
		{
			try
			{
				$excelReader = $this->getExcelReader($this->filename);
				$sheetData = $excelReader->getActiveSheet()->toArray(null,true);
				
				$limit = count($sheetData);
				$i = 0;
				foreach ($sheetData as $row)
				{
					$hasValue = false;
					foreach ($row as $key => $value)
					{
						if (!empty($value))
						{
							$hasValue = true;
							break;
						}
					}
					
					if (!$hasValue)
					{
						$limit = $i;
						break;
					}
					
					$i++;
				}
				
				if ($limit !== count($sheetData))
				{
					$sheetData = array_slice($sheetData, 0, $limit);
				}
										
				$this->rowIterator = new PhpExcelWorksheetRowArrayIterator($sheetData, $this->hasHeaderWithColumns, $this->ignoreHeaderWithColumns);
			}
			catch(\Exception $e)
			{
				print $e;
			}
		}
		
		return $this->rowIterator;
	}
	
	public function getRowCount()
	{
		return iterator_count($this->getRows());
	}
	
	public function getColumns()
	{
		return $this->getRows()->columns();
	}
	
	/**
	 * Private/protected methods
	 */
	
	protected function getExcelReader($filename)
	{
		if (!$this->excelReader)
		{
			$this->excelReader = \PHPExcel_IOFactory::load($filename);
		}
		
		return $this->excelReader;
	}
}



/**
 * Worksheet rows array iterator
 * @author Jerry Sietsma
 */
class PhpExcelWorksheetRowArrayIterator extends ArrayIterator
{
	protected $columns;
	
	protected $hasHeaderWithColumns;
	
	protected $ignoreHeaderWithColums;
	
	public function __construct($worksheetData, $hasHeaderWithColumns = false, $ignoreHeaderWithColumns = false)
	{
		$columns = ($hasHeaderWithColumns) ? array_shift($worksheetData) : null;
					
		if ($columns !== null)
		{
			if ($ignoreHeaderWithColumns)
			{
				$columns = null;
			}
			else
			{
				$newColumns = array();
				$i = 0;
				foreach ($columns as $column)
				{
					if ($column == '')
					{
						$column = 'col_' . $i;
					}
					$newColumns[] = $column;
					$i++;
				}
				
				$columns = $newColumns;				
			}
		}
		
		$this->columns = $columns;
		
		parent::__construct($worksheetData);
		
		$this->hasHeaderWithColumns = $hasHeaderWithColumns;
		$this->ignoreHeaderWithColumns = $ignoreHeaderWithColumns;
				
		// init: set columns if available
		$this->rewind();
	}
	
	/**
	 * Parent overrides
	 */
	
	public function current()
    {
        return $this->columns !== null ? array_combine($this->columns, parent::current()) : parent::current();
    }
	
	/**
	 * Public methods
	 */
	
	public function columns()
	{
		return $this->columns;
	}
}