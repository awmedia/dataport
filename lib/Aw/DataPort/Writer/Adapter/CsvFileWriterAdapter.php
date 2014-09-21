<?php

namespace Aw\DataPort\Writer\Adapter;

use Aw\DataPort\WriterAdapter,
    Aw\DataPort\Exception;

/**
 * Csv File Writer Adapter
 * @author Jerry Sietsma
 */
class CsvFileWriterAdapter extends AbstractWriterAdapter
{
	protected $filename;
	
	protected $createHeaderWithColumns;
	
	protected $delimiter;
	
	protected $enclosure;
		
	public function __construct($filename, $delimiter = ',', $enclosure = '"')
	{
		if (file_exists($filename))
		{
    		throw new Exception('File "' . $filename . '" already exists.');
		}
		
		$this->delimiter = $delimiter;
		$this->enclosure = $enclosure;
		$this->filename = $filename;
		
		$this->fileHandle = null;
	}
	
	public function setCreateHeaderWithColumns($createHeader)
	{
		$this->createHeaderWithColumns = $createHeader;
	}
	
	public function writeRow(array $convertedRow)
	{
		if ($this->fileHandle === null)
		{
			$this->fileHandle = fopen($this->filename, 'w');
		}
		
		if ($this->getWriteCount() === 0)
		{
			if ($this->createHeaderWithColumns === true)
			{
				fputcsv($this->fileHandle, array_keys($convertedRow), $this->delimiter, $this->enclosure);
			}
		}
		
		$success = fputcsv($this->fileHandle, array_values($convertedRow), $this->delimiter, $this->enclosure) !== false;
		
		return $success;
	}
	
	public function getResult()
	{
		return $this->filename;
	}
	
	public function __destruct()
	{
		if ($this->fileHandle !== null)
		{
			fclose($this->fileHandle);
		}
	}
}
	