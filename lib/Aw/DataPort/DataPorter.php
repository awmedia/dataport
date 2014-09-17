<?php

namespace Aw\DataPort;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * DataPorter
 * @author Jerry Sietsma
 */
class DataPorter
{
    protected $reader;
    
    protected $writer;
    
    protected $mapper;
    
    protected $processors;
    
    protected $autoFlushWriter = true;
    
    public function __construct()
    {
        $this->processors = new ArrayCollection();
        $this->preProcessorFilters = new ArrayCollection();
        $this->postProcessorFilters = new ArrayCollection();
    }
    
    public function setReader(Reader $reader)
    {
        $this->reader = $reader;
        return $this;
    }
    
    public function getReader()
    {
        return $this->reader;
    }
    
    public function setWriter(Writer $writer)
    {
        $this->writer = $writer;
        return $this;
    }
    
    public function getWriter()
    {
        return $this->writer;
    }
    
    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }
    
    public function getMapper()
    {
        return $this->mapper;
    }
    
    public function getProcessors()
    {
        return $this->processors;
    }
    
    public function getPreProcessorFilters()
    {
        return $this->preProcessorFilters;
    }
    
    public function getPostProcessorFilters()
    {
        return $this->postProcessorFilters;
    }
    
    public function setAutoFlushWriter($autoFlush)
    {
        $this->autoFlushWriter = (bool) $autoFlush;
        return $this;
    }
    
    public function port()
    {
        $count = 0;
        $result = null;
        $success = false;
        $exception = null;
        $filteredCount = 0;
        
        try
        {
            $sourceColumns = $this->reader->getColumns();

            foreach ($this->reader->getRows() as $sourceRow)
            {
                $mappedRow = $this->mapper ? $this->mapper->mapSourceToDestinationRow($sourceRow) : $sourceRow;
                
                if (!$this->acceptRowByFilters($mappedRow, $this->preProcessorFilters))
                {
                    $filteredCount++;
                    continue;
                }
                
                $processedRow = $mappedRow;
                foreach ($this->getProcessors() as $processor)
                {
                    $processedRow = $processor->process($mappedRow, $sourceRow, $sourceColumns, $this->mapper);
                }
                
                if (!$this->acceptRowByFilters($processedRow, $this->postProcessorFilters))
                {
                    $filteredCount++;
                    continue;
                }
                
                $this->writer->writeRow($processedRow);
                                
                $count++;
            }
            
            if ($this->autoFlushWriter)
            {
                $this->writer->flush();
            }
            
            $result = $this->writer->getResult();
            $success = true;
        }
        catch(Exception $e)
        {
            $exception = $e;
        }
        
        return new DataPorterResult($success, $writerResult, $rowsPortedCounter, $exception);
    }
    
    /**
     * Private/protected function
     */
    
    protected function acceptRowByFilters($row, $filters)
    {
        foreach ($filters as $filter)
        {
            if (!$filter->accept($sourceRow))
            {
                return false;
            }
        }
        
        return true;
    }
}