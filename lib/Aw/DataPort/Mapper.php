<?php

namespace Aw\DataPort;

use Aw\Common\Utils\ArrayUtils as Arr;

/**
 * Mapper
 * @author Jerry Sietsma
 */
class Mapper
{
	protected $mapping;
	
	/**
	 * @cfg	bool	True to include unmapped columns in mapping, false if not (default = false)
	 */
	protected $includeUnmappedColumns = false;
	
	public function setIncludeUnmappedColumns($include)
	{
		$this->includeUnmappedColumns = (bool) $include;
	}
	
	/**
	 * Set mapping
	 * Map destination to source columns
	 * @param	array	$map	Array with key: destination column or index => value: source column or index
	 * @return	object	$this
	 */
	public function setMapping(array $mapping)
	{
		$this->mapping = $mapping;
		return $this;
	}
	
	/**
	 * Get mapping
	 * @return	array	Mapping
	 */
	public function getMapping()
	{
		return $this->mapping;
	}
	
	public function mapSourceToDestinationRow($sourceRow)
	{
		$mappedRow = array();
		
		foreach ($this->getMapping() as $destinationColumn => $sourceColumn)
		{
			$value = null;
			if ($sourceColumn || $sourceColumn != '')
			{
				$hasKey = array_key_exists($sourceColumn, $sourceRow);
				
				if ($hasKey)
				{
					$value = $sourceRow[$sourceColumn];
				}
				else
				{
					// if key doesn't exists, try to resolve by path (key.subkey.subsubkey etc.)
					$value = Arr::path($sourceRow, $sourceColumn);
				}
			}
			
			$mappedRow[$destinationColumn] = $value;
		}
		
		if ($this->includeUnmappedColumns)
		{
			foreach ($sourceRow as $sourceKey => $sourceValue)
			{
				if (!array_key_exists($sourceKey, $this->getMapping()))
				{
					$mappedRow[$sourceKey] = $sourceValue;
				}
			}
		}
		
		return $mappedRow;
	}
	
	public function getDestinationColumnForSource($sourceColumn)
	{
    	return array_key_exists($sourceColumn, $this->getMapping()) ? $this->mapping[$sourceColumn] : null;
	}
}