<?php

namespace Aw\DataPort;

use \Exception,
    \Closure;

/**
 * Processor
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
        
        foreach ($mappedRow as $column => $cell)
        {
            $this->_processedRow[$column] = $this->processCell($column, $cell, $mappedRow, $sourceRow, $sourceColumns, $mapper);
        }
        
        return $this->_processedRow;
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
    
    protected function isCellProcessed($destinationColumn)
    {
        return array_key_exists($destinationColumn, $this->_processedRow);
    }
    
    protected function getConvertedCell($destinationColumn)
    {
        return $this->_isCellConverted($destinationColumn) ? $this->_processedRow[$destinationColumn] : null;
    }
}