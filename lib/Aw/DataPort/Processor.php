<?php

namespace Aw\DataPort;

use \Exception,
    \Closure;

/**
 * Processor
 * Process data in some way (conversion, format, trim, etc)
 * @author Jerry Sietsma
 */
abstract class Processor
{
    /**
     * @cfg string  The method prefix for cell processor methods
     */
    private $cellMethodPrefix = 'process_';
    
    public function __construct()
    {
        $this->_processedRow;
        $this->_processorMethodsBuffer = array();
    }
    
    /**
     * Process row
     * @param   array
     * @param   array   
     * @param   array   Array with columns or null
     * @param   object  Mapper instance
     * @param   mixed   Processed result
     */
    public function processRow(array $mappedRow, array $sourceRow, $sourceColumns = null, Mapper $mapper)
    {
        $this->_processedRow = array();
        
        $mappedRow = $this->onPreProcessRow($mappedRow, $sourceRow, $sourceColumns, $mapper);
        
        foreach ($mappedRow as $column => $cell)
        {
            $cell = $this->onPreProcessCell($column, $cell, $mappedRow, $sourceRow, $sourceColumns, $mapper);
            
            $this->_processedRow[$column] = $this->processCell($column, $cell, $mappedRow, $sourceRow, $sourceColumns, $mapper);
            
            $cell = $this->onPostProcessCell($column, $this->_processedRow[$column], $mappedRow, $sourceRow, $sourceColumns, $mapper);
        }
        
        $this->_processedRow = $this->onPostProcessRow($this->_processedRow, $sourceRow, $sourceColumns, $mapper);
        
        return $this->_processedRow;
    }
    
    /**
     * Template methods for subclasses
     * Override for custom implementation
     */
    
    protected function onPreProcessRow(array $preProcessedRow, array $sourceRow, array $sourceColumns = null, Mapper $mapper)
    {
	    return $preProcessedRow;
    }
    
    protected function onPostProcessRow(array $postProcessedRow, array $sourceRow, array $sourceColumns = null, Mapper $mapper)
    {
	    return $postProcessedRow;
    }
        
    protected function onPreProcessCell($column, $preProcessedCell, array $mappedRow, array $sourceRow, array $sourceColumns, Mapper $mapper)
    {
	    return $preProcessedCell;
    }
    
    protected function onPostProcessCell($column, $postProcessedCell, array $mappedRow, array $sourceRow, array $sourceColumns, Mapper $mapper)
    {
	    return $postProcessedCell;
    }
    
    /**
     * Private/protected methods
     */
    
    private function processCell($column, $cell, $mappedRow, $sourceRow, $sourceColumns, $mapper)
    {
        if (!array_key_exists($column, $this->_processorMethodsBuffer))
        {
            $method = $this->cellMethodPrefix . str_replace(array(' ', '-', ':'), '_', $column);
            $this->_processorMethodsBuffer[$column] = method_exists($this, $method) ? $method : null;
        }
        else
        {
            $method = $this->_processorMethodsBuffer[$column];
        }
        
        if ($method)
        {
            $destinationColumnName = $mapper->getDestinationColumnForSource($column);
            $cell = $this->{$method}($cell, $mappedRow, $sourceRow, $sourceColumns, $destinationColumnName);
        }
        
        return $cell;
    }
    
    /**
     * Is the cell for the current row processed?
     * @param	string	Destination column name
     * @return	bool	True if cell in current row is processed, false if not
     */
    protected function isCellProcessed($destinationColumn)
    {
        return array_key_exists($destinationColumn, $this->_processedRow);
    }
    
    /**
     * Get the cell value for the current row
     * @param	string	Destination column name
     * @return	mixed	The value for the cell or null if not processed yet
     */
    protected function getConvertedCell($destinationColumn)
    {
        return $this->_isCellConverted($destinationColumn) ? $this->_processedRow[$destinationColumn] : null;
    }
}